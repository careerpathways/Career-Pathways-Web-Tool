<?php
chdir("..");
include("inc.php");

ModuleInit('drawings');

if( Request('id') ) {

	$drawing = $DB->SingleQuery("SELECT *
		FROM drawings, drawing_main
		WHERE drawings.parent_id=drawing_main.id
		AND drawing_main.id=".intval(Request('id')));
	if( !(Request('id') == "" || is_array($drawing) && (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id'])) ) {
		// permissions error
		die();
	}

	$school_id = $DB->GetValue('school_id', 'drawing_main', intval($_REQUEST['id']));

	$content = array();
	$content['name'] = $_REQUEST['title'];
	$content['last_modified'] = $DB->SQLDate();
	$content['last_modified_by'] = $_SESSION['user_id'];
	$content['code'] = CreateDrawingCodeFromTitle($content['name'],$school_id,intval($_REQUEST['id']));

	$DB->Update('drawing_main',$content,intval($_REQUEST['id']));

	echo $content['code'];
}

if( Request('drawing_id') ) {

	// permissions check
	$drawing = GetDrawingInfo(intval(Request('drawing_id')));
	if( !is_array($drawing) || (!IsAdmin() && $_SESSION['school_id'] != $drawing['school_id']) ) {
		die();
	}

	if( Request('note') ) {
		$content = array();
		$content['note'] = $_REQUEST['note'];
		$DB->Update('drawings',$content,intval($_REQUEST['drawing_id']));
	}

}

?>