<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

echo '<h2>Editing History</h2>';

echo '<div style="clear:both"></div>';

foreach( $tables as $t )
{
	echo '<h3>' . $t['caption'] . '</h3>';
	
	$drawings = $DB->MultiQuery('
	SELECT versions_created.date, new_versions, changed_versions, new_drawings, changed_drawings, drawings_published, current_published
	FROM
		(SELECT CONCAT(YEAR(v.date_created),"-",LPAD(MONTH(v.date_created),2,"0"),"-01") AS date, COUNT(*) AS new_versions
		FROM '.$t['version'].' v
		' . ($t['version'] == 'post_drawings' ? ' JOIN post_drawing_main m ON parent_id=m.id' : '') . '
		' . ($t['version'] == 'post_drawings' ? ' WHERE type="' . $t['type'] . '"' : '') . '
		GROUP BY YEAR(v.date_created), MONTH(v.date_created)) versions_created
	LEFT JOIN
		(SELECT CONCAT(YEAR(v.last_modified),"-",LPAD(MONTH(v.last_modified),2,"0"),"-01") AS date, COUNT(*) AS changed_versions
		FROM '.$t['version'].' v
		' . ($t['version'] == 'post_drawings' ? ' JOIN post_drawing_main m ON parent_id=m.id' : '') . '
		' . ($t['version'] == 'post_drawings' ? ' WHERE type="' . $t['type'] . '"' : '') . '
		GROUP BY YEAR(v.last_modified), MONTH(v.last_modified)) versions_modified
	ON versions_modified.date = versions_created.date
	LEFT JOIN
		(SELECT CONCAT(YEAR(date_created),"-",LPAD(MONTH(date_created),2,"0"),"-01") AS date, COUNT(*) AS new_drawings
		FROM '.$t['main'].'
		' . ($t['version'] == 'post_drawings' ? ' WHERE type="' . $t['type'] . '"' : '') . '
		GROUP BY YEAR(date_created), MONTH(date_created)) drawings_created
	ON versions_created.date = drawings_created.date
	LEFT JOIN
		(SELECT CONCAT(YEAR(last_modified),"-",LPAD(MONTH(last_modified),2,"0"),"-01") AS date, COUNT(*) AS changed_drawings
		FROM '.$t['main'].'
		' . ($t['version'] == 'post_drawings' ? ' WHERE type="' . $t['type'] . '"' : '') . '
		GROUP BY YEAR(last_modified), MONTH(last_modified)) drawings_modified
	ON versions_created.date = drawings_modified.date
	LEFT JOIN
		(SELECT CONCAT(YEAR(v.date_created),"-",LPAD(MONTH(v.date_created),2,"0"),"-01") AS date, COUNT(*) AS drawings_published
		FROM '.$t['version'].' v
		' . ($t['version'] == 'post_drawings' ? ' JOIN post_drawing_main m ON parent_id=m.id' : '') . '
		WHERE frozen=1
		' . ($t['version'] == 'post_drawings' ? ' AND type="' . $t['type'] . '"' : '') . '
		GROUP BY YEAR(v.date_created), MONTH(v.date_created)) drawings_published
	ON versions_created.date = drawings_published.date
	LEFT JOIN
		(SELECT CONCAT(YEAR(v.last_modified),"-",LPAD(MONTH(v.last_modified),2,"0"),"-01") AS date, COUNT(*) AS current_published
		FROM '.$t['version'].' v
		' . ($t['version'] == 'post_drawings' ? ' JOIN post_drawing_main m ON parent_id=m.id' : '') . '
		WHERE published=1
		' . ($t['version'] == 'post_drawings' ? ' AND type="' . $t['type'] . '"' : '') . '
		GROUP BY YEAR(v.last_modified), MONTH(v.last_modified)) current_published
	ON versions_created.date = current_published.date
	WHERE versions_created.date > "2007-09-01"
	ORDER BY versions_created.date');
	
	echo '<table>';
	echo '<tr>';
		echo '<th>Month</th>';
		echo '<th width="112">New Drawings</th>';
		echo '<th width="112">Changed Drawings</th>';
		echo '<th width="112">New Versions</th>';
		echo '<th width="112">Changed Versions</th>';
		echo '<th width="112">Drawings Published</th>';
		echo '<th width="112">Current Published</th>';
	echo '</tr>';
	foreach( $drawings as $i=>$d ) {
		echo '<tr>';
		echo '<td>'.$DB->Date("F Y",$d['date']).'</td>';
		echo '<td>'.bar($i,$drawings,'new_drawings').'</td>';
		echo '<td>'.bar($i,$drawings,'changed_drawings').'</td>';
		echo '<td>'.bar($i,$drawings,'new_versions').'</td>';
		echo '<td>'.bar($i,$drawings,'changed_versions').'</td>';
		echo '<td>'.bar($i,$drawings,'drawings_published').'</td>';
		echo '<td>'.bar($i,$drawings,'current_published').'</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	echo '<table>';
		echo '<tr>';
			echo '<th>New Drawings</th>';
			echo '<td>Number of drawings created during each month</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th>Changed Drawings</th>';
			echo '<td>Number of drawings modified</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th>New Versions</th>';
			echo '<td>Number of new versions of a drawing created</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th>Changed Versions</th>';
			echo '<td>Number of versions changed</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th>Drawings Published</th>';
			echo '<td>Number of drawings published during each month</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th>Current Published</th>';
			echo '<td>Of the currently published drawings, shows in which months those were published</td>';
		echo '</tr>';
	
	echo '</table>';
	
	echo '<br><br>';
}


	echo '<h3>POST Views</h3>';
	
	$drawings = $DB->MultiQuery('
	SELECT views_created.date, new_views, changed_views
	FROM
		(SELECT CONCAT(YEAR(v.date_created),"-",LPAD(MONTH(v.date_created),2,"0"),"-01") AS date, COUNT(*) AS new_views
		FROM vpost_views v
		GROUP BY YEAR(v.date_created), MONTH(v.date_created)) views_created
	LEFT JOIN
		(SELECT CONCAT(YEAR(v.last_modified),"-",LPAD(MONTH(v.last_modified),2,"0"),"-01") AS date, COUNT(*) AS changed_views
		FROM vpost_views v
		GROUP BY YEAR(v.last_modified), MONTH(v.last_modified)) views_modified
	ON views_modified.date = views_created.date
	ORDER BY views_created.date');
	
	echo '<table>';
	echo '<tr>';
		echo '<th>Month</th>';
		echo '<th width="112">New Views</th>';
		echo '<th width="112">Changed Views</th>';
	echo '</tr>';
	foreach( $drawings as $i=>$d ) {
		echo '<tr>';
		echo '<td>'.$DB->Date("F Y",$d['date']).'</td>';
		echo '<td>'.bar($i,$drawings,'new_views').'</td>';
		echo '<td>'.bar($i,$drawings,'changed_views').'</td>';
		echo '</tr>';
	}
	echo '</table>';
	
	echo '<table>';
		echo '<tr>';
			echo '<th>New Views</th>';
			echo '<td>Number of views created during each month</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th>Changed Views</th>';
			echo '<td>Number of views modified during each month</td>';
		echo '</tr>';
	echo '</table>';
	
	echo '<br><br>';

PrintFooter();

?>
