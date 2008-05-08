<?php
ini_set('display_errors','on');


$self = $_SERVER['REDIRECT_URL'];


$dom = new DOMDocument();
$dom->loadXML(file_get_contents($_REQUEST['xml']));
$X = new DOMXPath($dom);

$title = $X->query('/drawing/name/text()');
$schoolAbbr = $X->query('/drawing/schoolAbbr/text()');
$drawing_titlewithschool = $title->item(0)->nodeValue.' - '.$schoolAbbr->item(0)->nodeValue.' Career Pathways';
$drawing_title = $title->item(0)->nodeValue;

show_header();

if( array_key_exists('box',$_REQUEST) ) {

	// Display box content and links to/from

	$box_id = $_REQUEST['box'];

	$box = $X->query('/drawing/version/box[@id='.$box_id.']')->item(0);

	$node_title = $X->query('title', $box);

	echo '<h1>'.$schoolAbbr->item(0)->nodeValue.' '.$drawing_title.'</h1>';

	echo '<h2>'.$node_title->item(0)->nodeValue.'</h2>';

	$node_content = $X->query('content', $box);
	echo $node_content->item(0)->nodeValue.'<br>';


	$links_to = $X->query('connection/@destinationId', $box);
	if( $links_to->length > 0 ) {
		echo '<h3>Continue Down</h3>';
		echo '<ul>';
		for( $i=0; $i<$links_to->length; $i++ ) {
			$link_id = $links_to->item($i)->nodeValue;
			$link_title = $X->query('/drawing/version/box[@id='.$link_id.']/title/text()');
			echo '<li><a href="'.$self.'?box='.$link_id.'">'.$link_title->item(0)->nodeValue.'</a></li>';
		}
		echo '</ul>';
	}


	$links_from = $X->query('/drawing/version/box/connection[@destinationId='.$box_id.']/..');
	echo '<h3>Go Up</h3>';
	echo '<ul>';
	if( $links_from->length > 0 ) {
		for( $i=0; $i<$links_from->length; $i++ ) {
			$link_id = $X->query('@id',$links_from->item($i));
			$link_title = $X->query('title',$links_from->item($i));
			echo '<li><a href="'.$self.'?box='.$link_id->item(0)->nodeValue.'">'.$link_title->item(0)->nodeValue.'</a></li>';
		}
	} else {
		echo '<li><a href="'.$self.'">Home</a></li>';
	}
	echo '</ul>';

} else {

	// show links to all boxes that don't have incoming connections

	echo '<h1>'.$schoolAbbr->item(0)->nodeValue.' '.$drawing_title.'</h1>';

	$box_array = array();

	$boxes = $X->query('/drawing/version/box');
	for( $i=0; $i<$boxes->length; $i++ ) {
		$box_id = $X->query('@id',$boxes->item($i));
		$box_id = $box_id->item(0)->nodeValue;

		// find all boxes that link to this box. if none, add to box array
		$links = $X->query('/drawing/version/box/connection[@destinationId='.$box_id.']');
		if( $links->length == 0 ) {
			$box_array[] = $boxes->item($i);
		}
	}

	echo '<ul>';
	foreach( $box_array as $box ) {
		$link_id = $X->query('@id', $box);
		$link_title = $X->query('title', $box);
		echo '<li><a href="'.$self.'?box='.$link_id->item(0)->nodeValue.'">'.$link_title->item(0)->nodeValue.'</a></li>';
	}
	echo '</ul>';

	// display brief help text
	echo '<h3>Help</h3>';
	echo '<ul><li>Navigate through the chart by clicking links under the "Continue Down" and "Go Up" headers.</li></ul>';

}

show_footer();



function show_header() {
global $drawing_titlewithschool;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?= $drawing_titlewithschool ?></title>
	<style type="text/css">@import "/c/view/text.css";</style>
</head>
<body>
<?php
}

function show_footer() {
?>
</body>
</html>
<?php
}


?>