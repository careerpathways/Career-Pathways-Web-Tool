<?php
chdir('..');
include('inc.php');

ModuleInit('hs_settings');

if( IsAdmin() ) {
	// Allow admins to edit headers for any schools
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
				window.location.href = "/a/hs_settings.php?school_id="+id;
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
				$('#header_form').submit();	
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
		if( $school_id ) ShowSchoolForm($school_id);
	PrintFooter();
}





function ShowSchoolForm($id="") {
global $DB;
	
	$school = $DB->SingleQuery("SELECT * FROM schools WHERE id=$id");
	if( !is_array($school) ) {
		echo 'Specified record does not exist.';
		return false;
	}
	
	echo '<h2>' . $school['school_name'] . '</h2>';
	?>
		<p><span class="red">NOTE:</span> Changes are saved automatically. Changes to the default column headers will affect any drawings created in the future.</p>
		<p>Existing drawings will not be affected by changes made here. Existing versions copied into NEW versions will retain the columns of the existing drawing.</p>
		<p>Click and drag to re-order the columns</p>
	<?php
	echo '<h3>Default Column Headers</h3>';
	
	$defaults = $DB->MultiQuery('SELECT * FROM post_default_col WHERE school_id='.$id.' ORDER BY num');
	?>
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="header_form">
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
	<input type="hidden" name="school_id" value="<?= $id ?>" />
	</form>
	<?php
}


function ShowSchoolChooser()
{
	global $DB;

	if( IsAdmin() ) {
		echo '<div style="margin-bottom:10px;">';
		echo 'Choose School: ';
		$schools_ = $DB->VerticalQuery('SELECT id, school_name FROM schools WHERE organization_type="HS" ORDER BY school_name','school_name','id');
		$schools = array("-1"=>'') + $schools_;
		echo GenerateSelectBox($schools,'ss',-1,'switch_school(this.value)');
		echo '</div>';
	}

}


?>
