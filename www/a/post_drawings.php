<?php
chdir("..");
require_once("inc.php");
require_once("POSTChart.inc.php");

$MODE = 'post';
ModuleInit('post_drawings');


if (KeyInRequest('action')) {
	$action = $_REQUEST['action'];
	switch ($action) {
		case 'copy_version':
			copyVersion(intval($_REQUEST['version_id']));
			die();
		case 'view':
		case 'draw':
			showVersion();
			die();
		case 'version_info':
			showVersionInfo();
			die();
		case 'drawing_info':
			showDrawingInfo();
			die();
		case 'new_drawing_form':
			showNewDrawingForm();
			die();
		case 'configure_rowscols':
			showConfigureRowColForm(intval($_REQUEST['id']));
			die();
		case 'delete_row':
			configureDeleteRow();
			die();
		case 'add_row':
			configureAddRow();
			die();
		case 'delete_col':
			configureDeleteCol();
			die();
		case 'add_col':
			configureAddCol();
			die();
		case 'config':
			processConfigRequest();
			die();
		case 'drawing_list':
			// used to select a drawing in the connections chooser
			processDrawingListRequest();
			die();
	}
}


if( KeyInRequest('drawing_id') ) {
	$drawing_id = intval($_REQUEST['drawing_id']);

	if( PostRequest() ) {

		// permissions check
		$drawing = GetDrawingInfo($drawing_id, 'post');
		if( !is_array($drawing) || (!IsAdmin() && $_SESSION['school_id'] != $drawing['school_id']) ) {
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( Request('action') == 'delete' ) {
			if( is_array($drawing) ) {
				if( IsSchoolAdmin() || $drawing['frozen'] == 0 ) {
					// school admins can delete versions, and anyone can delete a version if it has never been committed
					$DB->Query("UPDATE post_drawings SET deleted=1 WHERE id=$drawing_id");
				}
			}
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( Request('action') == 'publish' ) {
			$drawing = $DB->SingleQuery("SELECT * FROM post_drawings WHERE id=$drawing_id");
			if( is_array($drawing) ) {
				$DB->Query("UPDATE post_drawings SET published=0 WHERE parent_id=".$drawing['parent_id']);
				$DB->Query("UPDATE post_drawings SET published=1, frozen=1 WHERE id=$drawing_id");
			}
		}

		header('Location: '.$_SERVER['PHP_SELF'].'?action=drawing_info&id='.$drawing['parent_id']);
		die();

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
			FROM post_drawings, post_drawing_main
			WHERE post_drawings.parent_id=post_drawing_main.id
			AND post_drawing_main.id=".intval(Request('id')));
		if( !(Request('id') == "" || is_array($drawing) && (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id'])) ) {
			// permissions error
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( Request('delete') == 'delete' ) {
			$drawing_id = intval($_REQUEST['id']);
			if( CanDeleteDrawing($drawing_id) ) {
				// when deleting the entire drawing (from drawing_main) actually remove the records
						
				$DB->Query("DELETE FROM post_drawings WHERE parent_id=".$drawing_id);
				$DB->Query("DELETE FROM post_drawing_main WHERE id=".$drawing_id);
				$DB->Query("DELETE FROM post_cell WHERE drawing_id=".$drawing_id);
				$DB->Query("DELETE FROM post_col WHERE drawing_id=".$drawing_id);
				header("Location: ".$_SERVER['PHP_SELF']);
			}
			die();
		}

		$content = array();
		$content['name'] = $_REQUEST['name'];
		$content['last_modified'] = $DB->SQLDate();
		$content['last_modified_by'] = $_SESSION['user_id'];

		if( IsAdmin() || (IsStaff() && Request('type') == 'hs') )
			$school_id = $_REQUEST['school_id'];
		else
			$school_id = $_SESSION['school_id'];
		
		$content['code'] = CreateDrawingCodeFromTitle($content['name'],$school_id);

		if( Request('id') ) {
			// update requests are only handled through drawings_post.php now.
		} else {
			if( Request('type') == 'cc' )
				$post = new POSTChart_CC();
			else
				$post = new POSTChart_HS();

			$post->type = Request('type');
			$post->school_id = $school_id;
			$post->skillset_id = Request('skillset_id');
			$post->name = Request('name');
			$post->code = CreateDrawingCodeFromTitle($content['name'],$school_id);
			$post->createEmptyChart();
			$drawing_id = $post->saveToDB();

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





function showVersion() {
	global $DB, $TEMPLATE;


	$drawing = $DB->SingleQuery('SELECT main.*, d.published, d.frozen, schools.school_abbr, d.id, os.title AS skillset
		FROM post_drawing_main AS main
		JOIN post_drawings AS d ON d.parent_id = main.id
		JOIN schools ON main.school_id = schools.id
		LEFT JOIN oregon_skillsets AS os ON skillset_id = os.id
		WHERE d.id = '.intval(Request('version_id')).'
			AND deleted = 0');
	if( !is_array($drawing) ) {
		echo "The record does not exist";
		die();
	}

	$TEMPLATE->addl_styles[] = "/c/pstyle.css";
	$TEMPLATE->addl_styles[] = "/files/greybox/greybox.css";

	$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';
	$TEMPLATE->addl_scripts[] = '/files/greybox.js';
	$TEMPLATE->addl_scripts[] = '/common/URLfunctions1.js';


	if(CanEditVersion($drawing['id'])) {
		$readonly = false;
	} else {
		$readonly = true;
	}



	if( $readonly == false ) 
	{
		$TEMPLATE->addl_scripts[] = '/common/jquery/jquery-ui.js';
		$TEMPLATE->addl_scripts[] = '/common/jquery/jquery.base64.js';
		$TEMPLATE->addl_scripts[] = '/common/jquery/jquery.contextMenu.js';
		$TEMPLATE->addl_scripts[] = '/c/postedit.js';
		$TEMPLATE->addl_styles[]  = '/common/jquery/jquery.contextMenu.css';
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

	
	PrintHeader();
	
	$post = POSTChart::Create($drawing['id']);

	echo '<div style="margin-bottom: 10px">';
	echo '<div id="post_title"><img src="/files/titles/post/'.base64_encode($post->school_abbr).'/'.base64_encode($post->name).'.png" alt="' . $post->school_abbr . ' Career Pathways - ' . $post->name . '" /></div>';
	if( $drawing['skillset'] )
	{
		echo '<div id="skillset">';
			echo 'Oregon Skill Set: ' . $drawing['skillset'];
		echo '</div>';
	}
	echo '</div>';
	
	echo '<div id="canvas">';
	$post->display();
	echo '</div> <!-- end canvas -->';
	
	PrintFooter();

}

function copyVersion($version_id) {
	global $DB;

	$post = POSTChart::create($version_id);

	$drawing = GetDrawingInfo($version_id, 'post');

	// first get the title information
	$drawing_main = $DB->SingleQuery("SELECT * FROM post_drawing_main WHERE id=" . $drawing['parent_id']);

	$create = Request('create') ? Request('create') : 'new_version';
	$copy_to = Request('copy_to') ? Request('copy_to') : 'same_school';

	if( IsAdmin() || ($drawing_main['type'] == 'HS' && IsStaff()) ) {
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
		if( $copy_to == 'othr_school' && (IsAdmin() || ($drawing_main['type'] == 'HS' && IsStaff())) ) {
			$newdrawing['school_id'] = Request('target_org_id');
			$post->school_id = $newdrawing['school_id'];
		} elseif ($copy_to == 'same_school') {
			$newdrawing['school_id'] = $drawing['school_id'];
			$post->school_id = $drawing['school_id'];
		}
		else {
			$newdrawing['school_id'] = $_SESSION['school_id'];
			$post->school_id = $_SESSION['school_id'];
		}
		$post->name = (Request('drawing_name') ? Request('drawing_name') : $drawing_main['name']);

		$new_version_id = $post->saveToDB();
	} else {
		// find the greatest version number for this drawing
		$new_version_id = $post->saveToDB($drawing_main['id']);
	}

	if (Request('from_popup') == 'true') {
		header("Location: /a/copy_success_popup.php?mode=post&version_id=$new_version_id&copy_to=$copy_to&create=$create");
	}
	else {
		header("Location: ".$_SERVER['PHP_SELF']."?action=draw&version_id=".$new_version_id);
	}
}


function showDrawingInfo() {
global $DB, $TEMPLATE;

	$TEMPLATE->AddCrumb('', 'POST Drawing Properties');
	$TEMPLATE->toolbar_function = "ShowSymbolLegend";
	
	PrintHeader();

	$drawing = $DB->SingleQuery("SELECT post_drawing_main.*
		FROM post_drawing_main
		WHERE post_drawing_main.id=".intval(Request('id')));
	if( is_array($drawing) ) {
		if( 1 || IsAdmin() || $drawing['school_id'] == $_SESSION['school_id'] ) {
			ShowDrawingForm(Request('id'));
		} else {
			ShowReadonlyForm(Request('id'));
		}
	} else {
		echo "Not found";
	}

	PrintFooter();
}

function showNewDrawingForm() {
	PrintHeader();
	ShowDrawingForm("");
	PrintFooter();
}

function ShowDrawingForm($id) {
	global $DB, $MODE;
	require('view/drawings/post_drawing_info.php');
}

function showConfigureRowColForm($version_id) {
	global $DB;

	$post = POSTChart::create($version_id);

	if(	$post->type == 'CC' )
	{
		$years = array(1=>1, 2, 3, 4, 5, 6);
		$terms = array('F'=>'Fall', 'W'=>'Winter', 'S'=>'Spring', 'U'=>'Summer (after Spring)', 'M'=>'Summer (before Fall)');
	}
	
	?>
	<div style="margin-left:25px; margin-right:25px; background-color: white; border: 1px #777777 solid"><div class="postGreyboxContent">
	<style type="text/css">
		#rowList {
			border-top: 1px #999999 solid;
			margin-top: 10px;
		}
		.rowName {
			font-size: 1.3em;
			padding: 2px;
			border-bottom: 1px #999999 solid;
		}
		.rowConfigHead {
			font-weight:bold;
			font-size: 1.5em;
		}
		.addRowText {
			font-size: 1.3em;
			margin-left: 10px;
		}
		#addRowTable td {
			vertical-align: middle;
			height: 30px;
		}
		.colButton {
			background-color: #888888;
			color: white;
			margin-right: 10px;
			font-size: 13px;
		}
	</style>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			bindDeleteButtons();
			
			jQuery(".addRowLink").click(function(){
				var type = jQuery(this).attr("id").split("_")[1];
				var data = {action: "add_row", id: <?= $version_id ?>};

				switch( type ) {
					case "prereq":
					case "unlabeled":
					case "electives":
						data.type = type;
						break;
					case "term":
						data.type = type;
						data.year = jQuery("#addYear").val();
						data.term = jQuery("#addTerm").val();
						break;
					default:
						return false;
				}
				
				jQuery.post("/a/post_drawings.php", data,
					function(data){
						jQuery("#rowList").html(data);
						bindDeleteButtons();
					}, "HTML");
			});
			
			jQuery(".colButton").click(function(){
				var mode = jQuery(this).attr("id");
				jQuery.post("/a/post_drawings.php", {
					action: mode,
					id: <?= $version_id ?>
				}, function(data){
					jQuery("#miniBlockDiagram").html(data);
				}, "HTML");
			
			});
			
		});

		function bindDeleteButtons() {
			jQuery(".deleteBtn").click(function(){
				jQuery.post("/a/post_drawings.php", {
					action: "delete_row",
					row_id: jQuery(this).attr("id").split("_")[1]
				},
				function(data){
					jQuery("#rowList").html(data);
					bindDeleteButtons();
				}, "HTML");
			})
		}
	</script>
	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
	<table><tr>
	<td valign="top" style="padding-left:10px">
		<div class="rowConfigHead">Rows in your Drawing</div>
		<div id="rowList">
		<?php
		showRowsInDrawing($post);
		?>
		</div>

		<div style="margin-top:20px; margin-bottom:10px">
		<div class="rowConfigHead" style="margin-bottom:3px;">Columns</div>
			<table width="300"><tr>
				<input type="button" class="colButton" value="Delete Last Column" id="delete_col" />
				<input type="button" class="colButton" value="Add Column" id="add_col" />
			</tr></table>
		</div>
		
		<div style="margin-top:20px; margin-bottom:10px">
		<div class="rowConfigHead" style="margin-bottom:3px">Block Diagram</div>
			<div id="miniBlockDiagram"><?php $post->displayMini(); ?></div>
		</div>
		

	</td>
	<td valign="top" style="padding-left:30px">
		<div class="rowConfigHead">Add Row</div>
		<table id="addRowTable">
		<?php if( $post->type == 'CC' ) { ?>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_prereq" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText">Prereq</div></div></td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_term" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText">Year: <?= GenerateSelectBox($years, 'addYear') ?> Term: <?= GenerateSelectBox($terms, 'addTerm') ?> </div></td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_electives" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText">Electives</div></td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_unlabeled" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText">Blank</div></td>
			</tr>
		<?php } else { ?>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_term" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText">Year: <?= GenerateSelectBox(array(9=>9, 10, 11, 12), 'addYear') ?></div></td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_electives" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText">Electives</div></td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_unlabeled" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText">Blank</div></td>
			</tr>
		<?php } ?>
		</table>

	</td>
	</tr></table>

	<div style="text-align:right; margin-right: 10px;">
		<input type="button" onclick="chGreybox.close()" value="Close" class="submit" />
	</div>

	<div style="color:#666666; margin-top:10px;"><span class="red">Warning:</span> Changing the number of rows or columns of your drawing is a <b>destructive</b> operation. For example, if you change your drawing from 7 columns to 6 columns, the contents of the far-right (seventh) column will be permanently erased.</div>

	</form>
	</div></div>
	<?php
}

function showRowsInDrawing(&$post)
{
	foreach( $post->rows as $r )
	{
		echo '<div class="rowName">';
			echo '<a href="javascript:void(0);" id="deleteRow_'.$r['id'].'" class="deleteBtn">' . SilkIcon('cross.png') . '</a> ';
			if( $r['row_type'] == 'unlabeled' )
				echo '(blank)';
			else
				echo str_replace('<br />', ' ', $r['rowName']);
			echo ' <span style="color:#999999">(' . $r['cellCount'] . ')</span>';
		echo '</div>';
	}
}

function configureDeleteCol()
{
	global $DB;
	
	$version_id = Request('id');
	if( CanEditVersion($version_id) )
	{
		$col = $DB->SingleQuery('SELECT * FROM post_col WHERE drawing_id='.$version_id.' ORDER BY num DESC LIMIT 1');
		$DB->Query('DELETE FROM post_cell WHERE col_id='.$col['id']);
		$DB->Query('DELETE FROM post_col WHERE id='.$col['id']);
	}

	$post = POSTChart::create($version_id);
	$post->displayMini();
}

function configureAddCol()
{
	global $DB;
	
	$version_id = Request('id');
	if( CanEditVersion($version_id) )
	{
		$last_col = $DB->SingleQuery('SELECT * FROM post_col WHERE drawing_id='.$version_id.' ORDER BY num DESC LIMIT 1');
		$col_id = $DB->Insert('post_col', array('drawing_id'=>$version_id, 'title'=>'', 'num'=>$last_col['num']+1));

		$rows = $DB->MultiQuery('SELECT id FROM post_row WHERE drawing_id='.$version_id);
		foreach( $rows as $r )
		{
			$DB->Insert('post_cell', array('drawing_id'=>$version_id, 'row_id'=>$r['id'], 'col_id'=>$col_id));
		}
	}
	
	$post = POSTChart::create($version_id);
	$post->displayMini();
}


function configureDeleteRow()
{
	global $DB;
	
	$row_id = intval(Request('row_id'));
	$version_id = $DB->GetValue('drawing_id', 'post_row', Request('row_id'));
	if( CanEditVersion($version_id) )
	{
		$DB->Query('DELETE FROM post_cell WHERE row_id='.$row_id);
		$DB->Query('DELETE FROM post_row WHERE id='.$row_id);
		$post = POSTChart::create($version_id);
		showRowsInDrawing($post);
	}
}

function configureAddRow()
{
	global $DB;

	$id = intval(Request('id'));
	if( CanEditVersion($id) )
	{
		switch(Request('type'))
		{
			case 'prereq':
			case 'electives':
			case 'unlabeled':
				// find the last row of this type
				$last_row = $DB->SingleQuery('SELECT * FROM post_row WHERE drawing_id='.$id.' AND row_type="'.Request('type').'" ORDER BY row_year DESC LIMIT 1');
				$next_row = $last_row['row_year']+1;
				$row_data = array('drawing_id'=>$id, 'row_type'=>Request('type'), 'row_year'=>$next_row);
				break;

			case 'term':
				$row_data = array('drawing_id'=>$id, 'row_type'=>Request('type'), 'row_year'=>Request('year'), 'row_term'=>Request('term'));
				break;

			default:
				return FALSE;
		}

		// create the row record and all the blank cells
		$row_id = $DB->Insert('post_row', $row_data);
		
		$cols = $DB->MultiQuery('SELECT id FROM post_col WHERE drawing_id='.$id);
		foreach( $cols as $c )
		{
			$DB->Insert('post_cell', array('drawing_id'=>$id, 'row_id'=>$row_id, 'col_id'=>$c['id']));
		}
		
		$post = POSTChart::create($id);
		showRowsInDrawing($post);
	}
}





function showVersionInfo() {
	global $DB, $MODE, $TEMPLATE;

	$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';
	$TEMPLATE->addl_scripts[] = '/files/greybox.js';
	$TEMPLATE->AddCrumb('', 'POST Version Settings');
	$TEMPLATE->toolbar_function = "ShowInfoAndLegend";

	PrintHeader();
	$version_id = Request('version_id');
	require('view/drawings/post_version_info.php');
	PrintFooter();
}

function ShowInfoAndLegend() {
	ShowInfobar();
	require('view/post/toolbar.php');
	ShowSymbolLegend();
}

function ShowSymbolLegend() {
	$helpFile = 'drawing_list';
	$onlyLegend = TRUE;
	require('view/drawings/helpbar.php');
}

function ShowInfobar() {
	require('view/post/infobar.php');
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
	ShowInfobar();
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
	require('view/post/toolbar.php');
	require('view/post/helpbar.php');
}

/**
 * This is an ajax handler for configuring rows/columns
 */
function processConfigRequest()
{
	global $DB;

	$drawing_id = intval(Request('drawing_id'));
	
	if( !CanEditVersion($drawing_id) )
		die('Permissions error');
	
	switch( Request('change') )
	{
	case 'terms':
		$old_rows = $DB->GetValue('num_rows', 'post_drawings', $drawing_id);
		$new_rows = intval(Request('value'));
		
		$drawing = array();
		$drawing['num_rows'] = $new_rows;
		$DB->Update('post_drawings', $drawing, $drawing_id);

		if( $new_rows < $old_rows )
		{
			// removing rows. delete some database records.
			$DB->Query('DELETE FROM post_cell 
				WHERE drawing_id='.$drawing_id.'
 				AND row_num>'.($new_rows));
		}
		elseif( $old_rows < $new_rows )
		{
			// adding rows. make some new empty cells.

			$cols = $DB->VerticalQuery('SELECT * FROM post_col WHERE drawing_id='.$drawing_id.' ORDER BY num', 'id', 'num');
			
			for( $i = $old_rows+1; $i <= $new_rows; $i++ )
			{
				foreach( $cols as $num=>$col_id )
				{
					$cell = array();
					$cell['drawing_id'] = $drawing_id;
					$cell['col_id'] = $col_id;
					$cell['row_num'] = $i;
					$DB->Insert('post_cell', $cell);
				}
			}
		
		}
		
		break;
	case 'extra_rows':
		$old_rows = $DB->GetValue('num_extra_rows', 'post_drawings', $drawing_id);
		$new_rows = intval(Request('value'));
		
		$drawing = array();
		$drawing['num_extra_rows'] = $new_rows;
		$DB->Update('post_drawings', $drawing, $drawing_id);

		if( $new_rows < $old_rows )
		{
			// removing rows. delete some database records.
			$DB->Query('DELETE FROM post_cell 
				WHERE drawing_id='.$drawing_id.'
 				AND row_num>='.(100+$new_rows));
		}
		elseif( $old_rows < $new_rows )
		{
			// adding rows. make some new empty cells.

			$cols = $DB->VerticalQuery('SELECT * FROM post_col WHERE drawing_id='.$drawing_id.' ORDER BY num', 'id', 'num');
			
			for( $i = $old_rows; $i < $new_rows; $i++ )
			{
				foreach( $cols as $num=>$col_id )
				{
					$cell = array();
					$cell['drawing_id'] = $drawing_id;
					$cell['col_id'] = $col_id;
					$cell['row_num'] = $i+100;
					$DB->Insert('post_cell', $cell);
				}
			}
		}
		break;
	case 'columns':
		$cols = $DB->VerticalQuery('SELECT * FROM post_col WHERE drawing_id='.$drawing_id.' ORDER BY num', 'id', 'num');
		$rows = $DB->VerticalQuery('SELECT * FROM post_rows WHERE drawing_id='.$drawing_id.' ORDER BY num', 'id');
		$drawing = $DB->SingleQuery('SELECT * FROM post_drawings WHERE id='.$drawing_id);

		$old_cols = count($cols);
		$new_cols = intval(Request('value'));
		
		if( $new_cols < $old_cols )
		{
			// removing columns. delete cells vertically
			for( $i = $old_cols; $i > $new_cols; $i-- )
			{
				$DB->Query('DELETE FROM post_col WHERE id='.$cols[$i]);
				$DB->Query('DELETE FROM post_cell WHERE col_id='.$cols[$i]);
			}
		}
		else
		{
			// adding columns. add cells vertically
			
			for( $i = $old_cols+1; $i <= $new_cols; $i++ )
			{
				$col = array();
				$col['drawing_id'] = $drawing_id;
				$col['num'] = $i;
				$col_id = $DB->Insert('post_col', $col);
				
				for( $j=1; $j<=$drawing['num_rows']; $j++ )
				{
					$cell = array();
					$cell['drawing_id'] = $drawing_id;
					$cell['row_num'] = $j;
					$cell['col_id'] = $col_id;
					$DB->Insert('post_cell', $cell);
				}

				for( $j=100; $j<$drawing['num_extra_rows']+100; $j++ )
				{
					$cell = array();
					$cell['drawing_id'] = $drawing_id;
					$cell['row_num'] = $j;
					$cell['col_id'] = $col_id;
					$DB->Insert('post_cell', $cell);
				}

			}
		}
	
		break;	
	}
}


function showNewDrawingPreview(&$config)
{
	global $DB;
	
	
	
	
}

?>