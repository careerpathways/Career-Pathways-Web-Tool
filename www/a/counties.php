<?php 
include("inc.php");
//Relies on this config: core/scripts/sql/updates/d20160224_update_counties_table.sql
$state = isset($_GET['state']) ? $_GET['state'] : '';

//echo GenerateSelectBoxDB('counties', 'school_county', 'id', 'county', 'county', $school['school_county'], array('state'=>$state));
if(isset($_GET['state'])) {
	$result = $DB->MultiQuery('SELECT * FROM `counties` WHERE `state` = "' . $state. '";');
} else {
	$result = $DB->MultiQuery('SELECT * FROM `counties`;');
}
?>

<select name="school_county" id="school_county">
<?php foreach($result as $r): ?>
	<option value="<?= $r['id'] ?>"><?= $r['county'] ?></option>
<?php endforeach; ?>
</select>
