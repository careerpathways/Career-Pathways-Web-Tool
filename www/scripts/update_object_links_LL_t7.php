<?php

include('scriptinc.php');

echo '<br>Running...<br>';

// Pull records with the outdated link.
$objects = $DB->MultiQuery('SELECT * FROM objects WHERE content LIKE "%new.qualityinfo.org%";');

foreach($objects as $o)
{
	$content = unserialize($o['content']);
    print('<br>Updating - id: ' . $o['id'].' - drawing id: '.$o['drawing_id']);
    $oldUrl = 'new.qualityinfo.org';
    $newUrl = 'www.qualityinfo.org';
    $content['config']['content'] = str_replace($oldUrl, $newUrl, $content['config']['content']);
    $content['config']['content_html'] = str_replace($oldUrl, $newUrl, $content['config']['content_html']);
	$query = "UPDATE objects SET content = '" . mysql_real_escape_string(serialize($content)) . "' WHERE id = " . $o['id'];
    $DB->Query($query);
}

unset($objects);


$query = "UPDATE post_drawings
SET footer_text = REPLACE(footer_text, 'new.qualityinfo.org', 'www.qualityinfo.org')
WHERE footer_text LIKE '%new.qualityinfo.org%'";
$DB->Query($query);

$query = "UPDATE post_drawings
SET header_text = REPLACE(header_text, 'new.qualityinfo.org', 'www.qualityinfo.org')
WHERE header_text LIKE '%new.qualityinfo.org%'";
$DB->Query($query);

?>
