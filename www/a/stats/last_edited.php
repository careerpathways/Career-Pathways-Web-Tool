<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();


$schools = $DB->MultiQuery('
	SELECT id, school_name, COUNT(1) AS num
	FROM 
		(SELECT s.id, s.school_name, s.organization_type
		 FROM post_drawing_main m JOIN post_drawings d ON m.id=d.parent_id 
		 JOIN schools s ON m.school_id=s.id 
		 WHERE published = 1 
		 GROUP BY m.id ORDER BY s.school_name, m.id) tmp
	GROUP BY id 
	ORDER BY school_name
');

echo '<h2>Last Edited Drawings</h2>';
echo '<br />';

echo '<table>';

foreach($schools as $s)
{
	echo '<tr class="drawing_schoolname">';
		echo '<td class="drawinglist_schoolname" colspan="3">' . $s['school_name'] . ' (' . $s['num'] . ')</td>';
	echo '</tr>';
	
	$olmis = $DB->MultiQuery('
		SELECT s.school_name, m.id, IF(m.name="", p.title, m.name) AS name, m.last_modified
		FROM post_drawing_main m 
		LEFT JOIN programs p ON m.program_id=p.id 
		JOIN post_drawings d ON m.id=d.parent_id 
		JOIN schools s ON m.school_id=s.id 
		WHERE published = 1
			AND s.id = ' . $s['id'] . '
			AND s.organization_type = "cc"
		GROUP BY m.id 
		ORDER BY m.last_modified DESC');
	
	foreach( $olmis as $o ) 
	{
		echo '<tr class="drawing_main">';
			echo '<td><a href="/a/post_drawings.php?action=drawing_info&id=' . $o['id'] . '">' . SilkIcon('cog.png') . '</a></td>';
			echo '<td class="drawinglist_name">' . ($o['name'] ? $o['name'] : '[[deleted]]') . '</td>';
			echo '<td width="140">' . $o['last_modified'] . '</td>';
		echo '</tr>';
	}
	
	echo '<tr><td colspan="2">&nbsp;</td></tr>';
}
	
echo '</table>';

PrintFooter();


?>