<?php
require_once "gd_color.inc.php";
//require_once "gd_vwfont.inc.php";

$title_font = 'verdanab.ttf';
$cp_font = 'TCCB____.TTF';

if( $i=strpos($_SERVER['REQUEST_URI'],'?') ) {
	// support for published drawings using old title format
	preg_match("|t=(.+)&|", substr($_SERVER['REQUEST_URI'],$i+1), $matches);
	$title = urldecode($matches[1]);
	$school = "";
} else  {
	preg_match("|/files/titles/(.+)/(.+).png|", $_SERVER['REQUEST_URI'], $matches);
	$school = strtoupper(base64_decode($matches[1]));
	$title  = base64_decode($matches[2]);
}

$hash = md5($school.$title);
$filename = "../../../cache/titles/".$hash;

$width = 800;

header("Content-type: image/png");

if( !file_exists($filename) ) {

	//$dst = imagecreatefrompng('base.png');
	$dst = imagecreatetruecolor($width,17);

	$c_bkg = imagecolorallocatestr($dst, "649FC2");
	$c_title = imagecolorallocatestr($dst, "FFFFFF");

	$c_cptitle1 = imagecolorallocatestr($dst, "cf9d2b");
	$c_cptitle2 = imagecolorallocatestr($dst, "295a76");
	$c_cpbkg    = imagecolorallocatestr($dst, "cccccc");

	imagefilledrectangle($dst, 0,0, $width,17, $c_cpbkg);

	//$font = imageloadfontvw("verdana_b_10pt.png");
	//imagevwstring($dst, $font, 130,1, $subtitle, $c_title);

	$bbox = imagettftext($dst, 15, 0, 4,15, $c_cptitle2, $cp_font, $school);
	$bbox = imagettftext($dst, 15, 0, $bbox[2]+2,15, $c_cptitle1, $cp_font, "CAREER");
	$bbox = imagettftext($dst, 15, 0, $bbox[2]+2,15, $c_cptitle2, $cp_font, "PATHWAYS");

	imagefilledrectangle($dst, $bbox[2]+5,0, $width,17, $c_bkg);
	imagettftext($dst, 12, 0, $bbox[2]+10,14, $c_title, $title_font, $title);

	imagepng($dst, $filename);
	imagepng($dst);
} else {
	readfile($filename);
}

?>
