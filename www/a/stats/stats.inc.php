<?php
include("inc.php");
include('GoogleCharts.php');

ModuleInit('stats');

$tables[] = array('main'=>'drawing_main', 'version'=>'drawings', 'caption'=>'Roadmaps', 'type'=>'CC');
$tables[] = array('main'=>'drawing_main', 'version'=>'drawings', 'caption'=>'Roadmaps', 'type'=>'Other');
$tables[] = array('main'=>'post_drawing_main', 'version'=>'post_drawings', 'caption'=>'CC POST Drawings', 'type'=>'CC');
$tables[] = array('main'=>'post_drawing_main', 'version'=>'post_drawings', 'caption'=>'HS POST Drawings', 'type'=>'HS');

$types = array(
	'pathways' => array('code'=>'pathways', 'title'=>'Pathways Roadmaps', 'main'=>'drawing_main'),
	'post' => array('code'=>'post', 'title'=>'POST Views', 'main'=>'vpost_views')
);

function PrintStatsMenu()
{
	?>
		<div style="float:right; width:220px; border: 1px #999 solid; font-size: 1.2em; line-height: 1.6em;">
			<ul style="margin:0; padding: 10px; list-style-type: none;">
				<li><a href="/a/stats/embedded_drawings.php">Embedded Drawings</a></li>
				<li><a href="/a/stats/user_stats.php">User Stats</a></li>
				<li><a href="/a/stats/drawing_counts.php">Drawing Counts</a></li>
				<li><a href="/a/stats/editing_history.php">Editing History</a></li>
				<li><a href="/a/stats/olmis_links.php">OLMIS Links</a></li>
				<li><a href="/a/stats/olmis_not_linked.php">OLMIS - Unlinked Roadmaps</a></li>
				<li><a href="/a/stats/broken_links.php">Broken Links</a></li>
				<li><a href="/a/stats/traffic_logs.php">Traffic Logs</a></li>
			</ul>
		</div>	
	<?php
}



function bar($index, &$arr, $key, $width=100) {
	$value = $arr[$index][$key];
	$value = ($value==''?0:$value);

	// calculate the maximum value of all the $arr[n][$key]
	$max = 0;
	foreach( $arr as $row ) {
		if( $row[$key] > $max ) $max = $row[$key];
	}

	if( $max == 0 ) {
		$percent = 0;
	} else {
		$percent = $value / $max;
	}

	$str = '<div class="percent_bar" style="width:'.$width.'px">';
	$str .= '<div class="percent_inside" style="width:'.floor($percent*$width).'px">'.$value.'</div>';
	$str .= '</div>';
	return $str;
}

function percent_bar($n, $d, $width=100) 
{
	$percent = $n / $d;

	$str = '<div class="percent_bar" style="width:'.$width.'px">';
	$str .= '<div class="percent_inside" style="width:'.floor($percent*$width).'px">'.$n.'</div>';
	$str .= '</div>';
	return $str;
}


?>