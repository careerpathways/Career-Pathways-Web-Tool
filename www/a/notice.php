<?php
chdir("..");
include("inc.php");

$people = $DB->MultiQuery('
SELECT u.id, u.first_name, u.last_name, u.email
FROM post_drawings d
LEFT JOIN post_drawing_main m on m.id=d.parent_id
LEFT JOIN post_cell c ON d.id=c.drawing_id
LEFT JOIN users u ON u.id=d.created_by
WHERE c.id IS NULL
GROUP BY u.id
ORDER BY d.date_created
');

foreach( $people as $p )
{
	echo '<br /><hr /><br />';
	echo $p['first_name'] . ' ' . $p['last_name'] . ' - ' . $p['email'] . '<br />';

	$drawings = $DB->MultiQuery('
	SELECT m.name, m.id AS main_id, d.id, d.version_num AS `version`, d.date_created
	FROM post_drawings d
	LEFT JOIN post_drawing_main m on m.id=d.parent_id
	LEFT JOIN post_cell c ON d.id=c.drawing_id
	LEFT JOIN users u ON u.id=d.created_by
	WHERE c.id IS NULL AND d.created_by='.$p['id'].'
	GROUP BY d.id
	ORDER BY d.date_created	
	');
	echo '<table>';
	echo '<tr>';
		echo '<td>Drawing</td>';
		echo '<td>Version</td>';
		echo '<td>Date Created</td>';
		echo '<td>&nbsp;</td>';
	echo '</tr>';
	foreach( $drawings as $d ) 
	{
		echo '<tr>';
			echo '<td>' . $d['name'] . '</td>';
			echo '<td>Version ' . $d['version'] . '</td>';
			echo '<td>' . $d['date_created'] . '</td>';
			echo '<td><a href="http://oregon.ctepathways.org/a/post_drawings.php?action=drawing_info&id=' . $d['main_id'] . '">Drawing Properties</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}

?>