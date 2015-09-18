<?php
include('scriptinc.php');

// Pull records with the outdated link.
// content LIKE "%osac.state.or.us/oda/oregon_map.pdf%"
$objects = $DB->MultiQuery('SELECT * FROM objects WHERE content LIKE "%osac.state.or.us/oda/oregon_map.pdf%";');
foreach($objects as $o)
{
    //'http://'.
	$content = unserialize($o['content']);
    $pdfURL = 'oregon.ctepathways.org/files/map-post-secondary-school-oregon.pdf';
    print('\n--------------------------\n'. $o['id'].' - '.$o['drawing_id'] . ': ' . $content['config']['content']);
    print('\n--------------------------\n'. $o['id'].' - '.$o['drawing_id'] . ': ' . $content['config']['content_html']);
    $content['config']['content'] = str_replace('www.osac.state.or.us/oda/oregon_map.pdf', $pdfURL, $content['config']['content']);
	$content['config']['content_html'] = str_replace('www.osac.state.or.us/oda/oregon_map.pdf', $pdfURL, $content['config']['content_html']);
    print('\n--------------------------\n'. $o['id'].' - '.$o['drawing_id']. ': ' .$content['config']['content']);
    print('\n--------------------------\n'. $o['id'].' - '.$o['drawing_id']. ': ' .$content['config']['content_html']);
    $query = "UPDATE objects SET content = '" . mysql_real_escape_string(serialize($content)) . "' WHERE id = " . $o['id'];
    $DB->Query($query);
/*
    //Debugging
    break;
    print("<br>*****************************************ORIGINAL************************************************<br>");
    var_dump($content['config']['content']);
    print("<br>*****************************************END ORIGINAL************************************************<br>");
    $content_replaced = str_replace('http://www.osac.state.or.us/oda/oregon_map.pdf', 'http://www.oregonstudentaid.gov/home.aspx', $content['config']['content']);
    print("<br>***************************************REPLACED**************************************************<br>");
    var_dump($content_replaced);
    print("<br>***************************************END REPLACED**************************************************<br>");
 */   
}

unset($objects);

?>
