#!/usr/bin/php
<?php
include('scriptinc.php');

$drawings = $DB->MultiQuery('SELECT d.* 
	FROM post_drawings d
	JOIN post_drawing_main m ON d.parent_id = m.id
	WHERE m.type = "CC"
');

foreach($drawings as $d)
{
	echo $d['id'] . "\n";
	
	$rows = $DB->MultiQuery('
		SELECT *, row_term * 1 AS row_term_num
		FROM post_row WHERE drawing_id = ' . $d['id'] . '
		ORDER BY row_year, row_term
	');
	
	$qtr = 1;
	foreach($rows as $r)
	{
		if($r['row_type'] == 'term')
		{
			$query = $DB->Query('UPDATE post_row SET row_qtr = ' . $qtr . ' WHERE id = ' . $r['id']);
			$qtr++;
		}
	}
	
}



?>