<?php
include("inc.php");


if( $_SERVER['REMOTE_ADDR'] != '207.192.71.230' ) {
	die();
}


header("Content-type: text/plain");

$schools = $DB->MultiQuery("
	SELECT schools.*
	FROM schools
	INNER JOIN users ON users.school_id=schools.id
	GROUP BY schools.id
	ORDER BY school_name
	");


foreach( $schools as $s ) {

	$users = $DB->MultiQuery("
		SELECT users.id, first_name, last_name, email, lev.name AS user_level, last_logon, last_logon_ip
		FROM users, admin_user_levels AS lev
		WHERE school_id=".$s['id']."
			AND lev.level = users.user_level
		");
	foreach( $users as $u ) {
		echo $u['email']."\n";
	}

}

?>
