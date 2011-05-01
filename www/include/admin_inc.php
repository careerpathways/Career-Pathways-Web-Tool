<?php

// abstractions to work with USER_ definitions in general.inc.php
function IsAdmin() {
	return $_SESSION['user_level'] >= CPUSER_STATEADMIN;
}
function IsSchoolAdmin() {
	return $_SESSION['user_level'] >= CPUSER_SCHOOLADMIN;
}
function IsWebmaster() {
	return $_SESSION['user_level'] >= CPUSER_WEBMASTER;
}
function IsStaff() {
	return $_SESSION['user_level'] >= CPUSER_STAFF;
}



function GetPendingUsers() {
global $DB;
	return $DB->MultiQuery("
		SELECT users.id, first_name, last_name, email, phone_number, lev.name AS user_level_name, user_level, last_logon, last_logon_ip, schools.school_name
		FROM users
		JOIN admin_user_levels AS lev ON lev.level = users.user_level
		LEFT JOIN schools ON school_id=schools.id
		WHERE (school_id=".$_SESSION['school_id']." OR ".(IsAdmin()?1:0).")
			AND new_user=1
		");
}



function UserCanEditCategory($cat_id) {
global $DB;
	if( IsAdmin() ) {
		return TRUE;
	} else {
		if( $_SESSION['user_level'] >= CPUSER_HIGHSCHOOL ) {
			if( !is_numeric($cat_id) ) {
				$DB->Query("SELECT id FROM admin_module WHERE name = '$cat_id'");
				$line = $DB->NextRecord();
				$cat_id = $line['id'];
			}
			$sql = "SELECT * FROM admin_level_module WHERE level<=".$_SESSION['user_level']." AND module_id=$cat_id";
			return (count($DB->MultiQuery($sql)) > 0);
		} else {
			return FALSE;
		}
	}
}

function GetDefaultPOSTRows($school_id) {
	global $DB;
	
	$rows = $DB->MultiQuery('SELECT * FROM post_default_row 
		WHERE school_id = ' . $school_id . '
		ORDER BY row_type, row_year, row_term, row_qtr, id');
	$school = $DB->SingleQuery("SELECT * FROM schools WHERE id = ". $school_id);

	if(count($rows) == 0) 
	{
		$rows = array();

		if($school['organization_type'] == 'HS') {
			for( $i=9; $i<=12; $i++ )
			{
				$row = array();
				$row['school_id'] = $school_id;
				$row['row_type'] = 'term';
				$row['row_year'] = $i;
				$row['row_term'] = '';
				$rows[] = $row;
			}
		}
		else {
			if(l('post row type') == 'year/term')
			{
				for( $i=1; $i<=6; $i++ )
				{
					$row = array();
					$row['school_id'] = $school_id;
					$row['row_type'] = 'term';
					$row['row_year'] = floor(($i-1) / 3) + 1;
					$row['row_term'] = (($i-1) % 3) + 2;
					$row['row_qtr'] = 0;
					$rows[] = $row;
				}
			}
			else
			{
				for( $i=1; $i<=8; $i++ )
				{
					$row = array();
					$row['school_id'] = $school_id;
					$row['row_type'] = 'term';
					$row['row_year'] = 0;
					$row['row_term'] = '';
					$row['row_qtr'] = $i;
					$rows[] = $row;
				}
			}
		}
		
		foreach($rows as $r) {
			$DB->Insert('post_default_row', $r);
		}

		$rows = $DB->MultiQuery('SELECT * FROM post_default_row WHERE school_id = ' . $school_id);
	}
	
	if($school['organization_type'] == 'HS')
		$postChart = new POSTChart_HS();
	else 
		$postChart = new POSTChart_CC();
	
	foreach($rows as $i=>$row) {
		$rows[$i]['rowName'] = $postChart->rowNameFromData($row);
	}

	return $rows;
}

function GetCategoriesForUser($id, $where="", $get_inverse=FALSE) {
global $DB;

	if( $get_inverse == TRUE ) {
	// get all categories the user is not in




	} else {
		$user = $DB->SingleQuery("SELECT user_level FROM users WHERE id = '$id'");

		if( $user['user_level'] == USER_ADMIN ) {
			$sql = "SELECT module.id, module.friendly_name AS name, module.name AS internal_name
					FROM admin_module AS module
					WHERE active = 1
					ORDER BY `order`";
		} else {
			$sql = "SELECT module.id, module.friendly_name AS name, module.name AS internal_name
					FROM admin_level_module AS level_module,
						admin_module AS module, users AS users
					WHERE (level_module.level <= ".$user['user_level']."
						AND level_module.module_id = module.id
						AND users.id = ".$id."
						AND active = 1)
					ORDER BY `order`";
		}
	}

	return $DB->MultiQuery($sql);
}

function ModuleInit($module) {
global $DB, $TEMPLATE, $MODULE_NAME, $MODULE_PAGETITLE;

	if( !UserCanEditCategory($module) ) {
		header("Location: /a/login.php?next=".urlencode($_SERVER['REQUEST_URI']));
		die();
	} else {
		//$DB->Query("UPDATE users SET last_module = '$module' WHERE id = '".$_SESSION['user_id']."'");
		$info = $DB->SingleQuery("SELECT page_title, friendly_name FROM admin_module WHERE name = '$module'");
		$MODULE_NAME = $info['friendly_name'];
		$MODULE_PAGETITLE = $info['page_title'];
	}
	$TEMPLATE->AddCrumb('/modules/'.$module.'.php', $MODULE_PAGETITLE);
}

function CanDeleteDrawing($drawing_id, $type='post') {
	global $DB;

	if( $type == 'post' )	
		$drawing = $DB->SingleQuery('SELECT * FROM post_drawing_main WHERE id='.$drawing_id);
	else
		$drawing = $DB->SingleQuery('SELECT * FROM drawing_main WHERE id='.$drawing_id);

	#if( @$drawing['published'] == 1 || @$drawing['frozen'] == 1 )
	#	return false;

	// state admins can delete anything
	if( IsAdmin() ) return true;

	// all staff can delete HS drawings in their affiliated list
	if( $type == 'post' && IsStaff() && strtolower($drawing['type']) == 'hs' ) 
	{
		$affl = GetAffiliatedSchools();
		if( array_key_exists($drawing['school_id'], $affl) )
			return true;
	}

	// school admins can delete any drawing at their school
	if( IsSchoolAdmin() && $_SESSION['school_id'] == $drawing['school_id'] ) return true;

	// anyone else can delete drawings created by them
	return $drawing['created_by'] == $_SESSION['user_id'];

}

function CanEditVersion($drawing_id, $mode='post', $check_published=true) {
	global $DB;
	
	if( $mode == 'post' )
		$tp = 'post_';
	else
		$tp = '';
	
	$drawing = $DB->SingleQuery('SELECT *, M.school_id'.($mode=='post'?', M.type':'').' 
		FROM '.$tp.'drawings D, '.$tp.'drawing_main M 
		WHERE D.id='.$drawing_id.' AND D.parent_id=M.id');

	if( $check_published ) {
		// ignore the fact that the drawing may be published
		if( $drawing['published'] == 1 || $drawing['frozen'] == 1 )
			return false;
	}

	if( IsAdmin() ) return true;

	// all staff can edit HS drawings in their affiliated list
	if( $mode == 'post' && IsStaff() ) 
	{
		$affl = GetAffiliatedSchools($drawing['type']);
		if( array_key_exists($drawing['school_id'], $affl) )
			return true;
	}

	return $_SESSION['school_id'] == $drawing['school_id'];
}

function IsAffiliatedWith($school_id) {
	global $DB;
	$school = $DB->SingleQuery('SELECT * FROM schools WHERE id = ' . $school_id);
	$affl = GetAffiliatedSchools($school['organization_type']);
	return array_key_exists($school_id, $affl);
}

function GetAffiliatedSchools($type='HS') {
	global $DB;
	
	$hsids = $DB->SingleQuery('SELECT GROUP_CONCAT(hs_id) AS hs FROM hs_affiliations WHERE cc_id='.$_SESSION['school_id']);
	if( $hsids['hs'] == '' ) $hsids['hs'] = 0;
	if(IsAdmin())
		return $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type="' . $type . '" ORDER BY school_name', 'school_name', 'id');
	else	
		return $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type="' . $type . '" AND id IN (0,'.$hsids['hs'].') ORDER BY school_name', 'school_name', 'id');
}

?>
