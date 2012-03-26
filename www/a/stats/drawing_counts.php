<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

echo '<h2>Drawing Counts</h2>';
echo '<br>';


echo '<table>';
foreach( $tables as $t )
{
	echo '<tr>';
		echo '<td colspan="2" valign="top">';
			echo '<h3>' . ($t['main'] == 'drawing_main' ? $t['type'] . ' ' : '') . $t['caption'] . '</h3>';
		echo '</td>';
	echo '</tr>';
	echo '<tr>';

	if($t['main'] == 'drawing_main')
	{
		$snapshot = $DB->SingleQuery('SELECT *
		FROM
			(SELECT COUNT(*) AS published FROM drawings d JOIN drawing_main m ON m.id=d.parent_id JOIN schools s ON m.school_id=s.id WHERE published=1 AND organization_type="' . $t['type'] . '") published_versions,
			(SELECT COUNT(DISTINCT(drawing_id)) AS embedded
				FROM external_links l
				LEFT JOIN drawing_main m ON m.id=l.drawing_id
				JOIN schools s ON m.school_id=s.id
				WHERE type="pathways" AND school_id IS NOT NULL AND organization_type = "' . $t['type'] . '") x,
			(SELECT COUNT(*) AS total_drawings FROM drawing_main JOIN schools s ON school_id=s.id WHERE organization_type = "' . $t['type'] . '") f,
			(SELECT COUNT(*) AS total_versions FROM drawings d JOIN drawing_main m ON d.parent_id=m.id JOIN schools s ON school_id=s.id WHERE organization_type = "' . $t['type'] . '") e,
			(SELECT COUNT(*) AS full_versions FROM
				(SELECT v.*, COUNT(o.id) AS num_objects
				FROM drawings AS v
				JOIN objects o ON o.drawing_id=v.id
				JOIN drawing_main m ON m.id=v.parent_id
				JOIN schools s on s.id=m.school_id
				WHERE organization_type="' . $t['type'] . '"
				GROUP BY v.id) c
				WHERE c.num_objects > 5) d');
		$versions = $DB->MultiQuery('SELECT IF(num_versions>7,8,num_versions) AS versions, COUNT(*) AS num_drawings FROM
			(SELECT m.*, COUNT(*) AS num_versions
			FROM drawing_main AS m
			JOIN drawings AS v ON v.parent_id=m.id
			JOIN schools s ON m.school_id=s.id
			WHERE organization_type="' . $t['type'] . '"
			GROUP BY m.id) g
		GROUP BY versions');
	}
	else
	{
		$snapshot = $DB->SingleQuery('SELECT *
		FROM
			(SELECT COUNT(*) AS published FROM post_drawings d JOIN post_drawing_main m ON d.parent_id=m.id JOIN schools s ON s.id = m.school_id WHERE published=1 AND s.organization_type="' . $t['type'] . '") published_versions,
			(SELECT COUNT(*) AS total_drawings FROM post_drawing_main WHERE type="' . $t['type'] . '") f,
			(SELECT COUNT(*) AS total_versions FROM post_drawings d JOIN post_drawing_main m ON d.parent_id=m.id WHERE type="' . $t['type'] . '") e');
		$versions = $DB->MultiQuery('SELECT IF(num_versions>7,8,num_versions) AS versions, COUNT(*) AS num_drawings FROM
			(SELECT m.*, COUNT(*) AS num_versions
			FROM post_drawing_main AS m, post_drawings AS v
			WHERE parent_id=m.id
				AND type="' . $t['type'] . '"
			GROUP BY m.id) g
		GROUP BY versions');
	}

	echo '<td valign="top">';
	
		echo '<table class="bordered">';
			if( $t['version'] == 'drawings' )
			{
				echo '<tr>';
					echo '<th>Total Embedded Drawings</th>';
					echo '<td>'.$snapshot['embedded'].'</td>';
				echo '</tr>';
			}
			echo '<tr>';
				echo '<th>Total Published Drawings</th>';
				echo '<td>'.$snapshot['published'].'</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<th>Total Drawings</th>';
				echo '<td>'.$snapshot['total_drawings'].'</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<th>Total Versions</th>';
				echo '<td>'.$snapshot['total_versions'].'</td>';
			echo '</tr>';
			if( $t['version'] == 'drawings' )
			{
				echo '<tr>';
					echo '<th>Versions with more than 5 objects</th>';
					echo '<td>'.$snapshot['full_versions'].'</td>';
				echo '</tr>';
			}
		echo '</table>';

	echo '</td>';
	echo '<td valign="top">';

		echo '<b>Number of drawings with x versions</b>';
		echo '<table>';
		foreach( $versions as $i=>$v ) {
			echo '<tr>';
				echo '<td width="70">'.($i==count($versions)-1?$v['versions'].' or more':$v['versions'].' version'.($v['versions']>1?'s':'')).'</td>';
				echo '<td>'.bar($i, $versions, 'num_drawings').'</td>';
			echo '</tr>';
		}
		echo '</table>';

	echo '</td>';
	echo '</tr>';
}

	echo '<tr>';
		echo '<td colspan="2" valign="top">';
			echo '<h3>POST Views</h3>';
		echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td valign="top">';

		$snapshot = $DB->SingleQuery('SELECT *
		FROM
			(SELECT COUNT(*) AS num_views FROM vpost_views) v,
			(SELECT COUNT(DISTINCT(drawing_id)) AS embedded FROM external_links WHERE type="post") x
		');
	
		echo '<table class="bordered">';
			echo '<tr>';
				echo '<th>Embedded POST Views</th>';
				echo '<td>'.$snapshot['embedded'].'</td>';
			echo '</tr>';
			echo '<tr>';
				echo '<th>Total POST Views</th>';
				echo '<td>'.$snapshot['num_views'].'</td>';
			echo '</tr>';
		echo '</table>';

	echo '</td>';
	echo '<td valign="top">';

		$versions = $DB->MultiQuery('SELECT IF(num_versions>7,8,num_versions) AS drawings, COUNT(*) AS num_views FROM
			(SELECT m.*, COUNT(*) AS num_versions
			FROM vpost_views AS m, vpost_links AS v
			WHERE vid=m.id
			GROUP BY m.id) g
		GROUP BY drawings');
		
		echo '<b>Number of views with x drawings</b>';
		echo '<table>';
		foreach( $versions as $i=>$v ) {
			echo '<tr>';
				echo '<td width="70">'.($i==count($versions)-1?$v['drawings'].' or more':$v['drawings'].' drawing'.($v['drawings']>1?'s':'')).'</td>';
				echo '<td>'.bar($i, $versions, 'num_views').'</td>';
			echo '</tr>';
		}
		echo '</table>';
			
	echo '</td>';
	echo '</tr>';


echo '</table>';

PrintFooter();

?>
