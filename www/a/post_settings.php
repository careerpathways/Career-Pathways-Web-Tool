<?php
chdir('..');
include('inc.php');
require_once("POSTChart.inc.php");

ModuleInit('post_settings');

if( IsAdmin() ) {
	// Allow admins to edit defaults for any schools
	if( Request('school_id') ) {
		$school_id = $_REQUEST['school_id'];
	} else {
		$school_id = 0;
	}
} else {
	// Force non-admins to their school
	$school_id = $_SESSION['school_id'];
}

if( PostRequest() && $school_id )
{
	if( Request('action') == 'add_default_row' )
	{
		$school = $DB->SingleQuery('SELECT * FROM schools WHERE id = ' . $school_id);
		
		switch(Request('type'))
		{
			case 'prereq':
			case 'electives':
			case 'unlabeled':
				// find the last row of this type
				$last_row = $DB->SingleQuery('SELECT * FROM post_default_row WHERE school_id='.$school_id.' AND row_type="'.Request('type').'" ORDER BY row_year DESC LIMIT 1');
				$next_row = $last_row['row_year']+1;
				$row_data = array('school_id'=>$school_id, 'row_type'=>Request('type'), 'row_year'=>$next_row, 'title'=>Request('title'));
				break;

			case 'term':
			case 'qtr':
				$row_data = array('school_id'=>$school_id, 'row_type'=>'term');
				if(Request('qtr'))
				{
					$row_data['row_qtr'] = Request('qtr');
				}
				else
				{
					$row_data['row_year'] = Request('year');
					$row_data['row_term'] = Request('term');
				}
				break;

			default:
				die();
		}
		
		$row_id = $DB->Insert('post_default_row', $row_data);
		
		ShowDefaultRows($school);
		die();
	}
	
	if( Request('action') == 'delete_default_row' )
	{
		$school = $DB->SingleQuery('SELECT * FROM schools WHERE id = ' . $school_id);
	
		$DB->Query('DELETE FROM post_default_row WHERE school_id = ' . $school_id . ' AND id = ' . intval(Request('row_id')));

		ShowDefaultRows($school);
		die();
	}



	if( trim(Request('new_title')) )
	{
		$max = $DB->SingleQuery('SELECT MAX(num) as max FROM post_default_col WHERE school_id=' . intval($school_id));
		
		$data = array();
		$data['school_id'] = $school_id;
		$data['title'] = trim($_REQUEST['new_title']);
		$data['num'] = $max['max'] + 1;
		$DB->Insert('post_default_col', $data);
	}
	
	if( Request('delete_id') )
	{
		$DB->Query('DELETE FROM post_default_col WHERE school_id=' . intval($school_id). ' AND id=' . intval(Request('delete_id')));
		echo "1";
		die();
	}
	
	if( Request('mode') == 'sorting' && is_array(Request('head')) )
	{
		for( $i=0; $i<count($_REQUEST['head']); $i++ )
		{
			$DB->Query('UPDATE post_default_col SET num=' . ($i+1) . ' WHERE school_id=' . $school_id . ' AND id=' . intval($_REQUEST['head'][$i]));
		}
		echo "1";
		die();
	}

	header('Location: ' . $_SERVER['PHP_SELF'] . '?school_id=' . $school_id);
	die();
}
else
{
	$TEMPLATE->addl_styles[]  = '/c/pstyle.css';
	$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';
	$TEMPLATE->addl_scripts[] = '/common/jquery/jquery-ui.js';
	PrintHeader();
		?>
		<script type="text/javascript">

			$(document).ready(function() {
				$("#new_title").focus();
				$(".sortable_header").sortable({
					items: 'li:not(.addnew)',
					placeholder: "ui-selected",
					axis: "y",
					revert: false,
					scroll: false,
					update: function(e, ui) {
						var data = $(".sortable_header").sortable("serialize");
						data += '&mode=sorting';
						$.ajax({
							type: 'POST',
							url: '<?= $_SERVER['PHP_SELF'] ?>?school_id=<?= $school_id ?>',
							data: data
						});
					}
				});
			});
	
			function switch_school(id) {
				window.location.href = "/a/post_settings.php?school_id="+id;
			}

			function delete_header(id)
			{
				$.ajax({
					type: 'POST',
					url: '<?= $_SERVER['PHP_SELF'] ?>?school_id=<?= $school_id ?>',
					data: {
						school_id: <?= $school_id ?>,
						delete_id: id
					},
					complete: function(http, status) {
						window.location.href = '<?= $_SERVER['PHP_SELF'] ?>?school_id=<?= $school_id ?>';
					}
				});
			}
			
			function save_header()
			{
				$.ajax({
					type: 'POST',
					url: '<?= $_SERVER['PHP_SELF'] ?>?school_id=<?= $school_id ?>',
					data: {
						school_id: <?= $school_id ?>,
						new_title: $("#new_title").val()
					}, 
					complete: function(http, status) {
						window.location.href = '<?= $_SERVER['PHP_SELF'] ?>?school_id=<?= $school_id ?>';
					}
				});
			}

		</script>
		<style type="text/css">
			ul.sortable_header {
				width: 400px;
				margin: 0;
				padding: 0;
				list-style-type: none;
				list-style-position: inside;
				border-collapse: collapse;
			}
			ul.sortable_header li {
				margin: 0;
				padding-top: 4px;
				padding-bottom: 4px;
				padding-left: 8px;
				padding-right: 8px;
				background-color: #dddddd;
				font-weight: bold;
				border-top: 1px #999999 solid;
				border-left: 1px #999999 solid;
				border-right: 1px #999999 solid;
				border-bottom: 1px #999999 solid;
				cursor: hand;
				cursor: pointer;
				clear: both;
			}
			ul.sortable_header li a {
				float: right;
			}
			ul.sortable_header li.addnew {
				border-bottom: 1px #999999 solid;
			}
			ul.sortable_header li.ui-selected {
				background-color: #FFFFAA;
				height: 14px;
			}
		</style>
		<?php

		ShowSchoolChooser();
		if( $school_id ) 
		{
			$school = $DB->SingleQuery("SELECT * FROM schools WHERE id=$school_id");
			if( !is_array($school) ) {
				echo 'Specified record does not exist.';
				die();
			}

			echo '<h2>' . $school['school_name'] . '</h2>';
			
			?>
			<p><span class="red">NOTE:</span> Changes are saved automatically. Changes to the default column headers will affect any drawings created in the future.</p>
			<p>Existing drawings will not be affected by changes made here. Existing versions copied into NEW versions will retain the columns of the existing drawing.</p>
			<?php
			
			if($school['organization_type'] == 'HS') {
				ShowSchoolHeaderForm($school);
				echo '<div style="margin-top: 20px;"></div>';
			}

			ShowSchoolRowForm($school);

			echo '<input type="hidden" name="school_id" value="' . $school['id'] . '" />';
		}
	PrintFooter();
}





