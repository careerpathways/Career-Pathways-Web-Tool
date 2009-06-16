#!/usr/bin/php
<?php
include('scriptinc.php');

$drawings = $DB->MultiQuery('SELECT DISTINCT(parent_id) AS drawing_id, drawings.id AS version_id,
		school_name, name AS drawing_name,
		CONCAT("http://oregon.ctepathways.org/c/published/",parent_id,"/view.html") AS drawing_url,
		CONCAT("http://",school_website) AS school_website
	FROM objects 
	JOIN drawings ON drawings.id = drawing_id 
	JOIN drawing_main ON drawing_main.id=parent_id 
	JOIN schools on school_id=schools.id 
	WHERE content LIKE "%http://www.qualityinfo.org%"
	AND published = 1
	ORDER BY school_name');
$olmis = array();
$bycode = array();
foreach( $drawings as $d )
{
	$soc = array();
	$content = $DB->MultiQuery('SELECT content
		FROM objects
		WHERE drawing_id = ' . $d['version_id'] . '
		AND content LIKE "%http://www.qualityinfo.org%"');
	foreach( $content as $c )
		// http://www.qualityinfo.org/olmisj/OIC?areacode=4101000000&rpttype=full&action=report&occ=292011&go=Continue
		if( preg_match_all('|qualityinfo\.org/olmisj/OIC?.*?occ=([0-9]{6})|', $c['content'], $matches) )
			foreach( $matches[1] as $m )
				if( !in_array($m, $soc) )
				{
					$soc[] = $m;
					$bycode[$m][] = $d;
				}

	$d['soc'] = $soc;
	$olmis[] = $d;
}


// Generate CSV file
$fp = fopen($SITE->cache_path('olmis').'olmis.csv', 'w');
foreach( $olmis as $o )
	foreach( $o['soc'] as $soc )
		fwrite($fp, $o['school_name'].','.$soc.','.$o['drawing_url'].",".$o['drawing_name'].",\n");
fclose($fp);




// Generate XML file
ob_start();
echo '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
?>		
<cpolmisdata lastUpdated="<?=date('r')?>" lastUpdateTimestamp="<?=time()?>">
<?php
		foreach( $bycode as $soc=>$drawings ) {
?>	<soc val="<?=$soc?>">
<?php
			foreach( $drawings as $drawing ) {
?>		<roadmap>
			<roadmapurl><?=$drawing['drawing_url']?></roadmapurl>
			<embeddedurl></embeddedurl>
			<drawingname><?=htmlspecialchars($drawing['drawing_name'])?></drawingname>
			<schoolname><?=$drawing['school_name']?></schoolname>
			<schoolwebsite><?=$drawing['school_website']?></schoolwebsite>
		</roadmap>
<?php
			}
?>
	</soc>
<?php
		}
?>
</cpolmisdata>
<?php		
$xml = ob_get_clean();

$fp = fopen($SITE->cache_path('olmis').'olmis.xml', 'w');
fwrite($fp, $xml);
fclose($fp);


?>
