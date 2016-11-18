<?php
chdir("..");
require_once("inc.php");
require_once("POSTChart.inc.php");

$drawing_id = 0; //version id, not drawing main id

if( Request('version_id') ) {
	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, schools.id AS school_id, d.id, sk.title AS skillset
		FROM post_drawing_main AS main
		JOIN post_drawings AS d ON d.parent_id = main.id
		JOIN schools ON main.school_id = schools.id
		LEFT JOIN oregon_skillsets AS sk ON main.skillset_id = sk.id
		WHERE deleted = 0
			AND d.id='.intval(Request('version_id')).'
			AND main.id='.intval(Request('drawing_id')));
	$drawing_main_id = Request('drawing_id');
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

	$drawing_main_id = Request('drawing_id');
	if( is_array($drawing) ) {
		if( $drawing['program_id'] > 0 ){
			$program = $DB->SingleQuery('SELECT * FROM programs WHERE id = '.$drawing['program_id']);
			$drawing['name'] = $program['title'];
		}

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
	$drawing_main_id = Request('d');
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
		$page_title = GetDrawingName($drawing_main_id, 'post');
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
	$drawing_main_id = Request('d');
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
		$page_title = $drawing['name'];
	}
	else
		drawing_not_found('post', 0, 0, Request('d'), Request('v'));

}

$pdf_url = getBaseUrl().'/pdf/post/$$/%%.pdf';
$pdf_url = str_replace(
    array('$$', '%%'),
    array(
        $drawing_main_id,
        CleanDrawingCode(
            GetDrawingName($drawing_main_id, 'post')
        ),
    ),
    $pdf_url
);

//create a boolean for the case that this is a pdf render
if(isset($_GET['isPDF'])){
    $isPDF = true;
} else {
    $isPDF = false;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?= ($drawing_id==0?'Not Found':$page_title) ?></title>
	<link rel="stylesheet" href="<?= getBaseUrl(); ?>/c/pstyle.css" />
	<?php if(defined('SITE_TEMPLATE') && file_exists(SITE_TEMPLATE . 'styles-header.css')): ?>
		<link rel="stylesheet" href="<?= getBaseUrl(); ?>/site-template/styles-header.css" />
	<?php endif; ?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" media="screen">
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <style>
    .title_skillset {
        font-size:8pt;
        font-weight:bold;
        float:left;
    }
    .drawing-info {
        font-size:8pt;
        font-weight: bold;
        text-align:right;
        padding: 0 3px 10px 3px;
    }
    .drawing-info a {
        color: #001133;
        text-decoration: none;
    }
    </style>
</head>
<body>

<?php
if( $drawing_id == 0 )
{
	echo 'No published versions of this drawing are available.';
}
else
{
    ?>
	<div id="post_title_container">
		<div id="post_title" class="title_img"><?= ShowPostHeader($drawing_main_id) ?></div>

        <div class="drawing-info">
            <div class="title_skillset">
                <?= l('skillset name')?>: <?= $drawing['skillset'] ?>
            </div>

            <?php if ($drawing['show_updated']): ?>
                <?php $last_modified_time = strtotime($drawing['last_modified']); ?>
                <div class="last_modified">
                    Last Updated: <?= date('n-j-Y', $last_modified_time) ?>
                </div>
            <?php endif; ?>

		    <?php if (1$isPDF): ?>
	            <?php if ($drawing['show_pdf_ada_links']): ?>
	            <div class="alt-links">
	                <a target="_blank" href="<?= $pdf_url ?>"><i class="fa fa-file-pdf-o"></i> Printable PDF</a>
	            </div>
	            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div style="clear:both;height:0;"></div>
<?php

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

<?php
if(isset($SITE) && method_exists($SITE, 'google_analytics')){
	echo $SITE->google_analytics(l('google analytics drawings'));
}
?>

</body>
</html>