function ShowSchoolHeaderForm($school) {
	global $DB;
	
	echo '<p>Click and drag to re-order the columns</p>';

	echo '<h3>Default Column Headers</h3>';
	
	$defaults = $DB->MultiQuery('SELECT * FROM post_default_col WHERE school_id='.$school['id'].' ORDER BY num');
	?>
	<div class="sortable_container">
	<ul class="sortable_header">
	<?php
	foreach($defaults as $d)
	{
	?>
		<li id="head_<?= $d['id'] ?>">
		<a href="javascript:delete_header(<?= $d['id'] ?>)"><img src="/common/silk/cross.png" width="16" height="16" /></a>
		<?= $d['title'] ?>
		</li>
	<?php
	}
	?>
	<li class="addnew"><a href="javascript:save_header()"><img src="/common/silk/add.png" width="16" height="16"></a><input type="textbox" size="30" name="new_title" id="new_title" /></li>
	</ul>
	</div>
	<?php
}

function ShowSchoolRowForm($school) 
{
	global $DB;
	
	$defaults = $DB->MultiQuery('SELECT * FROM post_default_col WHERE school_id='.$school['id'].' ORDER BY num');

	$years = array(1=>1, 2, 3, 4, 5, 6);
	$terms = array('M'=>'Summer', 'F'=>'Fall', 'W'=>'Winter', 'S'=>'Spring', 'U'=>'Summer');
	$quarters = array(1=>1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16);

	?>
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
			font-size: 1.4em;
		}
		.addRowText {
			font-size: 1.3em;
			margin-left: 10px;
		}
		#addRowTable td {
			vertical-align: middle;
			border-bottom: 1px #ddd solid;
			padding: 6px;
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
				var data = {
					action: "add_default_row", 
					school_id: <?= $school['id'] ?>
				};

				switch( type ) {
					case "prereq":
					case "unlabeled":
					case "electives":
						data.type = type;
						if(type == "prereq") {
							data.title = $("#addRow_prereq_title").val();
						} else {
							data.title = $("#addRow_unlabeled_title").val();
						}
						break;
					case "term":
						data.type = type;
						data.year = jQuery("#addYear").val();
						data.term = jQuery("#addTerm").val();
						break;
					case "qtr":
						data.type = type;
						data.qtr  = jQuery("#addQtr").val();
						break;
					default:
						return false;
				}
				
				jQuery.post("/a/post_settings.php", data,
					function(data){
						jQuery("#rowList").html(data);
						bindDeleteButtons();
					}, "HTML");
			});
		});

		function bindDeleteButtons() {
			jQuery(".deleteBtn").click(function(){
				jQuery.post("/a/post_settings.php", {
					action: "delete_default_row",
					row_id: jQuery(this).attr("id").split("_")[1],
					school_id: <?= $school['id'] ?>
				},
				function(data){
					jQuery("#rowList").html(data);
					bindDeleteButtons();
				}, "HTML");
			})
		}
	</script>
	<table><tr>
	<td valign="top" style="padding-left:10px; width: 300px;">
		<div class="rowConfigHead">Default Rows</div>
		<div id="rowList">
		<?php
		ShowDefaultRows($school);
		?>
		</div>
	</td>
	<td valign="top" style="padding-left:30px">
	
		<div class="rowConfigHead">Add Row</div>
		<table id="addRowTable">
		<?php if( $school['organization_type'] != 'HS' ) { ?>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_prereq" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText"><input type="text" id="addRow_prereq_title" style="width: 120px;" /></div></td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_qtr" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td>
					<div class="addRowText">Term: <?= GenerateSelectBox($quarters, 'addQtr') ?>
				</td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_term" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td>
					<div class="addRowText">
						Year: <?= GenerateSelectBox($years, 'addYear') ?><br />
						Term: <?= GenerateSelectBox($terms, 'addTerm') ?>
					</div>
				</td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_unlabeled" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText"><input type="text" id="addRow_unlabeled_title" style="width: 120px;" /></div></td>
			</tr>
		<?php } else { ?>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_prereq" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText"><input type="text" id="addRow_prereq_title" style="width: 120px;" /></div></div></td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_term" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText">Year: <?= GenerateSelectBox(array(9=>9, 10, 11, 12), 'addYear') ?></div></td>
			</tr>
			<tr>
				<td><a href="javascript:void(0);" id="addRow_unlabeled" class="addRowLink"><?= SilkIcon('arrow_left.png') ?></a></td>
				<td><div class="addRowText"><input type="text" id="addRow_unlabeled_title" style="width: 120px;" /></div></td>
			</tr>
		<?php } ?>
		</table>

	</td>
	</tr></table>

	<?php
}


