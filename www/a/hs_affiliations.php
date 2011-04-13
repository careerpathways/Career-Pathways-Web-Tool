<?php
chdir('..');
include('inc.php');

ModuleInit('hs_affiliations');

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
	if( Request('delete_id') )
	{
		$DB->Query('DELETE FROM hs_affiliations WHERE cc_id=' . intval($school_id). ' AND id=' . intval(Request('delete_id')));
		echo "1";
		die();
	}

	if( Request('create') )
	{
		$data = array();
		$data['cc_id'] = $school_id;
		if($_POST['hs_id'])
			$data['hs_id'] = $_POST['hs_id'];
		else
			$data['hs_id'] = $_POST['cc_id'];
		$DB->Insert('hs_affiliations', $data);
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

			function switch_school(id) {
				window.location.href = "/a/hs_affiliations.php?school_id="+id;
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
				border-top: 1px #999999 solid;
			}
			ul.sortable_header li {
				margin: 0;
				padding-top: 4px;
				padding-bottom: 4px;
				padding-left: 8px;
				padding-right: 8px;
				background-color: #dddddd;
				font-weight: bold;
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

	echo '<h3>Affiliated High Schools</h3>';
	
	$affiliations = $DB->MultiQuery('SELECT s.*, a.id AS af_id
		FROM hs_affiliations AS a
		JOIN schools AS s ON s.id=hs_id AND s.organization_type = "HS"
		WHERE cc_id='.$id.'
		ORDER BY school_name');
	?>
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="header_form">
	<ul class="sortable_header">
	<?php
	$hslist = '0';
	foreach($affiliations as $d)
	{
		?>
		<li id="head_<?= $d['af_id'] ?>">
		<a href="javascript:delete_header(<?= $d['af_id'] ?>)"><img src="/common/silk/cross.png" width="16" height="16" /></a>
		<?= $d['school_name'] ?>
		</li>
		<?php
		$hslist .= ',' . $d['id'];
	}

	$highschools = $DB->VerticalQuery('SELECT id, school_name FROM schools WHERE organization_type="HS" AND id NOT IN ('.$hslist.') ORDER BY school_name', 'school_name', 'id');
	if( count($highschools) > 0 )
	{
	?>
	<li class="addnew">
		<?php
			$highschools = array(0=>'') + $highschools;
			echo GenerateSelectBox($highschools, 'hs_id', 0);
			echo '<a href="javascript:save_header(\'hs\')" style="float:left; margin-right: 3px;"><img src="/common/silk/add.png" width="16" height="16"></a>';
		?>
	</li>
	<?php
	}
	?>
	</ul>
	
	<?php
	if($school['organization_type'] == 'Other') {
		echo '<h3>Affiliated Community Colleges</h3>';
		
		$affiliations = $DB->MultiQuery('SELECT s.*, a.id AS af_id
			FROM hs_affiliations AS a
			JOIN schools AS s ON s.id=hs_id AND s.organization_type = "CC"
			WHERE cc_id='.$id.'
			ORDER BY school_name');
		?>
		<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="header_form">
		<ul class="sortable_header">
		<?php
		$cclist = '0';
		foreach($affiliations as $d)
		{
			?>
			<li id="head_<?= $d['af_id'] ?>">
			<a href="javascript:delete_header(<?= $d['af_id'] ?>)"><img src="/common/silk/cross.png" width="16" height="16" /></a>
			<?= $d['school_name'] ?>
			</li>
			<?php
			$cclist .= ',' . $d['id'];
		}
	
		$colleges = $DB->VerticalQuery('SELECT id, school_name FROM schools WHERE organization_type="CC" AND id NOT IN ('.$cclist.') ORDER BY school_name', 'school_name', 'id');
		if( count($colleges) > 0 )
		{
		?>
		<li class="addnew">
			<?php
				$colleges = array(0=>'') + $colleges;
				echo GenerateSelectBox($colleges, 'cc_id', 0);
				echo '<a href="javascript:save_header(\'cc\')" style="float:left; margin-right: 3px;"><img src="/common/silk/add.png" width="16" height="16"></a>';
			?>
		</li>
		<?php
		}
		?>
		</ul>
	<?php
	} // end if org type is "Other"
	?>
	
	<input type="hidden" name="school_id" value="<?= $id ?>" />
	<input type="hidden" name="create" value="1" />
	</form>
	<?php
}


function ShowSchoolChooser()
{
	global $DB;

	if( IsAdmin() ) {
		echo '<div style="margin-bottom:10px;">';
		echo 'Choose School: ';
		$schools_ = $DB->VerticalQuery('SELECT id, school_name FROM schools WHERE organization_type!="HS" ORDER BY school_name','school_name','id');
		$schools = array("-1"=>'') + $schools_;
		echo GenerateSelectBox($schools,'ss',-1,'switch_school(this.value)');
		echo '</div>';
	}

}


?>
