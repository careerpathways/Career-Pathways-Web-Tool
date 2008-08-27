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

	$wiki =& new Text_Wiki();
	$xhtml = $wiki->transform($n['text'], 'Xhtml');
	echo $xhtml;

	echo '<br>';
}

PrintFooter();

?>
