<?php
include("../../inc.php");
require_once 'Image/Graph.php';
require_once 'Image/Canvas.php';


$view_data = $DB->MultiQuery('
	SELECT DATE(date) AS date, COUNT(*) AS num_views
	FROM logs
	WHERE status_code!=404
	AND referer != "-"
	AND referer NOT LIKE "%oregon.ctepathways.org%"
	AND referer NOT LIKE "%oregon.ctpathways.org%"
	AND date >= "'.date('Y-m-01').'"
	GROUP BY DATE(date)
	ORDER BY date');

$Graph =& Image_Graph::factory('graph', array(200, 150));

$Font =& $Graph->addNew('font', 'verdana');
$Font->setSize(7);
$Graph->setFont($Font);

$Graph->add(
	$Plotarea = Image_Graph::factory('plotarea')
);


$Dataset =& Image_Graph::factory('dataset');

foreach( $view_data as $i=>$vd ) {
	$Dataset->addPoint($DB->Date("j",$vd['date']), $vd['num_views']);
}

$Plot =& $Plotarea->addNew('line', array(&$Dataset));

$Plot->setLineColor('#cf9d2b');
$Plot->setFillColor('#e6d09e');

$AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
$AxisX->setFontSize(6);
$AxisX->setLabelOption('position', 'outside');
$AxisX->setLabelInterval('auto');

$Graph->done();

?>