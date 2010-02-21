<?php
include('scriptinc.php');

// Get the object color from the serialized config array and put it in the table.
// This is for counting how many drawings have objects using each color.
$objects = $DB->MultiQuery('SELECT * FROM objects ORDER BY id DESC');
foreach($objects as $o)
{
	$content = unserialize($o['content']);

	if(array_key_exists('color', $content['config']) && $content['config']['color'] != '333333')
	{
		//echo $o['id'] . ': ' . $content['config']['color'] . "\n";
		$DB->Query('UPDATE objects SET color = "' . strtolower($content['config']['color']) . '" WHERE id = ' . $o['id']);
	}
}
unset($objects); // it's a large variable

// Count the number of drawings using each color
$colors = $DB->MultiQuery('SELECT * FROM color_schemes');
foreach($colors as $color)
{
	$roadmaps = $DB->MultiQuery('SELECT id, SUM(num)
		FROM
		(
		SELECT d.id, COUNT(1) AS num 
		FROM objects o
		JOIN drawings d ON o.drawing_id=d.id
		JOIN drawing_main m ON d.parent_id = m.id
		WHERE school_id = ' . $color['school_id'] . '
			AND color = "' . $color['hex'] . '"
		GROUP BY d.id
			UNION
		SELECT d.id, COUNT(1) AS num
		FROM connections c
		JOIN objects o ON c.source_object_id = o.id
		JOIN drawings d ON o.drawing_id=d.id
		JOIN drawing_main m ON d.parent_id = m.id
		WHERE school_id = ' . $color['school_id'] . '
			AND c.color = "' . $color['hex'] . '"
		GROUP BY d.id
		) AS tmp
		GROUP BY id');
	echo '#' . $color['hex'] . ' => ' . count($roadmaps) . "\n";
	$DB->Query('UPDATE color_schemes SET num_roadmaps = ' . count($roadmaps) . ' WHERE id = ' . $color['id']);
}

?>