function ShowDefaultRows(&$school)
{
	global $DB;

	$rows = GetDefaultPOSTRows($school['id']);
	
	foreach( $rows as $r )
	{
		echo '<div class="rowName">';
			echo '<a href="javascript:void(0);" id="deleteRow_'.$r['id'].'" class="deleteBtn">' . SilkIcon('cross.png') . '</a> ';
			if( $r['rowName'] == '' )
				echo '(blank)';
			else
				echo str_replace('<br />', ' ', $r['rowName']);
		echo '</div>';
	}
}



function ShowSchoolChooser()
{
	global $DB;

	if( IsAdmin() ) {
		echo '<div style="margin-bottom:10px;">';
			echo 'Select High School: ';
			$schools_ = $DB->VerticalQuery('SELECT id, school_name FROM schools WHERE organization_type="HS" ORDER BY school_name','school_name','id');
			$schools = array("-1"=>'') + $schools_;
			echo GenerateSelectBox($schools,'ss',-1,'switch_school(this.value)');
		echo '</div>';
		echo '<div style="margin-bottom:10px;">';
			echo 'Select College or Other Org: ';
			$schools_ = $DB->VerticalQuery('SELECT id, school_name FROM schools WHERE organization_type!="HS" ORDER BY school_name','school_name','id');
			$schools = array("-1"=>'') + $schools_;
			echo GenerateSelectBox($schools,'ss',-1,'switch_school(this.value)');
		echo '</div>';
	}

}
