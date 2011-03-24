<?php
chdir("..");
include("inc.php");
require_once "Text/Wiki.php";

$valid_categories = $TEMPLATE->resource_categories();

parse_str($_SERVER['REDIRECT_QUERY_STRING'], $_REQUEST);
$category = $_REQUEST['page'];
if( !array_key_exists($category, $valid_categories) ) {
	PrintHeader();
	echo "error";
	PrintFooter();
	die();
}


$TEMPLATE->AddCrumb('/p/'.$category, $valid_categories[$category]);

PrintHeader();

$orderby = ($category == 'release_info' ? 'date DESC' : 'sort_index DESC'); 

$news = $DB->MultiQuery("
	SELECT news.id, news.date, news.text, news.caption,
		CONCAT(users.first_name,' ',users.last_name) AS name
	FROM news, users
	WHERE user_id=users.id AND active=1 AND category='$category'
	ORDER BY ".$orderby);

foreach( $news as $n ) {
	echo '<div class="news_header">'.$n['caption'].'</div>';
	if( $category == 'release_info' ) {
		echo '<div class="news_date">'.$DB->Date('F j, Y', $n['date']).'</div>';
	}

	$wiki = new Text_Wiki();
	$xhtml = $wiki->transform($n['text'], 'Xhtml');

	/*** for the embedding instructions ***/
	$colors = array('999999','cccccc','cf9d2b','e6d09e','295a76');
	$color_chart = '<table style="margin-left:20px">';
	foreach( $colors as $c ) {
		$color_chart .= '<tr><td width="30" height="20" style="background-color:#'.$c.'"></td><td>#'.strtoupper($c).'</td></tr>';
	}
	$color_chart .= '</table>';
	
	$xhtml = str_replace("\n".'<span style="color: #0000BB">&lt;?php'."\n\n", '<span>', $xhtml);
	$xhtml = str_replace('?&gt;','',$xhtml);
	
	$xhtml = str_replace("\$example_url", "examples.ctepathways.org", $xhtml);
	$xhtml = str_replace("\$oregon_url", "oregon.ctepathways.org", $xhtml);
	$xhtml = str_replace("\$color_chart", $color_chart, $xhtml);
	/****************/	

	echo $xhtml;

	echo '<br>';
}

PrintFooter();

?>
