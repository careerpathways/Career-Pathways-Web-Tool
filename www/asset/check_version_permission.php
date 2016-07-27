<?php
/**
 * Checks if user can edit drawing version by id.
 */

//include('admin_inc.php');

//$can_edit_version = CanEditVersion($_GET['version_id'], 'post', false);


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

$can_edit_version = CanEditVersion(6832);
//$can_edit_version = TestFunction();

header('Content-type: application/json');
echo json_encode($can_edit_version);
