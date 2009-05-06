#!/usr/bin/php
<?php
set_include_path('.:../www/inc:../common');
chdir('../www');
include('inc.php');

if( !array_key_exists(1, $argv) )
	die('Usage: '.$argv[0]." drawing_id\n");

$drawing_id = $argv[1];

$drawing = $DB->SingleQuery('SELECT m.*, school_name FROM post_drawing_main AS m, schools s WHERE school_id=s.id AND m.id='.$drawing_id);
echo "Deleting '".$drawing['name']."' from '".$drawing['school_name']."'\n";

$versions = $DB->MultiQuery('SELECT * FROM post_drawings WHERE parent_id='.$drawing_id);
foreach( $versions as $v )
{
	$DB->Query('DELETE FROM post_row WHERE drawing_id='.$v['id']);
	echo "  ".$DB->AffectedRows() . " rows deleted\n";
	$DB->Query('DELETE FROM post_col WHERE drawing_id='.$v['id']);
	echo "  ".$DB->AffectedRows() . " cols deleted\n";
	$DB->Query('DELETE FROM post_cell WHERE drawing_id='.$v['id']);
	echo "  ".$DB->AffectedRows() . " cells deleted\n";
}
$DB->Query('DELETE FROM post_drawings WHERE parent_id='.$drawing_id);
echo "  ".$DB->AffectedRows() . " versions deleted\n";
$DB->Query('DELETE FROM post_drawing_main WHERE id='.$drawing_id);
echo "  ".$DB->AffectedRows() . " drawing deleted \n";

	
?>