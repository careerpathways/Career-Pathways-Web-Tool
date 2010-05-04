<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

echo '<h2>Roadmaps Not Linked to OLMIS</h2>';
echo '<br />';

$schools = $DB->MultiQuery('
	SELECT id, school_name, COUNT(1) AS num
	FROM 
		(SELECT s.id, s.school_name, s.organization_type
		 FROM drawing_main m JOIN drawings d ON m.id=d.parent_id 
		 JOIN schools s ON m.school_id=s.id 
		 WHERE published = 1 
			AND m.id NOT IN (
			SELECT m.id FROM drawing_main m JOIN olmis_links o ON m.id = o.drawing_id)
		GROUP BY m.id ORDER BY s.school_name, m.id) tmp
	WHERE organization_type = "CC"
	GROUP BY id 
	ORDER BY school_name
');

echo '<table>';

foreach($schools as $s)
{
	echo '<tr>';
		echo '<td><div style="font-size: 18pt;">' . $s['school_name'] . '</div></td>';
		echo '<td><div style="font-size: 18pt;">' . $s['num'] . '</div></td>';
	echo '</tr>';

	$olmis = $DB->MultiQuery('
		SELECT s.school_name, m.id, IF(m.name="", p.title, m.name) AS name
		FROM drawing_main m 
		LEFT JOIN programs p ON m.program_id=p.id 
		JOIN drawings d ON m.id=d.parent_id 
		JOIN schools s ON m.school_id=s.id 
		WHERE published = 1 
			AND m.id NOT IN (
				SELECT m.id FROM drawing_main m JOIN olmis_links o ON m.id = o.drawing_id
			)
			AND s.id = ' . $s['id'] . '
		GROUP BY m.id 
		ORDER BY s.school_name, m.name');
	
	foreach( $olmis as $o ) 
	{
		echo '<tr>';
			echo '<td colspan="2"><a href="/a/drawings.php?action=drawing_info&id=' . $o['id'] . '">' . $o['name'] . '</a></td>';
		echo '</tr>';
	}
	
	echo '<tr><td colspan="2">&nbsp;</td></tr>';
}
	
echo '</table>';

PrintFooter();

?>