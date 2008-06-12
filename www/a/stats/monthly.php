<?php
include("../../inc.php");
require_once 'Image/Graph.php';
require_once 'Image/Canvas.php';


// TODO: Can't figure out why there is so much whitespace at the bottom of the image

$view_data = $DB->MultiQuery('
	SELECT CONCAT(YEAR(date),"-",LPAD(MONTH(date),2,"0"),"-01") AS date, COUNT(*) AS num_views
	FROM logs
	WHERE status_code!=404
	AND referer != "-"
	AND referer NOT LIKE "%oregon.ctepathways.org%"
	AND referer NOT LIKE "%oregon.ctpathways.org%"
	GROUP BY YEAR(date), MONTH(date)
	ORDER BY date');


$Graph =& Image_Graph::factory('graph', array(200, 190));

$Font =& $Graph->addNew('font', 'verdana');
$Font->setSize(7);
$Graph->setFont($Font);

$Graph->add(
	$Plotarea = Image_Graph::factory('plotarea')
);


$Dataset =& Image_Graph::factory('dataset');

foreach( $view_data as $vd ) {
	$Dataset->addPoint($DB->Date("M Y",$vd['date']), $vd['num_views']);
}

$Plot =& $Plotarea->addNew('bar', array(&$Dataset));

$Plot->setLineColor('black');
$Plot->setFillColor('#e6d09e');

$AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
$AxisX->setFontAngle('vertical');
$AxisX->setFontSize(6);
$AxisX->setLabelOption('position', 'inside');
$AxisX->setLabelInterval('auto');

$Graph->done();

?>