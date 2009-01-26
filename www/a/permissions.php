<?php
chdir("..");
include("inc.php");

ModuleInit('permissions');


PrintHeader();

$levels = $DB->MultiQuery('SELECT * FROM admin_user_levels ORDER BY level DESC');

echo '<table class="bordered">';
echo '<tr>';
	echo '<th width="20">ID</th>';
	echo '<th width="150">Name</th>';
	foreach( $levels as $l )
	{
		echo '<th width="50">' . $l['name'] . '</th>';
	}
echo '</tr>';
$modules = $DB->MultiQuery('
	SELECT *
	FROM admin_module M
	LEFT JOIN admin_level_module L ON M.id=L.module_id
	WHERE active=1
	ORDER BY `order`');
foreach( $modules as $m )
{
	echo '<tr>';
	echo '<td>' . $m['id'] . '</td>';
	echo '<td>' . $m['friendly_name'] . '</td>';
	foreach( $levels as $l )
	{
		echo '<td align="center">' . ($l['level'] >= $m['level'] ? 'X' : '&nbsp;'). '</td>';
	}
	echo '</tr>';
}
echo '</table>';

PrintFooter();

?>