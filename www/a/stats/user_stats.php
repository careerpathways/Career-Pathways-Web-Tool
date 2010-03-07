<?php
include('stats.inc.php');

$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';

PrintHeader();

PrintStatsMenu();

?>
<script type="text/javascript">
	$(document).ready(function(){
		$("#search_btn").click(function(){
			$(this).val("Loading...");
			$.post("user_stats.ajax.php", {
					from_date: $("#from_date").val(),
					to_date: $("#to_date").val() 
				},
				function(data){
					$("#total_active_users").html(data.active_users);
					$("#total_users_added").html(data.users_added);
					$("#total_orgs_added").html(data.orgs_added);
					$("#total_rdmp_added").html(data.rdmp_added);
					$("#total_post_added").html(data.post_added);
					$("#search_btn").val("Filter");
				}, "json");
		}).click();
	});
</script>
<?php
echo '<h2>User Stats</h2>';
echo '<br>';

$total_users = $DB->SingleQuery('SELECT COUNT(*) AS num FROM users WHERE user_active=1');
$total_organizations = $DB->VerticalQuery('SELECT COUNT(*) AS num, organization_type FROM schools GROUP BY organization_type', 'num', 'organization_type');
$total_active = $DB->ArrayQuery('
		SELECT organization_type, COUNT(1) AS numOrgs, SUM(num) AS numUsers
		FROM
		(
			SELECT school_name, COUNT(users.id) AS num, organization_type
			FROM schools
			LEFT JOIN users ON school_id = schools.id
			GROUP BY schools.id
		) tmp
		WHERE num > 0
		GROUP BY organization_type
		', 'organization_type');

$caption['HS'] = 'High Schools';
$caption['CC'] = 'Community Colleges';
$caption['Other'] = 'Other Organizations';

echo '<table class="bordered">';
	echo '<tr>';
		echo '<th>Total Users</th>';
		echo '<td>'.$total_users['num'].'</td>';
	echo '</tr>';
echo '</table>';
echo '<br />';

echo '<table class="bordered">';
	echo '<tr>';
		echo '<th></th>';
		echo '<th>Total Orgs</th>';
		echo '<th>Orgs with Users</th>';
		echo '<th>Num Users</th>';
	echo '</tr>';
	foreach($caption as $type=>$title)
	{
		echo '<tr>';
			echo '<th>'.$title.'</th>';
			echo '<td>'.$total_organizations[$type].'</td>';
			echo '<td>'.$total_active[$type]['numOrgs'].'</td>';
			echo '<td>'.$total_active[$type]['numUsers'].'</td>';
		echo '</tr>';
	}
echo '</table>';
echo '<br>';

?>
<table class="bordered">
	<tr>
		<td colspan="2">From: <input type="text" size="15" id="from_date" name="from_date" value="<?= Request('from_date')?Request('from_date'):date('Y-m-01') ?>">
			To:<input type="text" size="15" id="to_date" name="to_date" value="<?= Request('to_date')?Request('to_date'):date('Y-m-t') ?>">
			<input type="button" value="Filter" id="search_btn">
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