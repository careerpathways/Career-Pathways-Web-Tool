#!/usr/bin/php
<?php
include('scriptinc.php');

$olmis = $DB->MultiQuery('
	SELECT IF(name="",p.title,name) AS drawing_name,
		drawing_main.id AS drawing_id, drawings.id AS version_id,
		school_name,
		CONCAT("http://oregon.ctepathways.org/c/published/",parent_id,"/view.html") AS drawing_url,
		CONCAT("http://",school_website) AS school_website,
		olmis_id
	FROM drawings
	JOIN drawing_main ON drawing_main.id=parent_id 
	LEFT JOIN programs AS p ON drawing_main.program_id=p.id
	JOIN olmis_links ON olmis_links.drawing_id=drawing_main.id
	JOIN schools on school_id=schools.id 
	AND published = 1
	GROUP BY drawing_main.id, olmis_id
	ORDER BY school_name, drawing_main.id');

// Generate CSV file
$fp = fopen($SITE->cache_path('olmis').'olmis.csv', 'w');
foreach( $olmis as $o )
{
	fwrite($fp, $o['school_name'].','.$o['olmis_id'].','.$o['drawing_url'].',"'.trim($o['drawing_name']).'"'."\n");
}
fclose($fp);

?>
