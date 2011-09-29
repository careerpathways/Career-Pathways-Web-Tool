<?php
include('stats.inc.php');

if( Request('from_date') ) {
	header("Content-type: text/plain");

	$from = date('y-m-d', strtotime(Request('from_date')));
	$to = date('y-m-d', strtotime(Request('to_date')));

	
	$response = array();

	
	
	
	

	$d = $DB->MultiQuery('
		SELECT org_type, school_name, COUNT(1) AS num FROM (
					SELECT DISTINCT(user_id) AS user_id, school_id, school_name, organization_type AS org_type
					FROM login_history AS lh
					LEFT JOIN users u ON lh.user_id=u.id
					LEFT JOIN schools s ON u.school_id=s.id
					WHERE date >= "'.$from.'" AND date <= "'.$to.' 23:59:59"
						AND school_name IS NOT NULL
					ORDER BY school_id) tmp
		GROUP BY org_type, school_name WITH ROLLUP
	');
	if(count($d) > 0) {
		$str = '<table width="300">';
		foreach( $d as $org ) 
		{
			$str .= '<tr>';
			$str .= '<td>' . ($org['school_name'] ? $org['school_name'] : '<b>' . $org['org_type'] . ' Total</b>') . '</td>';
			$str .= '<td width="30">' . ($org['school_name'] ? $org['num'] : '<b>' . $org['num'] . '</b>') . '</td>';
			$str .= '</tr>';
		}
		$str .= '</table>';
	} else {
		$str = 'None';
	}
	$response['active_users'] = $str;
	


	$d = $DB->MultiQuery('
		SELECT org_type, school_name, COUNT(*) AS num
		FROM (
			SELECT DISTINCT(u.id) AS user_id, school_id, school_name, organization_type AS org_type
			FROM users u
			JOIN schools s ON u.school_id=s.id
			WHERE u.date_created >= "'.$from.'" AND u.date_created <= "'.$to.' 23:59:59"
		) temp
		GROUP BY org_type, school_name WITH ROLLUP');
	if(count($d) > 0) {
		$str = '<table width="300">';
		foreach( $d as $org ) 
		{
			$str .= '<tr>';
			$str .= '<td>' . ($org['school_name'] ? $org['school_name'] : '<b>' . $org['org_type'] . ' Total</b>') . '</td>';
			$str .= '<td width="30">' . ($org['school_name'] ? $org['num'] : '<b>' . $org['num'] . '</b>') . '</td>';
			$str .= '</tr>';
		}
		$str .= '</table>';
	} else {
		$str = 'None';
	}
	$response['users_added'] = $str;



	$d = $DB->MultiQuery('
		SELECT organization_type, school_name, COUNT(1) AS num
		FROM schools 
		WHERE date_created>="'.$from.'" AND date_created<="'.$to.' 23:59:59"
		GROUP BY organization_type, school_name WITH ROLLUP
		');
	if(count($d) > 0) {
		$str = '<table width="300">';
		foreach( $d as $org ) 
		{
			$str .= '<tr>';
			$str .= '<td>' . ($org['school_name'] ? $org['school_name'] : '<b>' . $org['organization_type'] . ' Total</b>') . '</td>';
			$str .= '<td width="30">' . ($org['school_name'] ? '' : '<b>' . $org['num'] . '</b>') . '</td>';
			$str .= '</tr>';
		}
		$str .= '</table>';
	} else {
		$str = 'None';
	}
	$response['orgs_added'] = $str;



	$d = $DB->MultiQuery('
		SELECT org_type, school_name, COUNT(*) AS num
		FROM (
			SELECT DISTINCT(dm.id), school_id, school_name, organization_type AS org_type
			FROM drawing_main AS dm
			LEFT JOIN schools s ON dm.school_id=s.id
			WHERE dm.date_created >= "'.$from.'" AND dm.date_created <= "'.$to.' 23:59:59"
				AND school_name IS NOT NULL
		) temp
		GROUP BY org_type, school_name WITH ROLLUP');
	if(count($d) > 0) {
		$str = '<table width="300">';
		foreach( $d as $org ) 
		{
			$str .= '<tr>';
			$str .= '<td>' . ($org['school_name'] ? $org['school_name'] : '<b>' . $org['org_type'] . ' Total</b>') . '</td>';
			$str .= '<td width="30">' . ($org['school_name'] ? $org['num'] : '<b>' . $org['num'] . '</b>') . '</td>';
			$str .= '</tr>';
		}
		$str .= '</table>';
	} else {
		$str = 'None';
	}
	$response['rdmp_added'] = $str;
		
	
	
	$d = $DB->MultiQuery('
		SELECT org_type, school_name, COUNT(*) AS num
		FROM (
			SELECT DISTINCT(dm.id), school_id, school_name, organization_type AS org_type
			FROM post_drawing_main AS dm
			LEFT JOIN schools s ON dm.school_id=s.id
			WHERE dm.date_created >= "'.$from.'" AND dm.date_created <= "'.$to.' 23:59:59"
				AND school_name IS NOT NULL
		) temp
		GROUP BY org_type, school_name WITH ROLLUP');
	if(count($d) > 0) {
		$str = '<table width="300">';
		foreach( $d as $org ) 
		{
			$str .= '<tr>';
			$str .= '<td>' . ($org['school_name'] ? $org['school_name'] : '<b>' . $org['org_type'] . ' Total</b>') . '</td>';
			$str .= '<td width="30">' . ($org['school_name'] ? $org['num'] : '<b>' . $org['num'] . '</b>') . '</td>';
			$str .= '</tr>';
		}
		$str .= '</table>';
	} else {
		$str = 'None';
	}
	$response['post_added'] = $str;
	

	echo json_encode($response);
}

?>