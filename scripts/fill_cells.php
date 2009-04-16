#!/usr/bin/php
<?php
set_include_path('.:../www/inc:../common');
chdir('../www');
include('inc.php');

if( !array_key_exists(1, $argv) )
	die('Usage: '.$argv[0]." drawing_id\n");

$version_id = $argv[1];
	
$rows = $DB->MultiQuery('SELECT * FROM post_row WHERE drawing_id='.$version_id);
$cols = $DB->MultiQuery('SELECT * FROM post_col WHERE drawing_id='.$version_id);

foreach( $rows as $r ) {
	foreach( $cols as $c ) {
		$check = $DB->MultiQuery('SELECT * FROM post_cell WHERE row_id='.$r['id'].' AND col_id='.$c['id']);
		if( count($check) == 0 ) {
			echo "Found missing cell for row:".$r['id']." col:".$c['id']."\n";
			$newcell = array();
			$newcell['drawing_id'] = $version_id;
			$newcell['row_id'] = $r['id'];
			$newcell['col_id'] = $c['id'];
			$DB->Insert('post_cell', $newcell);
		}	
	}
}
	
	
	
	
?>