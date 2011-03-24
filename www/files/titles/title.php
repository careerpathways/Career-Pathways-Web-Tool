<?php
require_once "inc.php";
require_once "gd_color.inc.php";

$title_font = 'verdanab.ttf';
$cp_font = 'TCCB____.TTF';

$font_thick = 'EuropaGroNr2SB-Ult.ttf';
$font_thin = 'EuropaGroNr2SB-XLig.ttf';
$font_size = 13;

if( $i=strpos($_SERVER['REQUEST_URI'],'?') ) {
	// support for published drawings using old title format
	preg_match("|t=(.+)&|", substr($_SERVER['REQUEST_URI'],$i+1), $matches);
	$title = urldecode($matches[1]);
	$school = "";
} else  {
	$school = strtoupper(base64_decode(Request('school')));
	$title  = base64_decode(Request('title'));
}

if( $school == '-' ) $school = '';

$hash = md5($school.$title.Request('type'));
$filename = $SITE->cache_path("titles").$hash;

$width = 800;

header("Content-type: image/png");

/*
if(!function_exists('imagettftext')) {
	$im = imagecreatetruecolor($width, 19);
	$trans = imagecolorallocatealpha($im, 255, 255, 255, 127);
	$white = imagecolorallocate($im, 255, 255, 255);
	imagefill($im, 0,0, $trans);
	imagestring($im, 3, 10, 2, 'imagettftext function not found, probably your PHP does not have freetype support enabled.', $white);
	imagesavealpha($im, TRUE);
	imagepng($im);
	die();
}
*/


if( !file_exists($filename) ) {

	$dst = imagecreatetruecolor($width,19);

	$c_bkg = imagecolorallocatestr($dst, "295a76");
	$c_title = imagecolorallocatestr($dst, "FFFFFF");

	$c_cptitle1 = imagecolorallocatestr($dst, "ffffff");
	$c_cptitle2 = imagecolorallocatestr($dst, "ffffff");
	$c_cpbkg    = imagecolorallocatestr($dst, "333333");

	imagefilledrectangle($dst, 0,0, $width,imagesy($dst), $c_cpbkg);

	if(function_exists('imagettftext')) {
	
		$bbox = imagettftext($dst, $font_size, 0, 4,16, (Request('type')=='pathways'?$c_cptitle2:$c_cptitle1), $font_thick, $school);
		if( Request('type') == 'pathways' )
		{
			$bbox = imagettftext($dst, $font_size-1, 0, $bbox[2]+2,16, $c_cptitle1, $font_thin, l('drawing head pathways 1'));
			$bbox = imagettftext($dst, $font_size, 0, $bbox[2]+2,16, $c_cptitle2, $font_thick, l('drawing head pathways 2'));
		}
		else
		{
			$bbox = imagettftext($dst, $font_size-1, 0, $bbox[2]+2,16, $c_cptitle2, $font_thin, l('drawing head post 1'));
			$bbox = imagettftext($dst, $font_size, 0, $bbox[2]+3,16, $c_cptitle2, $font_thick, l('drawing head post 2'));
		}

		imagefilledrectangle($dst, $bbox[2]+5,0, $width,imagesy($dst), $c_bkg);
		imagettftext($dst, 12, 0, $bbox[2]+10,15, $c_title, $title_font, $title);
	} else {
		$white = imagecolorallocate($dst, 255,255,255);
	
		imagefilledrectangle($dst, 0,0, $width,imagesy($dst), $c_bkg);

		imagestring($dst, 3, 4,3, $school . ": " . $title, $white);
	}
	
	imagepng($dst, $filename);
	imagepng($dst);
} else {
	readfile($filename);
}

?>
