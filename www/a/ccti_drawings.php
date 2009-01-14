<?php
chdir("..");
include("inc.php");

$MODE = 'ccti';
ModuleInit('ccti_drawings');


if (KeyInRequest('action')) {
	$action = $_REQUEST['action'];
	switch ($action) {
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
		$drawing = GetDrawingInfo($drawing_id, 'ccti');
		if( !is_array($drawing) || (!IsAdmin() && $_SESSION['school_id'] != $drawing['school_id']) ) {
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( Request('action') == 'delete' ) {
			if( is_array($drawing) ) {
				if( IsSchoolAdmin() || $drawing['frozen'] == 0 ) {
					// school admins can delete versions, and anyone can delete a version if it has never been committed
					$DB->Query("UPDATE ccti_drawings SET deleted=1 WHERE id=$drawing_id");
				}
			}
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( Request('action') == 'publish' ) {
			$drawing = $DB->SingleQuery("SELECT * FROM ccti_drawings WHERE id=$drawing_id");
			if( is_array($drawing) ) {
				$DB->Query("UPDATE ccti_drawings SET published=0 WHERE parent_id=".$drawing['parent_id']);
				$DB->Query("UPDATE ccti_drawings SET published=1, frozen=1 WHERE id=$drawing_id");
			}
		}

		header("Location: ".$_SERVER['PHP_SELF'].'?action=drawing_info&id='.$drawing['parent_id']);

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
			FROM ccti_drawings, ccti_drawing_main
			WHERE ccti_drawings.parent_id=ccti_drawing_main.id
			AND ccti_drawing_main.id=".intval(Request('id')));
		if( !(Request('id') == "" || is_array($drawing) && (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id'])) ) {
			// permissions error
			header("Location: ".$_SERVER['PHP_SELF']);
			die();
		}

		if( CanDeleteDrawing($drawing) && Request('delete') == 'delete' ) {
			$drawing_id = intval($_REQUEST['id']);
			// when deleting the entire drawing (from drawing_main) actually remove the records

					
			$programs = $DB->MultiQuery('SELECT * FROM ccti_programs WHERE drawing_id='.$drawing_id);
			foreach( $programs as $p ) 
			{
				$sections = $DB->MultiQuery('SELECT * FROM ccti_sections WHERE program_id='.$p['id']);
				foreach( $sections as $s ) 
				{
					$DB->Query('DELETE FROM ccti_section_labels WHERE section_id='.$s['id']);
					$DB->Query('DELETE FROM ccti_data WHERE section_id='.$s['id']);
				}
				$DB->Query('DELETE FROM ccti_sections WHERE program_id='.$p['id']);
			}
			$DB->Query('DELETE FROM ccti_programs WHERE drawing_id='.$drawing_id);

			$DB->Query("DELETE FROM ccti_drawings WHERE parent_id=".$drawing_id);
			$DB->Query("DELETE FROM ccti_drawing_main WHERE id=".$drawing_id);
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

			$parent_id = $DB->Insert('ccti_drawing_main',$content);

			// create the first default drawing
			$content = array();
			$content['version_num'] = 1;
			$content['date_created'] = $DB->SQLDate();
			$content['created_by'] = $_SESSION['user_id'];
			$content['last_modified'] = $DB->SQLDate();
			$content['last_modified_by'] = $_SESSION['user_id'];
			$content['parent_id'] = $parent_id;

			$drawing_id = $DB->Insert('ccti_drawings',$content);

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
	global $DB;

	PrintHeader();
	
	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, d.id
		FROM ccti_drawing_main AS main, ccti_drawings AS d, schools
		WHERE d.parent_id = main.id
			AND main.school_id = schools.id
			AND d.id = '.intval(Request('version_id')).'
			AND deleted = 0');
	if( !is_array($drawing) ) {
		echo "The record does not exist";
		die();
	}

	$drawing_id = $drawing['id'];
	CCTI_check_permission($drawing_id);
	
	$ccti = new CCTI_Drawing($drawing_id);
	
	?>
	<link rel="stylesheet" type="text/css" href="/c/ctstyle.css" />
	<script type="text/javascript" src="/c/cctiedit.js"></script>
	<script type="text/javascript">
		CCTI.drawing_id = <?= $drawing_id ?>;
	</script>
	<link rel="stylesheet" type="text/css" href="/common/jquery/jquery-treeview/jquery.treeview.css" />
	<script src="/common/jquery-1.2.6.pack.js" type="text/javascript"></script>
	<script src="/common/jquery/jquery.base64.js" type="text/javascript"></script>
	<script src="/common/jquery/jquery-treeview/lib/jquery.cookie.js" type="text/javascript"></script>
	<script src="/common/jquery/jquery-treeview/jquery.treeview.js" type="text/javascript"></script>
	
	<script type="text/javascript">
			jQuery.noConflict();
	
			jQuery(document).ready(function() {
				jQuery("#tree").treeview({
					collapsed: true,
					animated: "medium",
					control:"#sidetreecontrol",
					persist: "location"
				});
			})
	</script>
	
	<?php
	
	echo '<div id="ccti_title"><img src="/files/titles/'.base64_encode($drawing['school_abbr']).'/'.base64_encode($ccti->name).'.png"></div>';
	
	echo '<div class="yui-skin-sam" id="canvas">';
	
	$pnum = 0;
	foreach( $ccti->programs as $pid=>$program )
	{
		echo '<table class="ccti_program" id="ccti_program'.$pid.'">';
	
		if( $program->header != '' )
		{
			echo '<tr>';
				img_head($program, $program->headleft, 'left', $pid);
				echo '<td colspan="'.($program->content_cols+1).'" class="ccti_program_header'.($program->header?'':' empty').'"onclick="CCTI.editContent(this, '.$pid.','.$sid.',\'header\')">';
					echo ($program->header?$program->header:"&nbsp;");
				echo '</td>';
				img_head($program, $program->headright, 'right', $pid);
			echo '</tr>';
		}
	
		$snum = 0;
		foreach( $program->sections as $sid=>$section )
		{
			if( $section->header != '' )
			{
				echo '<tr>';
					if( $snum == 0 && $program->header == '' )
					{
						img_head($program, $program->headleft, 'left', $pid);
					}
	
					echo '<td class="ccti_xy_header" onclick="CCTI.editContent(this, '.$pid.', '.$sid.', \'label\', 0, 0, \'xy\')">'.($section->label_xy->text?$section->label_xy->text:'&nbsp;').'</td>';
					echo '<td colspan="'.($program->content_cols-$program->show_occ_titles).'" class="ccti_header'.($section->header?'':' empty').'" onclick="CCTI.editContent(this, '.$pid.', '.$sid.', \'sectionheader\', 0, 0)">';
						echo ($section->header?$section->header:'&nbsp;');
					echo '</td>';
					if( $snum == 0 && $program->show_occ_titles )
					{
						echo '<td class="ccti_occu_head">List occupational titles here</td>';
					}
	
					if( $snum == 0 && $program->header == '' )
					{
						img_head($program, $program->headright, 'right', $pid);
					}
				echo '</tr>';
			}
	
			foreach( $section->labels_x as $lxi=>$lx )  // multiple rows of x labels supported
			{
				echo '<tr>';
				for( $i=0; $i<($program->content_cols-$program->show_occ_titles); $i )  // don't increment here
				{
					if( $snum == 0 && $lxi == 0 && $i == 0 && $section->header == '' && $program->header == '' )
					{
						img_head($program, $program->headleft, 'left', $pid);
					}
	
					if( $lxi == 0 && $i == 0 && $section->header == '' )
					{
						echo '<td rowspan="'.count($section->labels_x).'" class="ccti_xy_header">'.($section->label_xy->text?$section->label_xy->text:'.&nbsp;').'</td>';
					}
	
					if( array_key_exists($i, $lx) )
					{
						$label = $lx[$i];
						echo '<td colspan="'.$label->colspan.'" class="ccti_col_header" onclick="CCTI.editContent(this, '.$pid.', '.$sid.', \'label\', '.$lxi.', '.$i.', \'x\')">'.($label->text).'</td>';
						$i += $label->colspan;
					}
					else
					{
						echo '<td class="ccti_col_header" onclick="CCTI.editContent(this, '.$pid.', '.$sid.', \'label\', '.$lxi.', '.$i.', \'x\')">&nbsp;</td>';
						$i++;
					}
	
					if( $snum == 0 && $lxi == 0 && $i >= ($program->content_cols-$program->show_occ_titles) && $section->header == '' && $program->header == '' )
					{
						img_head($program, $program->headright, 'right', $pid);
					}
				}
	
				echo '</tr>';
			}
	
	
			$content_width = 700;
			if( $program->show_occ_titles ) $content_width -= 100;
	
			$cells_spanned = array();
			for( $i=0; $i<$section->num_rows; $i++ )
			{
				echo '<tr>';
					echo '<td class="ccti_row_header" onclick="CCTI.editContent(this, '.$pid.', '.$sid.', \'label\', '.$i.', 0, \'y\')">';
						if( array_key_exists($i, $section->labels_y) )
							echo $section->labels_y[$i][0]->text;
						else
							echo '&nbsp;';
					echo '</td>';
					for( $j=0; $j<$program->num_columns; $j++ )
					{
						if( !in_array($i.','.$j, $cells_spanned) )
						{
							if( $section->content[$i]->offsetExists($j) )
							{
								echo '<td onclick="CCTI.editContent(this, '.$pid.','.$sid.',\'data\','.$i.','.$j.')" colspan="'.$section->content[$i][$j]->colspan.'" width="'.round($section->content[$i][$j]->colspan / $program->num_columns * $content_width).'" class="ccti_content">';
								echo $section->content[$i][$j]->text;
								echo '</td>';
								if( $section->content[$i][$j]->colspan > 1 )
								{
									for( $ci=0; $ci<$section->content[$i][$j]->colspan; $ci++ )
									{
										$cells_spanned[] = $i.','.($j+$ci);
									}
								}
							}
							else
							{
								echo '<td onclick="CCTI.editContent(this, '.$pid.', '.$sid.',\'data\','.$i.', '.$j.')" class="ccti_content" width="'.round(1 / $program->num_columns * $content_width).'">';
								echo '&nbsp;';
								echo '</td>';
							}
						}
					}
					if( $snum == 0 && $i == 0 && $program->show_occ_titles )
					{
						echo '<td rowspan="'.($program->content_rows-1).'" width="100" onclick="CCTI.editContent(this, '.$pid.','.$sid.',\'occ_titles\')" class="ccti_content occ">';
						echo $program->occ_titles==""?"&nbsp;":$program->occ_titles;
						echo '</td>';
					}
				echo '</tr>';
			}
	
			$snum++;
		}
	
		echo '<tr>';
			echo '<td colspan="'.($program->num_columns+1+$program->show_occ_titles).'" class="ccti_footer" onclick="CCTI.editContent(this, '.$pid.','.$sid.',\'footer\')">';
			echo ($program->footer?$program->footer:'&nbsp;');
			echo '</td>';
		echo '</tr>';
	
		echo '</table>';
		echo '<br>';
		
		$pnum++;
	}
	
	echo '</div>';
	
	PrintFooter();

}

function img_head(&$program, $text, $type, $pid) 
{
	echo '<td rowspan="'.$program->total_rows.'" onclick="CCTI.editImgHead('.$pid.',\''.$type.'\')" class="ccti_program_head'.$type.'">';
		echo '<img src="/files/cctiv/'.base64_encode($text).'.png" alt="'.$text.'" id="head'.$type.$pid.'">';
	echo '</td>';
}


function copyVersion($version_id) {
	global $DB;

	$drawing = GetDrawingInfo($version_id, 'ccti');

	// first get the title information
	$drawing_main = $DB->SingleQuery("SELECT * FROM ccti_drawing_main WHERE id=" . $drawing['parent_id']);

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
		$newdrawing['code'] = CreateDrawingCodeFromTitle($newdrawing['name'],$newdrawing['school_id'],0,'ccti');
		$newdrawing['date_created'] = $DB->SQLDate();
		$newdrawing['last_modified'] = $DB->SQLDate();
		$newdrawing['created_by'] = $_SESSION['user_id'];
		$newdrawing['last_modified_by'] = $_SESSION['user_id'];
		$new_id = $DB->Insert('ccti_drawing_main',$newdrawing);
		$drawing_main = $DB->SingleQuery("SELECT * FROM ccti_drawing_main WHERE id=".$new_id);
		$version['next_num'] = 1;
	} else {
		// find the greatest version number for this drawing
		$version = $DB->SingleQuery("SELECT (MAX(version_num)+1) AS next_num FROM ccti_drawings WHERE parent_id=".$drawing_main['id']);
	}

	$content = array();
	$content['version_num'] = $version['next_num'];
	$content['date_created'] = $DB->SQLDate();
	$content['created_by'] = $_SESSION['user_id'];
	$content['last_modified'] = $DB->SQLDate();
	$content['last_modified_by'] = $_SESSION['user_id'];
	$content['parent_id'] = $drawing_main['id'];

	$new_version_id = $DB->Insert('ccti_drawings',$content);


	$programs = $DB->MultiQuery('SELECT * FROM ccti_programs WHERE drawing_id='.$version_id);
	foreach( $programs as $p ) 
	{
		$old_program_id = $p['id'];
		unset($p['id']);
		$p['`index`'] = $p['index'];
		unset($p['index']);
		$p['drawing_id'] = $new_version_id;
		$new_program_id = $DB->Insert('ccti_programs', $p);

		$sections = $DB->MultiQuery('SELECT * FROM ccti_sections WHERE program_id='.$old_program_id);
		foreach( $sections as $s ) 
		{
			$old_section_id = $s['id'];
			unset($s['id']);
			$s['`index`'] = $s['index'];
			unset($s['index']);
			$s['program_id'] = $new_program_id;
			$new_section_id = $DB->Insert('ccti_sections', $s);

			$section_labels = $DB->MultiQuery('SELECT * FROM ccti_section_labels WHERE section_id='.$old_section_id);
			foreach( $section_labels as $sl ) 
			{
				unset($sl['id']);
				$sl['section_id'] = $new_section_id;
				$DB->Insert('ccti_section_labels', $sl);
			}
		
			$data = $DB->MultiQuery('SELECT * FROM ccti_data WHERE section_id='.$old_section_id);
			foreach( $data as $d ) 
			{
				unset($d['id']);
				$d['section_id'] = $new_section_id;
				$DB->Insert('ccti_data', $d);
			}
		
		}	
	}

	if (Request('from_popup') == 'true') {
		header("Location: /a/copy_success_popup.php?mode=ccti&version_id=$new_version_id&copy_to=$copy_to&create=$create");
	}
	else {
		header("Location: ".$_SERVER['PHP_SELF']."?action=draw&version_id=".$new_version_id);
	}
}


function showDrawingInfo() {
global $DB;
	PrintHeader();

	$drawing = $DB->SingleQuery("SELECT ccti_drawing_main.*
		FROM ccti_drawing_main
		WHERE ccti_drawing_main.id=".intval(Request('id')));
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
	require('view/drawings/drawing_info.php');
}

function showVersionInfo() {
	global $DB, $MODE;
	PrintHeader();
	$version_id = Request('version_id');
	require('view/drawings/version_info.php');
	PrintFooter();
}

function ShowInfobar() {
	require('view/ccti/infobar.php');
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
	require('view/ccti/read_only_form.php');
}

function showToolbarAndHelp($publishAllowed, $helpFile = false) {
	require('view/ccti/toolbar.php');
	require('view/ccti/helpbar.php');
}




?>