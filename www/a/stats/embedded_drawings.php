<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

echo '<h2>Embedded Drawings</h2>';
echo '<br />';
echo '<div style="font-style:italic;">Stats from embedded drawings using the new embed code</div>';



ob_start();
foreach($types as &$type)
{
	echo '<h3>' . $type['title'] . '</h3>';
	$schools = $DB->MultiQuery('
	SELECT school_id, school_name, COUNT(1) as num_embeds FROM (
		SELECT school_id, school_name
		FROM external_links l
		JOIN ' . $type['main'] . ' m ON m.id=l.drawing_id
		JOIN schools s ON school_id=s.id
		WHERE type="' . $type['code'] . '"
		GROUP BY drawing_id) tmp
	GROUP BY school_id
	ORDER BY COUNT(1) DESC
	');
	echo '<table width="100%">';
	echo '<tr>';
		echo '<th width="20"></th>';
		echo '<th width="55">Drawing</th>';
		echo '<th>Published URL</th>';
		echo '<th width="140">Last Seen</th>';
		echo '<th width="60">Hits</th>';
	echo '</tr>';
	$type['total_embeds'] = 0;
	foreach($schools as $s)
	{
		$type['total_embeds'] += $s['num_embeds'];
		
		echo '<tr class="drawing_schoolname">';
			echo '<td class="drawinglist_schoolname" colspan="3">' . $s['school_name'] . ' (' . $s['num_embeds'] . ')</td>';
			echo '<td></td>';
			echo '<td></td>';
		echo '</tr>';
		$trClass = new Cycler('row_light', 'row_dark');
		
		if($type['code'] == 'post')
		{
			$drawings = $DB->MultiQuery('
				SELECT drawing_id, m.name, SUM(counter) AS counter
				FROM external_links l
				JOIN ' . $type['main'] . ' m ON m.id=l.drawing_id
				JOIN schools s ON school_id=s.id
				WHERE type="' . $type['code'] . '" AND school_id = ' . $s['school_id'] . '
				GROUP BY drawing_id');
		}
		else
		{
			$drawings = $DB->MultiQuery('
				SELECT drawing_id, IF(m.name="", p.title, m.name) AS name, SUM(counter) AS counter
				FROM external_links l
				JOIN ' . $type['main'] . ' m ON m.id=l.drawing_id
				JOIN schools s ON school_id=s.id
				LEFT JOIN programs AS p ON m.program_id=p.id
				WHERE type="' . $type['code'] . '" AND school_id = ' . $s['school_id'] . '
				GROUP BY drawing_id');
		}

		foreach($drawings as $d)
			ShowDrawingStatsList($d, $type);
	}
	echo '</table>';
}
$alltables = ob_get_clean();


echo '<br />';

echo '<table class="bordered">';
	echo '<tr>';
		echo '<th>Pathways Roadmaps Embedded</th>';
		echo '<td>' . $types['pathways']['total_embeds'] . '</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th>POST Views Embedded</th>';
		echo '<td>' . $types['post']['total_embeds'] . '</td>';
	echo '</tr>';
echo '</table>';

echo '<div style="clear:both;"></div>';


echo $alltables;




function ShowDrawingStatsList(&$d, &$type)
{
	global $DB;
	
	if($type['code'] == 'pathways')
		$mainLink = '/a/drawings.php?action=drawing_info&id=' . $d['drawing_id'];
	else
		$mainLink = '/a/post_views.php?id=' . $d['drawing_id'];
	
	echo '<tr class="drawing_main">';
		echo '<td><a href="' . $mainLink . '">' . SilkIcon('cog.png') . '</a></td>';
		echo '<td class="drawinglist_name" colspan="2">' . ($d['name'] ? $d['name'] : '[[deleted]]') . '</td>';
		echo '<td></td>';
		echo '<td style="font-weight:bold;">' . $d['counter'] . '</td>';
	echo '</tr>';

	$links = $DB->MultiQuery('SELECT * FROM `external_links`
		WHERE type="' . $type['code'] . '" AND drawing_id = ' . $d['drawing_id'] . '
		ORDER BY `counter` DESC, `last_seen` DESC');
	$trClass = new Cycler('row_light', 'row_dark');
	foreach($links as $k)
	{
		$url = parse_url($k['url']);
		if($url == FALSE
				|| (!array_key_exists('scheme', $url) || !array_key_exists('host', $url))
				|| ($url['scheme'] != 'http' && $url['scheme'] != 'https'))
		{
			$linkText = 'unknown URL';
		}
		else
		{
			$url['host'] = preg_replace('/^www\./', '', $url['host']);
			
			$uri = $url['path'] . (array_key_exists('query', $url) ? '?' . $url['query'] : '');
			if(strlen($uri) > 50)
				$fileName = substr($uri, 0, 15) . '...' . substr($uri, -35);
			else
				$fileName = $uri;
			
			$linkText = $url['host'] . $fileName;
		}
		
		echo '<tr class="' . $trClass . '">';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td><a href="' . $k['url'] . '" title="' . $k['url'] . '" target="_blank">' . $linkText . '</a></td>';
			echo '<td>' . $k['last_seen'] . '</td>';
			echo '<td>' . $k['counter'] . '</td>';
		echo '</tr>';
	}
}



PrintFooter();

?>