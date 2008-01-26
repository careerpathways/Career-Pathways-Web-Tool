<?php
session_start();
error_reporting(0);

$width = 200;
$height = 50;

$im = imagecreatetruecolor($width, $height);
$bkg = imagecolorallocate($im, 255, 255, 255);
imagefill($im,0,0,$bkg);
$font_path = "BRLNSR.TTF";


$imsrc = imagecreatefromjpeg('verify-base.jpg');
imagecopy($im,$imsrc,0,0,rand(0,600-$width),rand(0,600-$height),$width,$height);

//$white = imagecolorallocatealpha($im, 255, 255, 255, 80);
//imagefilledrectangle($im, 0, 0, $width, $height, $white);

$imtext = imagecreatefromjpeg('verify-text.jpg');


if( array_key_exists('verify',$_SESSION) ) {

	// now draw text over the background

	$text = $_SESSION['verify'];
	$font_size = 30;

	$xpos = 0;
	$ypos = rand(25,38);

	$ci = array(0,1,2,3,4);
	shuffle($ci);

	$letters = array();

	for( $i=0; $i<strlen($text); $i++ ) {
		$color1 = imagecolorat($imtext, $ci[$i], 0);
		$angle = rand(-20,20);

		$xpos += rand(20,35);
		$ypos = rand(25,38);

		$let['x'] = $xpos;
		$let['y'] = $ypos;
		$let['angle'] = $angle;
		$let['color'] = $color1;
		$let['char'] = substr($text,$i,1);
		$letters[] = $let;
	}

	foreach( $letters as $let ) {
		imagettftext($im, $font_size, $let['angle'], $let['x'], $let['y'], $let['color'], $font_path, $let['char']);
	}



} else {
	$text = "untied.us";

	$text_color = imagecolorallocate($im, 255, 255, 255);
	$font_size = 24;

	imagettftext($im, $font_size, 0, 10, $height*2/3, $text_color, $font_path, $text);
}

header("Content-type: image/png");
imagepng($im);
imagedestroy($im);

?>