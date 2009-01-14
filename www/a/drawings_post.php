<?php
chdir("..");

include("inc.php");



//ModuleInit('drawings');


if( Request('id') ) {

	if( Request('mode') == 'pathways' ) {
		$drawing = $DB->SingleQuery("SELECT *
			FROM drawings, drawing_main
			WHERE drawings.parent_id=drawing_main.id
			AND drawing_main.id=".intval(Request('id')));
		$school_id = $DB->GetValue('school_id', 'drawing_main', intval($_REQUEST['id']));
		$main_table = 'drawing_main';
	} elseif( Request('mode') == 'ccti' ) {
		$drawing = $DB->SingleQuery("SELECT *
			FROM ccti_drawings, ccti_drawing_main
			WHERE ccti_drawings.parent_id=ccti_drawing_main.id
			AND ccti_drawing_main.id=".intval(Request('id')));
		$school_id = $DB->GetValue('school_id', 'ccti_drawing_main', intval($_REQUEST['id']));
		$main_table = 'ccti_drawing_main';
	}
	if( !(Request('id') == "" || is_array($drawing) && (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id'])) ) {
		// permissions error
		die();
	}


	$content = array();
	$content['name'] = $_REQUEST['title'];
	$content['last_modified'] = $DB->SQLDate();
	$content['last_modified_by'] = $_SESSION['user_id'];
	$content['code'] = CreateDrawingCodeFromTitle($content['name'],$school_id,intval($_REQUEST['id']));

	$DB->Update($main_table,$content,intval($_REQUEST['id']));

	echo $content['code'];
}

if( Request('drawing_id') ) {

	// permissions check
	$drawing = GetDrawingInfo(intval(Request('drawing_id')), Request('mode'));
	if( !is_array($drawing) || (!IsAdmin() && $_SESSION['school_id'] != $drawing['school_id']) ) {
		die();
	}

	if( Request('note') ) {
		$content = array();
		$content['note'] = $_REQUEST['note'];
		$DB->Update((Request('mode')=='pathways'?'drawings':'ccti_drawings'),$content,intval($_REQUEST['drawing_id']));
	}

}

?>