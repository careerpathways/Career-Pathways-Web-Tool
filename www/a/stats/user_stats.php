<?php
include('stats.inc.php');

PrintHeader();

PrintStatsMenu();

?>
<script type="text/javascript">
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
		ajaxCallback(user_cb, 'user_stats.ajax.php?from_date='+getLayer('from_date').value+'&to_date='+getLayer('to_date').value);
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
		<td colspan="2">From: <input style="color: #999999" type="text" size="15" id="from_date" name="from_date" value="<?= Request('from_date')?Request('from_date'):date('Y-m-01') ?>" onfocus="date_clicked(this)">
			To:<input style="color: #999999" type="text" size="15" id="to_date" name="to_date" value="<?= Request('to_date')?Request('to_date'):date('Y-m-t') ?>" onfocus="date_clicked(this)">
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

PrintFooter();

?>