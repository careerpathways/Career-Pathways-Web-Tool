<?php
/**
 * Copyright 2007-2010 by Oregon Department of Community Colleges and Workforce Development.
 * See LICENSE.htm for licensing information.
 */

include("inc.php");
require_once "Text/Wiki.php";


/*
  This is the dashboard page that every user is taken to after logging in. It is also visible to people
  before they log in, so needs to modify its behavior based on the current userlevel.
*/

$wiki = new Text_Wiki();


PrintHeader();

echo '<div style="width: 500px; float: left; background-color: #ffffff;">';
$news = $DB->SingleQuery("
        SELECT news.id, news.date, news.text, news.caption,
                CONCAT(users.first_name,' ',users.last_name) AS name
        FROM news, users
        WHERE user_id=users.id AND active=1 AND category='welcome'
        ORDER BY date DESC
        LIMIT 1");
        echo '<div class="news_header">'.$news['caption'].'</div>';
              
        $xhtml = $wiki->transform($news['text'], 'Xhtml');
        echo $xhtml; 
                
        echo '<br>';

echo '</div>'; 


if( IsLoggedIn() ) {

	ShowBrowserNotice();

	echo '<div id="dash_links">';
	echo '<div id="dash_links_title">Quick Links</div>';
	echo '<ul>';
		if( $_SESSION['user_level'] > CPUSER_STAFF ) {
			echo '<li><a href="/a/users.php?id">Add a User</a></li>';
		}
		if( $_SESSION['email'] == 'guest' ) {
			echo '<li><a href="/a/apply.php">Apply for an Account</a></li>';
		}
		echo '<li><a href="/a/help.php?template=bugreport">Report a Bug</a></li>';
		echo '<li><a href="/a/help.php?template=newfeature">Request a Feature</a></li>';
	echo '</ul>';
	echo '</div>';

} else {

	echo '<br><br><br>';
	ShowLoginForm();

}


echo '<div style="clear:both"></div>';

if( IsLoggedIn() ) {
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
}

if( IsLoggedIn() ) {
	echo '<br>';
	$news = $DB->MultiQuery("
		SELECT news.id, news.date, news.text, news.caption,
			CONCAT(users.first_name,' ',users.last_name) AS name
		FROM news, users
		WHERE user_id=users.id AND active=1 AND category='release_info'
		ORDER BY date DESC
		LIMIT 1");
	foreach( $news as $n ) {
		echo '<div class="news_header">Latest Release: '.$n['caption'].'</div>';
		echo '<div class="news_date">'.$DB->Date('F j, Y', $n['date']).'</div>';
	
		$xhtml = $wiki->transform($n['text'], 'Xhtml');
		echo $xhtml;
	
		echo '<br>';
	}

	echo '<br>';
	$news = $DB->MultiQuery("
		SELECT news.id, news.date, news.text, news.caption,
			CONCAT(users.first_name,' ',users.last_name) AS name
		FROM news, users
		WHERE user_id=users.id AND active=1 AND category='dashboard'
		ORDER BY `sort_index` DESC");
	foreach( $news as $n ) {
		echo '<div class="news_header">'.$n['caption'].'</div>';
	
		$xhtml = $wiki->transform($n['text'], 'Xhtml');
		echo $xhtml;
	
		echo '<br>';
	}
}


PrintFooter();




?>
