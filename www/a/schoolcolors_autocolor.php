<?php
chdir("..");
include("inc.php");
require_once "colors.inc.php";

ModuleInit('schoolcolors');


if( KeyInRequest('blank') ) {
	echo '({"request_mode":"blank","colors":[]})';
	die();
}


if( Request('website') ) {
	$website = 'http://'.str_replace('http://','',Request('website'));

	$colors = array();

	// fetch the home page
	$homepage = file_get_contents($website);

	// parse the colors out of the home page
	$colors = array_merge($colors, parse_for_colors($homepage));

	// find all stylesheets linked to in the home page
	$stylesheets = array();
	preg_match_all('/href="(.+\.css)"/',$homepage,$stylesheets);
	// matches href="styles.css"

	$stylesheets1 = array();
	preg_match_all('/url\("*(.+\.css)"*\)/',$homepage,$stylesheets1);
	// matches url("styles.css") and url(styles.css)

	$stylesheets2 = array();
	preg_match_all('/@import "(.+\.css)"/',$homepage,$stylesheets2);
	// matches @import "styles.css"

	$frames = array();
	preg_match_all('/<frame[ a-zA-Z"=\.]*?src="([a-zA-Z0-9\-_%]+\..{3,4})"[ a-zA-Z0-9"=\.]*?>/i',$homepage,$frames);
	// matches <frame src="somepage.html" ...>

	$additional_pages = array_merge($stylesheets[1], $stylesheets1[1], $stylesheets2[1], $frames[1]);


	// parse the colors out of each stylesheet
	foreach( $additional_pages as $i=>$css ) {
		$url = $website.'/'.$css;
		$url = str_replace('//','/',$url);
		$url = str_replace('http:/','http://',$url);

		$cssfile = @file_get_contents($url);
		if( $cssfile != "" ) {
			$colors = array_merge($colors, parse_for_colors($cssfile));
		}
	}

	$colors = array_unique($colors);


	// remove greys and colors that are very light
	foreach( $colors as $key=>$color ) {
		$HSL = RGBtoHSL($color);
		if( $HSL['L'] > 0.70 || $HSL['S'] == 0 ) {
			unset($colors[$key]);
		}
	}


	printColors($colors, $website);

}







function printColors($colors, $website) {

	// this is just a rudimentary sorting method. could be improved in the future.
	// groups the colors into r/g/b and sorts each group by brightness

	$group = array('r'=>array(), 'g'=>array(), 'b'=>array());
	foreach( $colors as $c ) {
		$group[getdominantcolor($c)][] = array('rgb'=>$c, 'sort'=>getbrightness($c));
	}

	usort($group['r'], 'TheSort');
	usort($group['g'], 'TheSort');
	usort($group['b'], 'TheSort');

	$colors_ = array();
	foreach( $group['r'] as $c ) {
		$colors_[] = $c['rgb'];
	}
	foreach( $group['g'] as $c ) {
		$colors_[] = $c['rgb'];
	}
	foreach( $group['b'] as $c ) {
		$colors_[] = $c['rgb'];
	}

	if( count($colors_) > 0 ) {
		$json_colors = '"'.implode('","',$colors_).'"';
	} else {
		$json_colors = '';
	}
	echo '({"request_mode":"request","colors":['.$json_colors.']})';
}


function parse_for_colors($text) {
	$matches = array();
	preg_match_all("/#([0-9A-Fa-f]{6})/",$text,$matches);

	$colors = array();
	foreach( $matches[1] as $color ) {
		$color = strtolower($color);
		if( !in_array($color, array('ffffff','333333','000000')) ) {
			$colors[] = $color;
		}
	}

	return $colors;
}

function TheSort($a, $b) {
	return $a['sort'] > $b['sort'];
}


?>
