<?php
include('stats.inc.php');

if( Request('from_date') ) {
	header("Content-type: text/plain");

	$from = date('y-m-d', strtotime(Request('from_date')));
	$to = date('y-m-d', strtotime(Request('to_date')));



	$d = $DB->MultiQuery('
		SELECT COUNT(*) AS num, school_name
		FROM (
			SELECT DISTINCT(user_id) AS user_id, school_id, school_name
			FROM login_history AS lh
			LEFT JOIN users u ON lh.user_id=u.id
			LEFT JOIN schools s ON u.school_id=s.id
			WHERE date >= "'.$from.'" AND date <= "'.$to.' 23:59:59"
				AND school_name IS NOT NULL
		) temp
		GROUP BY school_id
		ORDER BY num DESC');
	$total = 0;
	echo '<table width="300">';
	foreach( $d as $org ) {
		$total += $org['num'];
		echo '<tr><td>'.$org['num'].'</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.$total.'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";



	$d = $DB->MultiQuery('
		SELECT COUNT(*) AS num, school_name
		FROM (
			SELECT DISTINCT(u.id) AS user_id, school_id, school_name
			FROM users u
			LEFT JOIN schools s ON u.school_id=s.id
			WHERE u.date_created >= "'.$from.'" AND u.date_created <= "'.$to.' 23:59:59"
		) temp
		GROUP BY school_id
		ORDER BY num DESC');
	$total = 0;
	echo '<table width="300">';
	foreach( $d as $org ) {
		$total += $org['num'];
		echo '<tr><td>'.$org['num'].'</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.$total.'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";



	$d = $DB->MultiQuery('SELECT * FROM schools WHERE date_created>="'.$from.'" AND date_created<="'.$to.' 23:59:59"');
	echo '<table width="300">';
	foreach( $d as $org ) {
		echo '<tr><td>&nbsp;</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.count($d).'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";



	$d = $DB->MultiQuery('
		SELECT COUNT(*) AS num, school_name
		FROM (
			SELECT DISTINCT(dm.id), school_id, school_name
			FROM drawing_main AS dm
			LEFT JOIN schools s ON dm.school_id=s.id
			WHERE dm.date_created >= "'.$from.'" AND dm.date_created <= "'.$to.' 23:59:59"
				AND school_name IS NOT NULL
		) temp
		GROUP BY school_id
		ORDER BY num DESC');
	$total = 0;
	echo '<table width="300">';
	foreach( $d as $org ) {
		$total += $org['num'];
		echo '<tr><td>'.$org['num'].'</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.$total.'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";
	
	
	
	$d = $DB->MultiQuery('
		SELECT COUNT(*) AS num, school_name
		FROM (
			SELECT DISTINCT(dm.id), school_id, school_name
			FROM post_drawing_main AS dm
			LEFT JOIN schools s ON dm.school_id=s.id
			WHERE dm.date_created >= "'.$from.'" AND dm.date_created <= "'.$to.' 23:59:59"
				AND school_name IS NOT NULL
		) temp
		GROUP BY school_id
		ORDER BY num DESC');
	$total = 0;
	echo '<table width="300">';
	foreach( $d as $org ) {
		$total += $org['num'];
		echo '<tr><td>'.$org['num'].'</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.$total.'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";
	
	
}

?>