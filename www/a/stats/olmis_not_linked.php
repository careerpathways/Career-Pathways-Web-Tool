<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

echo '<h2>Roadmaps Not Linked to OLMIS</h2>';
echo '<br />';

$total = $DB->SingleQuery('
	SELECT COUNT(DISTINCT tmp.id) AS num FROM
	(
			SELECT m.id  
			FROM drawing_main m 
			LEFT JOIN programs p ON m.program_id=p.id 
			JOIN drawings d ON m.id=d.parent_id 
			JOIN schools s ON m.school_id=s.id 
			WHERE published = 1 
				AND m.id NOT IN (
					SELECT m.id FROM drawing_main m JOIN olmis_links o ON m.id = o.drawing_id
				)
				AND s.organization_type = "CC"
			GROUP BY m.id 
			ORDER BY s.school_name, m.name
	) tmp
');

echo $total['num'] . ' drawings at community colleges do not link to OLMIS<br /><br />';


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
	echo '<tr class="drawing_schoolname">';
		echo '<td class="drawinglist_schoolname" colspan="2">' . $s['school_name'] . ' (' . $s['num'] . ')</td>';
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
		echo '<tr class="drawing_main">';
			echo '<td><a href="/a/drawings.php?action=drawing_info&id=' . $o['id'] . '">' . SilkIcon('cog.png') . '</a></td>';
			echo '<td class="drawinglist_name">' . ($o['name'] ? $o['name'] : '[[deleted]]') . '</td>';
		echo '</tr>';
	}
	
	echo '<tr><td colspan="2">&nbsp;</td></tr>';
}
	
echo '</table>';

PrintFooter();

?>