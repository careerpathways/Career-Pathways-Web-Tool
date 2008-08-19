<?php
require_once "gd_color.inc.php";

$text = $_SERVER['REDIRECT_QUERY_STRING'];
$text = strtoupper($text);

$hash = md5($text);

$title_font = '/www/common/font/verdanab.ttf';
$font_size = 6;
$filename = "../../../cache/ccti_vertical/".$hash;


header("Content-type: image/png");

if( 1 || !file_exists($filename) ) {
	$bbox = imagettfbbox($font_size, 90, $title_font, $text);
	$dst = imagecreatetruecolor(abs($bbox[6])-2, abs($bbox[3]) + 8);

	$c_bkg = imagecolorallocatestr($dst, "FFFFFF");
	$c_title = imagecolorallocatestr($dst, "000000");

	imagefill($dst, 0,0, $c_bkg);

	imagettftext($dst, $font_size, 90, abs($bbox[6])-3,abs($bbox[3])+6, $c_title, $title_font, $text);

	imagepng($dst, $filename);
	imagepng($dst);
} else {
	readfile($filename);
}



?>