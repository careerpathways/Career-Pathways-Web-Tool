<?php

function colornormalize($colorarray) {
	$result = array();
	foreach( array('red','green','blue') as $key ) {
		$result[$key] = ($colorarray[$key]/255);
	}
	$result['alpha'] = $colorarray['alpha']/127;
	return $result;
}

function colormultiply($ca1, $ca2) {
	// this is like photoshop's blending mode, "multiply"
	// $c * black = black
	// $c * white = $c

	$result = array();
	foreach( $ca1 as $key=>$ca1v ) {
		$ca2v = $ca2[$key];
		$result[$key] = ($ca1v * $ca2v);
	}
	return $result;
}

function colorinvert($color,$preserve_alpha=false) {
	$result = array();
	foreach( $color as $key=>$val ) {
		$result[$key] = 1 - $val;
	}
	if( $preserve_alpha ) {
		$result['alpha'] = 1-$result['alpha'];
	}
	return $result;
}

function colorscreen($c1, $c2) {
	// this is like photoshop's blending mode, "screen"
	// $c / black = $c
	// $c / white = white
	return colorinvert(colormultiply(colorinvert($c1), colorinvert($c2)));
}

function colorhexalpha($color) {
	// returns an html-like string (RGBA) e.g. FF0000FF
	$hex = sprintf("%02x",($color['red']*255));
	$hex .= sprintf("%02x",($color['green']*255));
	$hex .= sprintf("%02x",($color['blue']*255));
	$hex .= sprintf("%02x",($color['alpha']*255));
	return $hex;
}

function colorhex($color) {
	// returns an html string (RGB) e.g. FF0000
	$hex = sprintf("%02x",($color['red']*255));
	$hex .= sprintf("%02x",($color['green']*255));
	$hex .= sprintf("%02x",($color['blue']*255));
	return $hex;
}

function imagecolorallocatestr(&$img, $str) {
	// RRGGBB or RRGGBBAA format

	if( strlen($str) == 6 ) {
		$red = hexdec(substr($str,0,2));
		$green = hexdec(substr($str,2,2));
		$blue = hexdec(substr($str,4,2));
		return imagecolorallocate($img, $red, $green, $blue);
	} else {
		$red = hexdec(substr($str,0,2));
		$green = hexdec(substr($str,2,2));
		$blue = hexdec(substr($str,4,2));
		$alpha = hexdec(substr($str,6,2));
		return imagecolorallocatealpha($img, $red, $green, $blue, $alpha);
	}
}

function hextocolorarray($str) {
	// returns an array with the color given in $str. values are between 0 and 1

	$red = hexdec(substr($str,0,2));
	$green = hexdec(substr($str,2,2));
	$blue = hexdec(substr($str,4,2));
	if( strlen($str) == 6 ) {
		$alpha = 0;
	} else {
		$alpha = hexdec(substr($str,6,2));
	}

	$target = array(
		'red'   => $red,
		'green' => $green,
		'blue'  => $blue,
		'alpha' => 0,
		);
	return colornormalize($target);
}


?>