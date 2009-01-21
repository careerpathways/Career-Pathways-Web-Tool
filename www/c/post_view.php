<?php
chdir("..");
require_once("inc.php");
require_once("POSTChart.inc.php");

$drawing_id = 0;

if( Request('page') == 'published' ) {
	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, d.id
		FROM post_drawing_main AS main, ccti_drawings AS d, schools 
		WHERE d.parent_id = main.id
			AND main.school_id = schools.id
			AND published = 1
			AND deleted = 0
			AND code="'.Request('d').'"');
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
	}
} elseif( Request('page') == 'version') {

	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, d.id
		FROM post_drawing_main AS main, ccti_drawings AS d, schools
		WHERE d.parent_id = main.id
			AND main.school_id = schools.id
			AND version_num = '.Request('v').'
			AND deleted = 0
			AND code="'.Request('d').'"');
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
	}
}

if( $drawing_id == 0 ) {
	die();
}

$page_title = 'test';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?=$page_title?></title>
	<link rel="stylesheet" href="/c/pstyle.css" />
</head>
<body>
	<div id="post_title">
		<img src="/files/titles/<?=base64_encode($drawing['school_abbr'])?>/<?=base64_encode($page_title)?>.png" alt="Career POST" />
	</div>
<?php
	$post = POSTChart::create($drawing_id);
	$post->display();
?>
</body>
</html>