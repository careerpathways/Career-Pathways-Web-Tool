<?php
include('stats.inc.php');

ModuleInit('stats');

$url = Request('url');

echo '<div style="background-color: white; padding: 5px;">';

echo '<h2>Links & Embeds</h2>';
echo '<h3>'.$url.'</h3>';

$links = $DB->MultiQuery('SELECT date, referer, COUNT(*) AS num_views
	FROM logs
	WHERE url="'.$url.'"
	AND status_code!=404
	AND referer != "-"
	AND referer NOT LIKE "%oregon.ctepathways.org%"
	AND referer NOT LIKE "%oregon.ctpathways.org%"
	GROUP BY referer');
echo '<table width="100%">';
foreach( $links as $i=>$k ) {
	echo '<tr class="'.($i%2==0?'row_light':'row_dark').'">';
		echo '<td>'.$DB->Date('Y-m-d',$k['date']).'</td>';
		echo '<td><a href="'.$k['referer'].'" title="'.$k['referer'].'" target="_new">'.(strlen($k['referer'])>80?substr($k['referer'],0,80).'...':$k['referer']).'</a></td>';
		echo '<td>'.$k['num_views'].'</td>';
	echo '</tr>';
}
echo '</table>';

echo '</div>';

?>