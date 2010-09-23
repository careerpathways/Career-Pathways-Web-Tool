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

echo '<table>';
foreach( $olmis as $o )
{
	$a = '<a href="http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ='.$o['olmis_id'].'&go=Continue#section11" target="_blank">';
	echo '<tr class="drawing_main">';
		echo '<td>' . $a . '<img src="/images/olmis-16.gif" width="16" height="16" /></a></td>';
		echo '<td class="drawinglist_name" colspan="5">' . $o['job_title'] . '</td>';
	echo '</tr>';

	$trClass = new Cycler('row_light', 'row_dark');
	
	$drawings = $DB->MultiQuery('
		SELECT IFNULL(p.title, d.name) AS name, d.id, schools.school_abbr
		FROM olmis_links AS l
		JOIN drawing_main AS d ON l.drawing_id=d.id
		LEFT JOIN programs AS p ON d.program_id=p.id
		JOIN schools ON d.school_id=schools.id
		WHERE l.olmis_id = '.$o['olmis_id']);
	foreach( $drawings as $d ) 
	{
		if(($url = getExternalDrawingLink($d['id'], 'pathways')) == FALSE)
			$url = '/c/published/' . $d['id'] . '/view.html';
		
		$domain = parse_url($url, PHP_URL_HOST);
			
		echo '<tr class="' . $trClass . '">';
			echo '<td></td>';
			echo '<td width="25"></td>';
			echo '<td width="16"><a href="/a/drawings.php?action=drawing_info&id=' . $d['id'] . '">' . SilkIcon('cog.png') . '</a></td>';
			echo '<td width="16"><a href="' . $url . '" target="_blank">' . SilkIcon('link.png') . '</a></td>';
			echo '<td><a href="' . $url . '" target="_blank">' . $d['school_abbr'] . ': ' . $d['name'] . '</a></td>';
			echo '<td>' . $domain . '</td>';
		echo '</tr>';
	}
}
echo '</table>';

PrintFooter();

?>