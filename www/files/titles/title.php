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
$filename = $SITE->cache_path()."titles/".$hash;

if( !is_dir($SITE->cache_path() . "titles") ) mkdir($SITE->cache_path() . "titles", 0777);

$width = 800;

header("Content-type: image/png");

if( 1 || !file_exists($filename) ) {

	$dst = imagecreatetruecolor($width,19);

	$c_bkg = imagecolorallocatestr($dst, "295a76");
	$c_title = imagecolorallocatestr($dst, "FFFFFF");

	$c_cptitle1 = imagecolorallocatestr($dst, "ffffff");
	$c_cptitle2 = imagecolorallocatestr($dst, "ffffff");
	$c_cpbkg    = imagecolorallocatestr($dst, "333333");

	imagefilledrectangle($dst, 0,0, $width,imagesy($dst), $c_cpbkg);

	$bbox = imagettftext($dst, $font_size, 0, 4,16, (Request('type')=='pathways'?$c_cptitle2:$c_cptitle1), $font_thick, $school);
	if( Request('type') == 'pathways' )
	{
		$bbox = imagettftext($dst, $font_size-1, 0, $bbox[2]+2,16, $c_cptitle1, $font_thin, "CAREER");
		$bbox = imagettftext($dst, $font_size, 0, $bbox[2]+2,16, $c_cptitle2, $font_thick, "PATHWAYS");
	}
	else
	{
		$bbox = imagettftext($dst, $font_size-1, 0, $bbox[2]+2,16, $c_cptitle2, $font_thin, "PLAN OF");
		$bbox = imagettftext($dst, $font_size, 0, $bbox[2]+3,16, $c_cptitle2, $font_thick, "STUDY");
	}

	imagefilledrectangle($dst, $bbox[2]+5,0, $width,imagesy($dst), $c_bkg);
	imagettftext($dst, 12, 0, $bbox[2]+10,15, $c_title, $title_font, $title);

	imagepng($dst, $filename);
	imagepng($dst);
} else {
	readfile($filename);
}

?>
