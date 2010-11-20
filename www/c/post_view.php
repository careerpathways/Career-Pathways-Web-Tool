<?php
chdir("..");
require_once("inc.php");
require_once("POSTChart.inc.php");

$drawing_id = 0;


if( Request('version_id') ) {
	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, schools.id AS school_id, d.id, sk.title AS skillset
		FROM post_drawing_main AS main
		JOIN post_drawings AS d ON d.parent_id = main.id
		JOIN schools ON main.school_id = schools.id
		LEFT JOIN oregon_skillsets AS sk ON main.skillset_id = sk.id
		WHERE deleted = 0
			AND d.id='.intval(Request('version_id')).'
			AND main.id='.intval(Request('drawing_id')));
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
		$page_title = $drawing['name'];
	}
	else
		drawing_not_found('post', Request('drawing_id'), Request('version_id'));

} elseif( Request('drawing_id') ) {
	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, schools.id AS school_id, d.id, sk.title AS skillset
		FROM post_drawing_main AS main
		JOIN post_drawings AS d ON d.parent_id = main.id
		JOIN schools ON main.school_id = schools.id
		LEFT JOIN oregon_skillsets AS sk ON main.skillset_id = sk.id
		WHERE published = 1
			AND deleted = 0
			AND main.id='.intval(Request('drawing_id')));
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
		$page_title = $drawing['name'];
	}
	else
		drawing_not_found('post', Request('drawing_id'));
	
} elseif( Request('page') == 'published' ) {
	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, schools.id AS school_id, d.id, sk.title AS skillset
		FROM post_drawing_main AS main
		JOIN post_drawings AS d ON d.parent_id = main.id
		JOIN schools ON main.school_id = schools.id
		LEFT JOIN oregon_skillsets AS sk ON main.skillset_id = sk.id
		WHERE published = 1
			AND deleted = 0
			AND code="'.Request('d').'"');
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
		$page_title = $drawing['name'];
	}
	else
		drawing_not_found('post', 0, 0, Request('d'));
		
} elseif( Request('page') == 'version') {

	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, schools.id AS school_id, d.id, sk.title AS skillset
		FROM post_drawing_main AS main
		JOIN post_drawings AS d ON d.parent_id = main.id
		JOIN schools ON main.school_id = schools.id
		LEFT JOIN oregon_skillsets AS sk ON main.skillset_id = sk.id
		WHERE version_num = '.Request('v').'
			AND deleted = 0
			AND code="'.Request('d').'"');
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
		$page_title = $drawing['name'];
	}
	else
		drawing_not_found('post', 0, 0, Request('d'), Request('v'));

}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?= ($drawing_id==0?'Not Found':$page_title) ?></title>
	<link rel="stylesheet" href="/c/pstyle.css" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
</head>
<body>

<?php
if( $drawing_id == 0 )
{
	echo 'No published versions of this drawing are available.';
}
else
{
	echo '<div id="post_title_container">';
		echo '<div id="post_title">';
			echo '<img src="/files/titles/post/' . base64_encode($drawing['school_abbr']) . '/' . base64_encode($page_title) . '.png" alt="' . $page_title . '" width="800" height="19" />';
		echo '</div>';
		if( $drawing['skillset'] ) {
			echo '<div id="skillset">';
				echo l('skillset name') . ': ' . $drawing['skillset'];
			echo '</div>';
		}
	echo '</div>';

	$post = POSTChart::create($drawing_id);
	$post->display();
}

include('view/course_description_include.php');
?>
<script type="text/javascript">
	$(function(){
		$(".post_cell .cell_container").each(function(){
			if($(this).find("img").length > 0) {
				var h = $(this).parent(".post_cell").height();
				$(this).css({
					height: h + "px"
				});
			}
		});
	});
</script>

</body>
</html>