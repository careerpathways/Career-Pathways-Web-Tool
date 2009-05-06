#!/usr/bin/php
<?php
set_include_path('.:../www/inc:../common');
chdir('../www');
include('inc.php');

if( !array_key_exists(1, $argv) )
	die('Usage: '.$argv[0]." drawing_id\n");

$version_id = $argv[1];

if( $version_id == 'all' ) 
{
	$versions = $DB->MultiQuery('SELECT * FROM post_drawings');
	foreach($versions as $v) 
	{
		fill_version($v['id']);
	}
}
else
{
	fill_version($version_id);
}


function fill_version($version_id) {
	global $DB;

	$drawing = $DB->SingleQuery('SELECT m.*, school_name FROM post_drawing_main AS m, schools s WHERE school_id=s.id AND m.id='.$version_id);
	echo "Processing '".$drawing['name']."' from '".$drawing['school_name']."'\n";
	
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
}	
	
	
	
?>