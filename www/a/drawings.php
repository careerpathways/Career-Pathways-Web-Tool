<?php
chdir("..");
include("inc.php");

ModuleInit('drawings');

$version_id = Request('version_id');

if (!$version_id) {
	$version_id = Request('drawing_id');
}

if ($version_id) {
	$drawing_id = $version_id = $_REQUEST['drawing_id'] = $_REQUEST['version_id'] = intval($version_id);
}

if (KeyInRequest('action')) {
	$action = $_REQUEST['action'];

	// actions which do not require a post
	switch ($action) {
		// TODO this should require a post
		case 'copy_version':
			copyVersion(intval($_REQUEST['version_id']));
			die();
			break;
		case 'view':
		case 'draw':
			showVersion();
			die();
			break;
		case 'version_info':
			showVersionInfo();
			die();
			break;
		case 'drawing_info':
			showDrawingInfo();
			die();
			break;
		case 'new_drawing_form':
			showNewDrawingForm();
			die();
			break;
	}
}

if( KeyInRequest('drawing_id') ) {
	$drawing_id = intval($_REQUEST['drawing_id']);

	if( PostRequest() ) {

		// permissions check
		$drawing = GetDrawingInfo($drawing_id);
		if( !is_array($drawing) || (!IsAdmin() && $_SESSION['school_id'] != $drawing['school_id']) ) {
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( Request('action') == 'delete' ) {
			if( is_array($drawing) ) {
				if( IsSchoolAdmin() || $drawing['frozen'] == 0 ) {
					// school admins can delete versions, and anyone can delete a version if it has never been committed
					$DB->Query("UPDATE drawings SET deleted=1 WHERE id=$drawing_id");
				}
			}
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( Request('action') == 'publish' ) {
			$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=$drawing_id");
			if( is_array($drawing) ) {
				$DB->Query("UPDATE drawings SET published=0 WHERE parent_id=".$drawing['parent_id']);
				$DB->Query("UPDATE drawings SET published=1, frozen=1 WHERE id=$drawing_id");

				$content = array();
				$content['rendered_html'] = $_REQUEST['rendered_html'];
				$DB->Update('drawing_main',$content,$drawing['parent_id']);
			}
		}

		header("Location: ".$_SERVER['PHP_SELF']);

	} else {
		// support for old-school urls
		if (KeyInRequest('draw')) {
			header("Location: ".$_SERVER['PHP_SELF']."?action=draw&version_id=".$drawing_id);
		}
		if (KeyInRequest('view')) {
			header("Location: ".$_SERVER['PHP_SELF']."?action=view&version_id=".$drawing_id);
		}
		else {
			header("Location: ".$_SERVER['PHP_SELF']."?action=version_info&version_id=".$drawing_id);
		}
	}

} elseif( KeyInRequest('id') ) {

	if( PostRequest() ) {

		$drawing = $DB->SingleQuery("SELECT *
			FROM drawings, drawing_main
			WHERE drawings.parent_id=drawing_main.id
			AND drawing_main.id=".intval(Request('id')));
		if( !(Request('id') == "" || is_array($drawing) && (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id'])) ) {
			// permissions error
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( IsSchoolAdmin() && Request('delete') == 'delete' ) {
			$drawing_id = intval($_REQUEST['id']);
			// when deleting the entire drawing (from drawing_main) actually remove the records
			$DB->Query('DELETE FROM connections WHERE source_object_id IN (SELECT objects.id FROM objects, drawings WHERE objects.drawing_id=drawings.id AND drawings.parent_id=' . $drawing_id . ')');

			$drawings = $DB->VerticalQuery("SELECT id FROM drawings WHERE parent_id=" . $drawing_id,'id');
			foreach( $drawings as $did ) {
				$DB->Query("DELETE FROM objects WHERE drawing_id=".$did);
			}
			$DB->Query("DELETE FROM drawings WHERE parent_id=".$_REQUEST['id']);
			$DB->Query("DELETE FROM drawing_main WHERE id=".$_REQUEST['id']);
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		$content = array();
		$content['name'] = $_REQUEST['name'];
		$content['last_modified'] = $DB->SQLDate();
		$content['last_modified_by'] = $_SESSION['user_id'];

		$school_id = (IsAdmin()?$_REQUEST['school_id']:$_SESSION['school_id']);

		$content['code'] = CreateDrawingCodeFromTitle($content['name'],$school_id);

		if( Request('id') ) {
			// update requests are only handled through drawings_post.php now.
		} else {
			$content['date_created'] = $DB->SQLDate();
			$content['created_by'] = $_SESSION['user_id'];
			$content['school_id'] = $school_id;

			$parent_id = $DB->Insert('drawing_main',$content);

			// create the first default drawing
			$content = array();
			$content['version_num'] = 1;
			$content['date_created'] = $DB->SQLDate();
			$content['created_by'] = $_SESSION['user_id'];
			$content['last_modified'] = $DB->SQLDate();
			$content['last_modified_by'] = $_SESSION['user_id'];
			$content['parent_id'] = $parent_id;

			$drawing_id = $DB->Insert('drawings',$content);

			// start drawing it
			header("Location: ".$_SERVER['PHP_SELF']."?action=draw&version_id=".$drawing_id);
		}

	} else {
		// support for old-school urls
		if (!Request('id')) {
			header("Location: ".$_SERVER['PHP_SELF'] . '?action=new_drawing_form');
		}
		else {
			header("Location: ".$_SERVER['PHP_SELF'] . '?action=drawing_info&id=' . $_REQUEST['id']);
		}
	}

} else {
	require('view/drawings/list.php');
}

function showVersionInfo() {
	global $DB, $version_id;
	PrintHeader();
	require('view/drawings/version_info.php');
	PrintFooter();
}

function showVersion() {
	global $DB, $TEMPLATE;

	$version_id = $_REQUEST['drawing_id'] = intval($_REQUEST['version_id']);
	// load the drawing to get the parent_id
	$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=$version_id");

	// load the title information, etc
	$drawing_main = $DB->SingleQuery("SELECT * FROM drawing_main WHERE id=".$drawing['parent_id']);

	$TEMPLATE->AddCrumb('',$drawing_main['name']);

	$_SESSION['drawing_id'] = $version_id;

	// permissions check. freeze the drawing if from a different school
	$drawing = GetDrawingInfo($version_id);
	if( CanEditOtherSchools() || $_SESSION['school_id'] == $drawing_main['school_id'] ) {
		$readonly = false;
	} else {
		$readonly = true;
	}

	if( !($drawing['published']==1 || $drawing['frozen']==1) ) {
		$TEMPLATE->toolbar_function = "ShowToolbar";
	}
	if( $drawing['frozen'] == 1 ) {
		$TEMPLATE->toolbar_function = "ShowFrozenHelp";
	}
	if( $drawing['published'] == 1 ) {
		$TEMPLATE->toolbar_function = "ShowPublishedHelp";
	}
	if( KeyInRequest('view') ) {
		$TEMPLATE->toolbar_function = "";
	}
	if( $readonly ) {
		$TEMPLATE->toolbar_function = "ShowReadonlyHelp";
	}

	$TEMPLATE->is_chart_page = true;
	PrintHeader();
	?>

	<script type="text/javascript" src="/files/greybox.js"></script>

	<div id="drawing_canvas" class="ctpathways">
		<?php require('c/view/chart_include.php'); ?>
		<?php if (!($drawing['published']==1 || $drawing['frozen']==1 || KeyInRequest('view') || $readonly)) : ?>
		<script type="text/javascript" src="/c/chadmin.js"></script>
		<?php endif; ?>
		<script type="text/javascript">
			function init() {
				if (arguments.callee.done) return;
				arguments.callee.done = true;
				Charts.draw('drawing_canvas','toolbar_content');
			}

			/* for Mozilla */
			if (document.addEventListener) {
				document.addEventListener("DOMContentLoaded", init, false);
			}

			/* for Internet Explorer */
			/*@cc_on @*/
			/*@if (@_win32)
				document.write("<script defer src=\"/c/init.js\"><"+"/script>");
			/*@end @*/

			/* for other browsers */
			window.onload = init;
		</script>
	</div>

	<?php
	PrintFooter();
}

function copyVersion($version_id) {
	global $DB;
	$drawing = GetDrawingInfo($version_id);

	// first get the title information
	$drawing_main = $DB->SingleQuery("SELECT * FROM drawing_main WHERE id=" . $drawing['parent_id']);
	
	$create = Request('create') ? Request('create') : 'new_version';
	$copy_to = Request('copy_to') ? Request('copy_to') :'same_school';
	if( IsAdmin() ) {
		if( $_SESSION['school_id'] != $drawing['school_id'] ) {
			if ($copy_to !== 'same_school') {
				$different_school = false;
				$create = 'new_drawing';
			}
		} else {
			$different_school = false;
		}
	} else if ($_SESSION['school_id'] !== $drawing['school_id']) {
		$create = 'new_drawing';
		$copy_to = 'user_school';
	}
	else {
		$copy_to = 'same_school';
	}

	if( $create == 'new_drawing' ) {
		if ($copy_to !== 'same_school') {
			$newdrawing['school_id'] = $_SESSION['school_id'];
		}
		else {
			$newdrawing['school_id'] = $drawing['school_id'];
		}
			
		$newdrawing['name'] = Request('drawing_name') ? Request('drawing_name') : $drawing_main['name'];
		// tack on a random number at the end. it will only last until they change the name of the drawing
		$newdrawing['code'] = strtolower($DB->GetValue('school_abbr','schools',$newdrawing['school_id']).'_'.str_replace(' ','_',$newdrawing['name'])).rand(100,999);
		$newdrawing['date_created'] = $drawing_main['date_created'];
		$newdrawing['last_modified'] = $DB->SQLDate();
		$newdrawing['created_by'] = $drawing_main['created_by'];
		$newdrawing['last_modified_by'] = $_SESSION['user_id'];
		$new_id = $DB->Insert('drawing_main',$newdrawing);
		$drawing_main = $DB->SingleQuery("SELECT * FROM drawing_main WHERE id=".$new_id);
		$version['next_num'] = 1;
	} else {
		// find the greatest version number for this drawing
		$version = $DB->SingleQuery("SELECT (MAX(version_num)+1) AS next_num FROM drawings WHERE parent_id=".$drawing_main['id']);
	}

	// duplicate the requested drawing and create a new one
	$objects = $DB->MultiQuery("SELECT * FROM objects WHERE drawing_id=$version_id");

	$content = array();
	$content['version_num'] = $version['next_num'];
	$content['date_created'] = $DB->SQLDate();
	$content['created_by'] = $_SESSION['user_id'];
	$content['last_modified'] = $DB->SQLDate();
	$content['last_modified_by'] = $_SESSION['user_id'];
	$content['parent_id'] = $drawing_main['id'];

	$new_version_id = $DB->Insert('drawings',$content);

	$idMap = array();

	foreach( $objects as $obj ) {
		$newobj = array();
		$newobj['drawing_id'] = $new_version_id;

		// FIXME !!!!
		// have to modify the ids inside the content to be the new id
		// find the next auto_increment value that will be created for this row
		$table_status = $DB->SingleQuery("SHOW TABLE STATUS WHERE Name='objects'");
		$new_id = $table_status['Auto_increment'];
		$idMap[$obj['id']] = $new_id;

		$obj = unserialize($obj['content']);
		$obj['id'] = $new_id;
		if( $copy_to !== 'same_school' ) {
			$obj['config']['color'] = "333333";  // reset the colors on the objects to grey
		}
		$newobj['content'] = serialize($obj);

		$DB->Insert('objects',$newobj);
	}

	$connections = $DB->MultiQuery('SELECT connections.* FROM connections, objects WHERE source_object_id=objects.id and objects.drawing_id=' . $version_id);

	foreach ($connections as $connection) {
		unset($connection['id']);
		$connection['source_object_id'] = $idMap[$connection['source_object_id']];
		$connection['destination_object_id'] = $idMap[$connection['destination_object_id']];
		$DB->Insert('connections', $connection);
	}
	
	if (Request('from_popup') == 'true') {
		header("Location: /a/copy_success_popup.php?version_id=".$new_version_id);
	}
	else {
		header("Location: ".$_SERVER['PHP_SELF']."?action=draw&version_id=".$new_version_id);
	}
}

function showNewDrawingForm() {
	PrintHeader();
	ShowDrawingForm("");
	PrintFooter();
}

function showDrawingInfo() {
	PrintHeader();

	$drawing = GetDrawingInfo(intval(Request('id')), 'drawing_main');
	if( is_array($drawing) ) {
		if( IsAdmin() || $drawing['school_id'] == $_SESSION['school_id'] ) {
			ShowDrawingForm(Request('id'));
		} else {
			ShowReadonlyForm(Request('id'));
		}
	} else {
		echo "Not found";
	}

	PrintFooter();
}

function ShowDrawingForm($id) {
	global $DB;
	require('view/drawings/drawing_info.php');
}

function ShowInfobar() {
	require('view/drawings/infobar.php');
}

function ShowToolbar() {
	ShowInfobar();

	showToolbarAndHelp(true, 'standard');
}


function ShowPublishedHelp() {
	ShowInfobar();

	showToolbarAndHelp(false, 'published');
}

function ShowFrozenHelp() {
	ShowInfobar();

	showToolbarAndHelp(true, 'frozen');
}

function ShowReadonlyHelp() {
	showToolbarAndHelp(false, 'read_only');
}

function ShowDrawingListHelp() {
	$helpFile = 'drawing_list';
	require('view/drawings/helpbar.php');
}

function ShowReadonlyForm($id) {
	require('view/drawings/read_only_form.php');
}

function showToolbarAndHelp($publishAllowed, $helpFile = false) {
	require('view/drawings/toolbar.php');
	require('view/drawings/helpbar.php');
}
