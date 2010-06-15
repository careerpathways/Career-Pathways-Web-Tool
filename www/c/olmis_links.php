<?php
chdir("..");
include("inc.php");

$fmt = request('format');
if($fmt == 'php')
	$fmt = 'html';

if(request('occ'))
{
	$o = $DB->SingleQuery('
		SELECT c.olmis_id, c.job_title
		FROM olmis_links AS l
		JOIN olmis_codes AS c ON l.olmis_id=c.olmis_id
		WHERE c.olmis_id = ' . intval(request('occ')) . '
		GROUP BY l.olmis_id
		ORDER BY job_title');

	switch($fmt)
	{
		case 'html':
			echo html_header($o['job_title'] . ' - OLMIS Links');

			if($o)
				echo html_occupation($o);
			else
				echo 'Not found';

			echo html_footer();
			break;
		case 'json':
			header('Content-type: text/plain');
	
			if($o)
				echo json_encode(json_occupation($o));
			else
				echo json_encode(array('error'=>'Not found'));
				
			break;
	}
}
else
{
	$olmis = $DB->MultiQuery('
		SELECT c.olmis_id, c.job_title
		FROM olmis_links AS l
		JOIN olmis_codes AS c ON l.olmis_id=c.olmis_id
		GROUP BY l.olmis_id
		ORDER BY job_title');
	
	switch($fmt)
	{
		case 'html':
			echo html_header();
			echo count($olmis) . ' OLMIS reports currently link to published roadmaps' . "\n";
			
			foreach( $olmis as $o ) 
				echo html_occupation($o);

			echo html_footer();
		break;
		case 'json':
			header('Content-type: text/plain');
			
			$json = array();
			$json['results'] = count($olmis);
			$json['programs'] = array();
			foreach( $olmis as $o ) 
				$json['programs'][] = json_occupation($o);

			echo json_encode($json);
			
		break;
	}
}


function html_header($title = 'OLMIS Links')
{
	ob_start();
	?>
	<html>
	<head>
		<title><?=$title?></title>
		<style type="text/css">
			body {
				font-family: verdana, sans-serif;
				font-size: 10px;
			}
			.olmis_title {
				margin-top: 8px;
				font-size: 1.2em;
			}
			.olmis_roadmap {
				margin-left: 30px;
			}
		</style>
	</head>
	<body>
	<?php
	return ob_get_clean();
}

function html_footer()
{
	?>
	</body>
	</html>
	<?php
}

function html_occupation($o)
{
	global $DB;
	
	ob_start();
	echo '<div class="olmis_title"><a href="http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ='.$o['olmis_id'].'&go=Continue#section11" target="_blank">' . $o['job_title'] . '</a></div>' . "\n";
	$drawings = $DB->MultiQuery('
		SELECT IFNULL(p.title, d.name) AS name, d.id, schools.school_abbr, schools.school_name
		FROM olmis_links AS l
		JOIN drawing_main AS d ON l.drawing_id=d.id
		LEFT JOIN programs AS p ON d.program_id=p.id
		JOIN schools ON d.school_id=schools.id
		WHERE l.olmis_id = '.$o['olmis_id']);
	foreach( $drawings as $d ) {
		echo '<div class="olmis_roadmap"><a href="/c/published/' . $d['id'] . '/view.html" target="_blank"><span title="' . $d['school_name'] . '">' . $d['school_abbr'] . '</span>: ' . $d['name'] . '</a></div>' . "\n";
	}
	return ob_get_clean();
}

function json_occupation($o)
{
	global $DB;
	
	$program = array(
		'title'=>$o['job_title'], 
		'url'=>'http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ='.$o['olmis_id'].'&go=Continue#section11'
	);
	$drawings = $DB->MultiQuery('
		SELECT IFNULL(p.title, d.name) AS name, d.id, schools.school_abbr, schools.school_name
		FROM olmis_links AS l
		JOIN drawing_main AS d ON l.drawing_id=d.id
		LEFT JOIN programs AS p ON d.program_id=p.id
		JOIN schools ON d.school_id=schools.id
		WHERE l.olmis_id = '.$o['olmis_id']);
	foreach( $drawings as $d ) 
		$program['roadmaps'][] = array('url'=>'http://' . $_SERVER['SERVER_NAME'] . '/c/published/' . $d['id'] . '/view.html', 'school_abbr'=>$d['school_abbr'], 'school_name'=>$d['school_name'], 'title'=>$d['name']);

	return $program;
}

?>