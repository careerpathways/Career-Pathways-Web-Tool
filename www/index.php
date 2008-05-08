<?php
include("inc.php");
require_once "Text/Wiki.php";


RequireLogin();


PrintHeader();

echo '<div id="news">';


$news = $DB->MultiQuery("
	SELECT news.id, news.date, news.text, news.caption,
		CONCAT(users.first_name,' ',users.last_name) AS name
	FROM news, users
	WHERE user_id=users.id AND active=1
	ORDER BY sort_index");

foreach( $news as $n ) {

	echo '<div class="news_header">'.$n['caption'].'</div>';
	/*
	if( $DB->Date('n.d.Y',$n['date']) == date('n.d.Y') ) {
		$time = " g:ia";
	} else {
		$time = "";
	}
	echo '<div class="news_date">'.$DB->Date("n.d.y".$time,$n['date']).' | Posted by '.$n['name'].'</div>';
	*/

	$wiki =& new Text_Wiki();
	$xhtml = $wiki->transform($n['text'], 'Xhtml');
	echo $xhtml;

	echo '<br>';
}




echo '</div>';

echo str_repeat('<br>',20);


PrintFooter();



?>