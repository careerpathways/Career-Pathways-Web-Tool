<?php
chdir("..");
include("inc.php");

ModuleInit('stats');

if( Request('from_date') ) {
	header("Content-type: text/plain");

	$from = date('y-m-d', strtotime(Request('from_date')));
	$to = date('y-m-d', strtotime(Request('to_date')));



	$d = $DB->MultiQuery('
		SELECT COUNT(*) AS num, school_name
		FROM (
			SELECT DISTINCT(user_id) AS user_id, school_id, school_name
			FROM login_history AS lh
			LEFT JOIN users u ON lh.user_id=u.id
			LEFT JOIN schools s ON u.school_id=s.id
			WHERE date >= "'.$from.'" AND date <= "'.$to.' 23:59:59"
				AND school_name IS NOT NULL
		) temp
		GROUP BY school_id
		ORDER BY num DESC');
	$total = 0;
	echo '<table width="300">';
	foreach( $d as $org ) {
		$total += $org['num'];
		echo '<tr><td>'.$org['num'].'</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.$total.'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";



	$d = $DB->MultiQuery('
		SELECT COUNT(*) AS num, school_name
		FROM (
			SELECT DISTINCT(u.id) AS user_id, school_id, school_name
			FROM users u
			LEFT JOIN schools s ON u.school_id=s.id
			WHERE u.date_created >= "'.$from.'" AND u.date_created <= "'.$to.' 23:59:59"
		) temp
		GROUP BY school_id
		ORDER BY num DESC');
	$total = 0;
	echo '<table width="300">';
	foreach( $d as $org ) {
		$total += $org['num'];
		echo '<tr><td>'.$org['num'].'</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.$total.'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";



	$d = $DB->MultiQuery('SELECT * FROM schools WHERE date_created>="'.$from.'" AND date_created<="'.$to.' 23:59:59"');
	echo '<table width="300">';
	foreach( $d as $org ) {
		echo '<tr><td>&nbsp;</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.count($d).'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";



	$d = $DB->MultiQuery('
		SELECT COUNT(*) AS num, school_name
		FROM (
			SELECT DISTINCT(dm.id), school_id, school_name
			FROM drawing_main AS dm
			LEFT JOIN schools s ON dm.school_id=s.id
			WHERE dm.date_created >= "'.$from.'" AND dm.date_created <= "'.$to.' 23:59:59"
				AND school_name IS NOT NULL
		) temp
		GROUP BY school_id
		ORDER BY num DESC');
	$total = 0;
	echo '<table width="300">';
	foreach( $d as $org ) {
		$total += $org['num'];
		echo '<tr><td>'.$org['num'].'</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.$total.'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";
	
	
	
	$d = $DB->MultiQuery('
		SELECT COUNT(*) AS num, school_name
		FROM (
			SELECT DISTINCT(dm.id), school_id, school_name
			FROM post_drawing_main AS dm
			LEFT JOIN schools s ON dm.school_id=s.id
			WHERE dm.date_created >= "'.$from.'" AND dm.date_created <= "'.$to.' 23:59:59"
				AND school_name IS NOT NULL
		) temp
		GROUP BY school_id
		ORDER BY num DESC');
	$total = 0;
	echo '<table width="300">';
	foreach( $d as $org ) {
		$total += $org['num'];
		echo '<tr><td>'.$org['num'].'</td><td>' .$org['school_name'].'</td></tr>';
	}
	echo '<tr><td width="30">'.$total.'</td><td><b>Total</b></td></tr>';
	echo '</table>';
	echo "\n";
	
	
	
	die();
}
	

PrintHeader();

?>
<script type="text/javascript" src="/files/greybox.js"></script>
<script type="text/javascript">
	function viewLinks(url) {
		chGreybox.create('',620,300);
		ajaxCallback(loadLinkContent, "stats/drawing_links.php?url="+url);
	}
	function loadLinkContent(content) {
		document.getElementById('greybox_content').innerHTML = content;
	}
	function date_clicked(obj) {
		if( obj.value == 'yyyy-mm-dd' ) obj.value = '';
		obj.style.color = '#000000';
	}
	function do_user_stats() {
		getLayer('total_active_users').innerHTML = 'loading...';
		getLayer('total_users_added').innerHTML = 'loading...';
		getLayer('total_orgs_added').innerHTML = 'loading...';
		getLayer('total_rdmp_added').innerHTML = 'loading...';
		getLayer('total_post_added').innerHTML = 'loading...';
		ajaxCallback(user_cb, 'stats.php?from_date='+getLayer('from_date').value+'&to_date='+getLayer('to_date').value);
	}
	function user_cb(data) {
		data = data.split("\n");
		getLayer('total_active_users').innerHTML = data[0];
		getLayer('total_users_added').innerHTML = data[1];
		getLayer('total_orgs_added').innerHTML = data[2];
		getLayer('total_rdmp_added').innerHTML = data[3];
		getLayer('total_post_added').innerHTML = data[4];
	}
</script>
<?php

echo '<h2>User Stats</h2>';
echo '<br>';

$total_users = $DB->SingleQuery('SELECT COUNT(*) AS num FROM users WHERE user_active=1');
$total_organizations = $DB->MultiQuery('SELECT COUNT(*) AS num, organization_type FROM schools GROUP BY organization_type');

$temp['HS'] = 'High Schools';
$temp['CC'] = 'Community Colleges';
$temp['Other'] = 'Other Organizations';

echo '<table class="bordered">';
	echo '<tr>';
		echo '<th>Total Users</th>';
		echo '<td>'.$total_users['num'].'</td>';
	echo '</tr>';
	foreach( $total_organizations as $to )
	{
		echo '<tr>';
			echo '<th>Total '.$temp[$to['organization_type']].'</th>';
			echo '<td>'.$to['num'].'</td>';
		echo '</tr>';
	}
echo '</table>';
echo '<br>';

?>
<table class="bordered">
	<tr>
		<td colspan="2">From: <input style="color: #999999" type="text" size="15" id="from_date" name="from_date" value="<?= Request('from_date')?Request('from_date'):'yyyy-mm-dd' ?>" onfocus="date_clicked(this)">
			To:<input style="color: #999999" type="text" size="15" id="to_date" name="to_date" value="<?= Request('to_date')?Request('to_date'):'yyyy-mm-dd' ?>" onfocus="date_clicked(this)">
			<input type="button" value="Search" onclick="do_user_stats()">
		</td>
	</tr>
	<tr>
		<th>Active Users</th>
		<td width="400"><div id="total_active_users"></div></td>
	</tr>
	<tr>
		<th>Users Added</th>
		<td><div id="total_users_added"></div></td>
	</tr>
	<tr>
		<th valign="top">Organizations Added</th>
		<td><div id="total_orgs_added"></div></td>
	</tr>
	<tr>
		<th valign="top">Roadmaps Added</th>
		<td><div id="total_rdmp_added"></div></td>
	</tr>
	<tr>
		<th valign="top">POST Drawings Added</th>
		<td><div id="total_post_added"></div></td>
	</tr>
</table>
<p class="tiny">Note: Statistics data available since Nov 1, 2008</p>
<br />
<?php
echo '<br>';


// SNAPSHOT
echo '<hr>';
echo '<h2>Drawing Counts</h2>';
echo '<br>';

$tables[] = array('main'=>'drawing_main', 'version'=>'drawings', 'caption'=>'Roadmaps');
$tables[] = array('main'=>'post_drawing_main', 'version'=>'post_drawings', 'caption'=>'POST Drawings');

echo '<table><tr>';
foreach( $tables as $t )
{
	echo '<td valign="top" style="padding: 10px">';
	echo '<h3>' . $t['caption'] . '</h3>';
	$snapshot = $DB->SingleQuery('SELECT *
		FROM
			(SELECT COUNT(*) AS published FROM '.$t['version'].' WHERE published=1) published_versions,
			(SELECT COUNT(*) AS total_drawings FROM '.$t['main'].') f,
			(SELECT COUNT(*) AS total_versions FROM '.$t['version'].') e,
			(SELECT COUNT(*) AS full_versions FROM
				(SELECT v.*, COUNT(objects.id) AS num_objects
				FROM '.$t['version'].' AS v, objects
				WHERE drawing_id=v.id
				GROUP BY v.id) c
			WHERE c.num_objects > 5) d');
	
	echo '<table class="bordered">';
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

	$versions = $DB->MultiQuery('SELECT IF(num_versions>7,8,num_versions) AS versions, COUNT(*) AS num_drawings FROM
			(SELECT m.*, COUNT(*) AS num_versions
			FROM '.$t['main'].' AS m, '.$t['version'].' AS v
			WHERE parent_id=m.id
			GROUP BY m.id) g
		GROUP BY versions');
	
	echo '<b>Number of drawings with x versions</b>';
	echo '<table>';
	foreach( $versions as $i=>$v ) {
		echo '<tr>';
			echo '<td width="70">'.($i==count($versions)-1?$v['versions'].' or more':$v['versions'].' version'.($v['versions']>1?'s':'')).'</td>';
			echo '<td>'.$v['num_drawings'].'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<br><br>';
	

	echo '</td>';
}
echo '</tr></table>';



// HISTORY
echo '<hr>';
echo '<h2>Editing History</h2>';

foreach( $tables as $t )
{
	echo '<h3>' . $t['caption'] . '</h3>';
	
	$drawings = $DB->MultiQuery('SELECT versions_created.date, new_versions, changed_versions, new_drawings, changed_drawings, drawings_published, current_published
	FROM
	(SELECT CONCAT(YEAR(date_created),"-",LPAD(MONTH(date_created),2,"0"),"-01") AS date, COUNT(*) AS new_versions
	FROM '.$t['version'].'
	GROUP BY YEAR(date_created), MONTH(date_created)) versions_created
	LEFT JOIN
		(SELECT CONCAT(YEAR(last_modified),"-",LPAD(MONTH(last_modified),2,"0"),"-01") AS date, COUNT(*) AS changed_versions
		FROM '.$t['version'].'
		GROUP BY YEAR(last_modified), MONTH(last_modified)) versions_modified
	ON versions_modified.date = versions_created.date
	LEFT JOIN
		(SELECT CONCAT(YEAR(date_created),"-",LPAD(MONTH(date_created),2,"0"),"-01") AS date, COUNT(*) AS new_drawings
		FROM '.$t['main'].'
		GROUP BY YEAR(date_created), MONTH(date_created)) drawings_created
	ON versions_created.date = drawings_created.date
	LEFT JOIN
		(SELECT CONCAT(YEAR(last_modified),"-",LPAD(MONTH(last_modified),2,"0"),"-01") AS date, COUNT(*) AS changed_drawings
		FROM '.$t['main'].'
		GROUP BY YEAR(last_modified), MONTH(last_modified)) drawings_modified
	ON versions_created.date = drawings_modified.date
	LEFT JOIN
		(SELECT CONCAT(YEAR(date_created),"-",LPAD(MONTH(date_created),2,"0"),"-01") AS date, COUNT(*) AS drawings_published
		FROM '.$t['version'].'
		WHERE frozen=1
		GROUP BY YEAR(date_created), MONTH(date_created)) drawings_published
	ON versions_created.date = drawings_published.date
	LEFT JOIN
		(SELECT CONCAT(YEAR(last_modified),"-",LPAD(MONTH(last_modified),2,"0"),"-01") AS date, COUNT(*) AS current_published
		FROM '.$t['version'].'
		WHERE published=1
		GROUP BY YEAR(last_modified), MONTH(last_modified)) current_published
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

/*
$log_dir = '/www/ctpathways.org/oregon/logs';

$files = explode("\n",shell_exec('ls -1 '.$log_dir.' | grep access'));
array_pop($files); // remove last filename (always empty)

foreach( $files as $file ) {

	$check = $DB->SingleQuery('SELECT COUNT(*) AS num FROM logs_processed WHERE filename="'.$file.'"');
	if( $check['num'] == 0 ) {

		$lines = file_get_contents($log_dir.'/'.$file);

		echo $file.'<br>';

		preg_match_all('~([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) - - \[([^\]]*)\] "GET (/c/(?:version|published|text)/([^\./ ]+)[^ ]*) HTTP/1\.[01]*" (\d+) (\d+) "([^"]*)" "([^"]*)"\n~',$lines,$matches);
		for( $i=0; $i<count($matches[0]); $i++ ) {
			$rec['remote_addr'] 		= $matches[1][$i];
			$rec['date'] 				= date("Y-m-d H:i:s",strtotime($matches[2][$i]));
			$rec['url'] 				= $matches[3][$i];
			$rec['drawing_code']		= $matches[4][$i];

			$lookup = $DB->SingleQuery('SELECT * FROM drawing_main WHERE code="'.$rec['drawing_code'].'"');
			$rec['drawing_id']			= $lookup['id'];

			$rec['status_code'] 		= $matches[5][$i];
			$rec['bytes_transferred'] 	= $matches[6][$i];
			$rec['referer'] 			= $matches[7][$i];
			$rec['user_agent'] 			= $matches[8][$i];

			$DB->Insert('logs',$rec);
		}

		$DB->Insert('logs_processed', array('filename'=>$file, 'date_processed'=>$DB->SQLDate()));
	}
}
*/



// TRAFFIC LOGS

echo '<hr>';
echo '<h2>Traffic Logs</h2>';


echo '<h3>Monthly Views</h3>';

echo '<table>';
	echo '<tr>';
		echo '<th>Total Views</th>';
		echo '<th>This Month</th>';
	echo '</tr>';
	echo '<tr>';
		echo '<td valign="top"><img src="stats/monthly.png"></td>';
		echo '<td valign="top"><img src="stats/this_month.png"></td>';
	echo '</tr>';
echo '</table>';



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





?>