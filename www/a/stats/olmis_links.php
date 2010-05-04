<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

echo '<h2>OLMIS Links</h2>';
echo '<br />';

$count = $DB->SingleQuery('
	SELECT COUNT(1) AS num FROM drawing_main m JOIN (SELECT drawing_id, olmis_id FROM olmis_links GROUP BY drawing_id) o ON drawing_id = m.id
');
echo $count['num'] . ' drawings link to OLMIS reports<br /><br />';

$olmis = $DB->MultiQuery('
	SELECT c.olmis_id, c.job_title
	FROM olmis_links AS l
	JOIN olmis_codes AS c ON l.olmis_id=c.olmis_id
	GROUP BY l.olmis_id
	ORDER BY job_title');

echo count($olmis) . ' OLMIS reports currently link to published roadmaps';

foreach( $olmis as $o ) {
	echo '<div class="olmis_title"><a href="http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ='.$o['olmis_id'].'&go=Continue#section11" target="_blank">' . $o['job_title'] . '</a></div>';
	$drawings = $DB->MultiQuery('
		SELECT IFNULL(p.title, d.name) AS name, d.id, schools.school_abbr
		FROM olmis_links AS l
		JOIN drawing_main AS d ON l.drawing_id=d.id
		LEFT JOIN programs AS p ON d.program_id=p.id
		JOIN schools ON d.school_id=schools.id
		WHERE l.olmis_id = '.$o['olmis_id']);
	foreach( $drawings as $d ) {
		echo '<div class="olmis_roadmap"><a href="/c/published/' . $d['id'] . '/view.html" target="_blank">' . $d['school_abbr'] . ': ' . $d['name'] . '</a></div>';
	}
}

PrintFooter();

?>