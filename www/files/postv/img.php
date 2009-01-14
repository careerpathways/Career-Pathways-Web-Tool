<?php
require_once "inc.php";
require_once "gd_color.inc.php";

$text = $_SERVER['REDIRECT_QUERY_STRING'];
$text = strtoupper(base64_decode($text));

$hash = md5($text);

$title_font = '/web/common/font/verdanab.ttf';
$font_size = 6;
$filename = $SITE->cache_path() . "post_vertical/".$hash;


header("Content-type: image/png");

if( !file_exists($filename) ) {
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
