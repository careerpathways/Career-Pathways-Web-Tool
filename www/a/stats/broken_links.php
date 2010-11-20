<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

echo '<h2>Broken Links</h2>';
echo '<div style="font-style:italic;">Shows external pages linking to non-existant drawings that are giving users "Not Found" errors.</div>';
echo '<div style="clear:both"></div>';


foreach(array('roadmap'=>'Roadmaps', 'postview'=>'Post Views', 'post'=>'Post Drawings') as $type=>$title)
{
	echo '<h3>' . $title . '</h3>';
	
	$hits = $DB->MultiQuery('SELECT * FROM external_link_errors WHERE type = "' . $type . '" ORDER BY date DESC');
	
	echo '<table width="100%">';
	echo '<tr>';
		echo '<th width="20"></th>';
		echo '<th width="70">Date</th>';
		echo '<th>Internal URL</th>';
		echo '<th width="360">External URL</th>';
		echo '<th width="60">Views</th>';
	echo '</tr>';
	foreach($hits as $i=>$h) 
	{
		echo '<tr class="'.($i%2==1?'row_light':'row_dark').'">';
			echo '<td>';
				if($h['version_id'])
				{
					if($h['type'] == 'roadmap')
						$url = '/a/drawings.php?action=version_info&version_id=' . $h['version_id'];
					else
						$url = '/a/post_drawings.php?action=version_info&version_id=' . $h['version_id'];
					echo '<a href="' . $url . '">' . SilkIcon('wrench.png') . '</a>';
				}
				elseif($h['drawing_id'])
				{
					if($h['type'] == 'roadmap')
						$url = '/a/drawings.php?action=drawing_info&id=' . $h['drawing_id'];
					elseif($h['type'] == 'post')
						$url = '/a/post_drawings.php?action=drawing_info&drawing_id=' . $h['drawing_id'];
					else
						$url = '/a/post_views.php?id=' . $h['drawing_id'];
					echo '<a href="' . $url . '">' . SilkIcon('cog.png') . '</a>';
				}
			echo '</td>';
			echo '<td><div title="' . $DB->Date('Y-m-d H:i:s', $h['date']) . '">' . $DB->Date('Y-m-d', $h['date']) . '</div></td>';
			echo '<td><a href="' . $h['request_uri'] . '" target="_new">' . $h['request_uri'] . '</a><br />';
				foreach(array('drawing_id', 'version_id', 'version') as $field)
					if($h[$field])
						echo ' ' . $field . ': ' . $h[$field];
			echo '</td>';
			echo '<td><a href="' . $h['external_url'] . '" target="_new">' . $h['external_url'] . '</a></td>';
			echo '<td>' . $h['counter'] . '</td>';
		echo '</tr>';
	}
	echo '</table>';

}

PrintFooter();

?>