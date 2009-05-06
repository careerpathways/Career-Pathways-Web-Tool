#!/usr/bin/php
<?php
set_include_path('.:/web/oregon.ctepathways.org/www/inc:/web/oregon.ctepathways.org/common');
chdir('../www');
include('inc.php');

if( !array_key_exists(1, $argv) )
	die('Usage: '.$argv[0]." version_id\n");

$drawings = $DB->MultiQuery('SELECT * FROM post_drawings');
foreach( $drawings as $dwg )
{
	$version_id = $dwg['id'];

	$version = $DB->SingleQuery('SELECT * FROM post_drawings WHERE id='.$version_id);
	$drawing = $DB->SingleQuery('SELECT * FROM post_drawing_main WHERE id='.$version['parent_id']);

	llog('Processing "'.$drawing['name'].'" version '.$version['version_num'].' of type '.$drawing['type']);
		
	$check = $DB->SingleQuery('SELECT COUNT(*) AS num FROM post_row WHERE drawing_id='.$version_id);
		
	if( $check['num'] == 0 )
	{
		// insert the row records
		if( $drawing['type'] == 'HS' )
		{
			for( $y=9; $y<=12; $y++ )
			{
				$row = array();
				$row['drawing_id'] = $version_id;
				$row['row_type'] = 'term';
				$row['row_year'] = $y;
				$row_id = $DB->Insert('post_row', $row);
				
				// find the cells that are for this row and assign them
				$cells = $DB->MultiQuery('SELECT * FROM post_cell WHERE drawing_id='.$version_id.' AND row_num='.($y-8));
				llog('Found '.count($cells).' cells for grade '.$y);
				foreach( $cells as $c )
				{
					$DB->Query('UPDATE post_cell SET row_id='.$row_id.' WHERE id='.$c['id']);				
				}
			}
			for( $i=0; $i<$version['num_extra_rows']; $i++ )
			{
				$row = array();
				$row['drawing_id'] = $version_id;
				$row['row_type'] = 'unlabeled';
				$row['row_year'] = ($i+1);
				$row_id = $DB->Insert('post_row', $row);
				
				// find the cells that are for this row and assign them
				$cells = $DB->MultiQuery('SELECT * FROM post_cell WHERE drawing_id='.$version_id.' AND row_num='.($i+100));
				llog('Found '.count($cells).' cells for extra row '.($i+1));
				foreach( $cells as $c )
				{
					$DB->Query('UPDATE post_cell SET row_id='.$row_id.' WHERE id='.$c['id']);				
				}
				
			}
		}
		else
		{
			for( $i=1; $i<=$version['num_rows']; $i++ )
			{
				$row = array();
				$row['drawing_id'] = $version_id;
				$row['row_type'] = 'term';
				$row['row_year'] = floor(($i-1) / 3) + 1;
				$row['row_term'] = (($i-1) % 3) + 1;
				$row_id = $DB->Insert('post_row', $row);
	
				// find the cells that are for this row and assign them
				$cells = $DB->MultiQuery('SELECT * FROM post_cell WHERE drawing_id='.$version_id.' AND row_num='.$i);
				llog('Found '.count($cells).' cells for term '.$i);
				foreach( $cells as $c )
				{
					$DB->Query('UPDATE post_cell SET row_id='.$row_id.' WHERE id='.$c['id']);				
				}
			}
			for( $i=0; $i<$version['num_extra_rows']; $i++ )
			{
				$row = array();
				$row['drawing_id'] = $version_id;
				$row['row_type'] = 'unlabeled';
				$row['row_year'] = ($i+1);
				$row_id = $DB->Insert('post_row', $row);
	
				// find the cells that are for this row and assign them
				$cells = $DB->MultiQuery('SELECT * FROM post_cell WHERE drawing_id='.$version_id.' AND row_num='.($i+100));
				llog('Found '.count($cells).' cells extra row '.($i+1));
				foreach( $cells as $c )
				{
					$DB->Query('UPDATE post_cell SET row_id='.$row_id.' WHERE id='.$c['id']);				
				}
			}
		}
	}
	else
	{
		llog("    This drawing has already been converted");
	}
}

function llog($msg)
{
	echo date('Y-m-d H:i:s').' '.$msg."\n";
}

?>
