<?php
session_start();
error_reporting(0);

$width = 200;
$height = 50;

$im = imagecreatetruecolor($width, $height);
$bkg = imagecolorallocate($im, 255, 255, 255);
imagefill($im,0,0,$bkg);
$font_path = "/www/common/font/ROCK.TTF";


$index = $_REQUEST['i'];

if( array_key_exists($index,$_SESSION) && array_key_exists('verify',$_SESSION[$index]) ) {

	if( array_key_exists('font',$_SESSION[$index]) ) {
		$font_path = $_SESSION[$index]['font'];
	}

	// first put down background dots
	// allocate 4 random colors
	for( $i=0; $i<4; $i++ ) {
		$colors[] = imagecolorallocate($im, rand(192,255), rand(127,255), rand(127,255));
	}
	// randomly place dots in the image
	for( $i=0; $i<($width*$height*2/3); $i++ ) {
		$x = rand(0,$width);
		$y = rand(0,$height);
		imagesetpixel($im,$x,$y,$colors[rand(0,count($colors)-1)]);
		imagesetpixel($im,$x+1,$y,$colors[rand(0,count($colors)-1)]);
		imagesetpixel($im,$x+1,$y+1,$colors[rand(0,count($colors)-1)]);
	}


	// now draw text over the background

	$text = $_SESSION[$index]['verify'];
	$font_size = 20;

	$color1 = imagecolorallocate($im, rand(0,100), rand(0,100), rand(0,100));
	$color2 = imagecolorallocate($im, rand(92,160), rand(92,192), rand(128,192));
	$color3 = imagecolorallocate($im, rand(128,255), rand(192,255), rand(192,255));

	$xpos = rand(20,28);
	$ypos = rand(20,28);
	imagettftext($im, $font_size, 0, $xpos, $ypos, $color1, $font_path, $text);

	$xpos = rand(33,40);
	$ypos = rand(33,40);
	imagettftext($im, $font_size, 0, $xpos, $ypos, $color2, $font_path, $text);
	imagettftext($im, $font_size, 0, $xpos-3, $ypos-3, $color3, $font_path, $text);

} else {
	$text = "NO DATA";

	$text_color = imagecolorallocate($im, 255, 255, 255);
	$font_size = 20;

	imagettftext($im, $font_size, 0, 10, $height*2/3, $text_color, $font_path, $text);
}

header("Content-type: image/png");
imagepng($im);
imagedestroy($im);

?>