<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

?>
<script type="text/javascript" src="/files/greybox.js"></script>
<script type="text/javascript">
	function viewLinks(url) {
		chGreybox.create('',620,300);
		ajaxCallback(loadLinkContent, "drawing_links.ajax.php?url="+url);
	}
	function loadLinkContent(content) {
		document.getElementById('greybox_content').innerHTML = content;
	}
</script>
<?php


echo '<h2>Traffic Logs</h2>';


echo '<div style="font-style:italic;">';
	echo 'This is an analysis of the raw web server logs. On this page you will see any URLs that link (or embed) drawings. This often includes people clicking on drawing links from within a webmail client, so this data cannot be used to accurately determine the URLs on which drawings are embedded. For this information, please see <a href="embedded_drawings.php">Embedded Drawings</a>. More detailed analytics are available on the <a href="http://google.com/analytics">Google Analytics</a> profile.';
echo '</div>';

		$view_data = $DB->MultiQuery('
			SELECT CONCAT(YEAR(date),"-",LPAD(MONTH(date),2,"0"),"-01") AS date, COUNT(*) AS num_views
			FROM logs
			WHERE status_code!=404
			AND referer != "-"
			AND referer NOT LIKE "%oregon.ctepathways.org%"
			AND referer NOT LIKE "%oregon.ctpathways.org%"
			GROUP BY YEAR(date), MONTH(date)
			ORDER BY date');
		$maxViews = 0;
		$labels = array();
		$history = array();
		foreach($view_data as $d)
		{
			if($d['num_views'] > $maxViews)
				$maxViews = $d['num_views'];
			$labels[] = date('M', strtotime($d['date']));
			$history[] = $d['num_views'];
		}

		$chart = new GoogleCharts('ls');
		$chart->chf = 'bg,s,FFFFFF';
					$chart->chdlp = 'b';
					$chart->chxt = 'x,y';
		$chart->chco = 'CF9D2B';
		$chart->chxr = '1,0,' . round($maxViews * 1.1);
		$chart->chds = '0,' . round($maxViews * 1.1);
		$chart->chxl = '0:|' . implode('|',$labels);
		$chart->chd = array($history);
		$chart->chg = '4,20,1,5';
		echo '<div style="width:700px;">';
			echo '<h3>All Logs</h3>';
			echo $chart->img(700, 190);
		echo '</div>';
		


		$view_data = $DB->MultiQuery('
			SELECT DATE(date) AS date, COUNT(*) AS num_views
			FROM logs
			WHERE status_code!=404
			AND referer != "-"
			AND referer NOT LIKE "%oregon.ctepathways.org%"
			AND referer NOT LIKE "%oregon.ctpathways.org%"
			AND date >= "'.date('Y-m-01').'"
			GROUP BY DATE(date)
			ORDER BY date');
		$maxViews = 0;
		$labels = array();
		$history = array();
		foreach($view_data as $d)
		{
			if($d['num_views'] > $maxViews)
				$maxViews = $d['num_views'];
			$labels[] = date('j', strtotime($d['date']));
			$history[] = $d['num_views'];
		}

		$chart = new GoogleCharts('ls');
		$chart->chf = 'bg,s,FFFFFF';
					$chart->chdlp = 'b';
					$chart->chxt = 'x,y';
		$chart->chco = 'CF9D2B';
		$chart->chxr = '1,0,' . round($maxViews * 1.1);
		$chart->chds = '0,' . round($maxViews * 1.1);
		$chart->chxl = '0:|' . implode('|',$labels);
		$chart->chd = array($history);
		$chart->chg = '4,20,1,5';
		echo '<div style="width:700px;">';
			echo '<h3>This Month</h3>';
			echo $chart->img(700, 190);
		echo '</div>';




echo '<h3>Roadmaps Embedded</h3>';
$maps = $DB->MultiQuery('
	SELECT DATE(date) AS date, url, drawing_id, dm.name,
		COUNT(*) AS num_views,
		GROUP_CONCAT(DISTINCT REPLACE(SUBSTRING(@url2 := REPLACE(REPLACE(referer,"http://",""),"https://",""),1,LOCATE("/",@url2)-1),"www.","") SEPARATOR ", ") AS domains
	FROM logs
	LEFT JOIN drawing_main dm ON dm.id=drawing_id
	WHERE status_code!=404
	AND referer != "-"
	AND referer NOT LIKE "%oregon.ctepathways.org%"
	AND referer NOT LIKE "%oregon.ctpathways.org%"
	GROUP BY url
	ORDER BY date DESC
	');
echo '<div class="log_scrollbox"'.(count($maps)>40?' style="height: 500px"':'').'>';
echo '<table width="100%">';
echo '<tr>';
	echo '<th width="70">Date</th>';
	echo '<th>Drawing</th>';
	echo '<th>Views</th>';
	echo '<th>Links or Embeds</th>';
echo '</tr>';
foreach( $maps as $i=>$m ) {
	echo '<tr class="'.($i%2==0?'row_light':'row_dark').'">';
		echo '<td>'.$DB->Date('Y-m-d',$m['date']).'</td>';
		echo '<td><a href="'.$m['url'].'" target="_new">'.$m['url'].'</a></td>';
		echo '<td>'.$m['num_views'].'</td>';
		echo '<td><a href="javascript:viewLinks(\''.urlencode($m['url']).'\')">'.$m['domains'].'</a></td>';
	echo '</tr>';
}
echo '</table>';
echo '</div>';
echo '<br><br>';





echo '<h3>Error Logs</h3>';
$error404 = $DB->MultiQuery('
	SELECT *, GROUP_CONCAT(remote_addr SEPARATOR ", ") AS ips, COUNT(*) AS num_views
	FROM logs
	WHERE status_code=404
	AND referer != "-"
	AND referer NOT LIKE "%oregon.ctepathways.org%"
	AND referer NOT LIKE "%oregon.ctpathways.org%"
	GROUP BY url, referer
	ORDER BY date DESC
	');
echo '<div class="log_scrollbox"'.(count($error404)>40?' style="height: 500px"':'').'>';
echo '<table width="100%">';
echo '<tr>';
	echo '<th width="100">Date</th>';
	echo '<th>IP Addresses</th>';
	echo '<th>Requested URL</th>';
	echo '<th>Views</th>';
	echo '<th>Link or Embed</th>';
echo '</tr>';
foreach( $error404 as $i=>$er ) {
	echo '<tr class="'.($i%2==0?'row_light':'row_dark').'">';
		echo '<td>'.$DB->Date('Y-m-d h:i',$er['date']).'</td>';
		echo '<td><div title="'.$er['ips'].'">'.substr($er['ips'],0,strpos($er['ips'],',')).'</div></td>';
		echo '<td><a href="'.$er['url'].'" target="_new">'.$er['url'].'</a></td>';
		echo '<td>'.$er['num_views'].'</td>';
		echo '<td><a href="'.$er['referer'].'" title="'.$er['referer'].'">'.substr($er['referer'],0,50).'</a></td>';
	echo '</tr>';
}
echo '</table>';
echo '</div>';




PrintFooter();

?>