<?php
include("inc.php");
require_once "Text/Wiki.php";


RequireLogin();


PrintHeader();

echo '<div id="news">';

$pending = GetPendingUsers();
if( count($pending) > 0 ) {
	echo '<div class="news_header red">Pending User Requests</div>';
	if( count($pending) > 1 ) {
		echo '<p>There are '.count($pending).' pending user requests at your school. Please visit the "<a href="/a/users.php">Users</a>" page to review these requests.</p>';
	} else {
		echo '<p>There is one pending user request at your school. Please visit the "<a href="/a/users.php">Users</a>" page to review this request.</p>';
	}
	echo '<br><br>';
}


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