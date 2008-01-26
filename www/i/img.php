<?php
include("gd_color.inc.php");

header("Content-type: image/png");

$cache_dir = '../../cache/components/';

$req = explode('/',$_SERVER['REDIRECT_QUERY_STRING']);

$valid_files = scandir('source');
$valid_files_ = array_flip($valid_files);
unset($valid_files_['.']);
unset($valid_files_['..']);
unset($valid_files_['.svn']);
$valid_files = array_flip($valid_files_);

$color = strtolower($req[0]);
$file  = $req[1];

// clean the request var
$color = preg_replace('/[^0123456789abcdef]*/','',$color);

if( strlen($color) == 6 && in_array($file, $valid_files) ) {

	if( !file_exists($cache_dir.$color) ) {
		mkdir($cache_dir.$color);
	}

	if( file_exists($cache_dir.$color.'/'.$file) ) {
		echo file_get_contents($cache_dir.$color.'/'.$file);
	} else {
		generateCTEimg($color, $file);
	}

} else {
	// since this script never called by a user, if the color is not 6 chars long, someone is doing something naughty
	$im = imagecreatetruecolor(1,1);
	imagepng($im);
}


function generateCTEimg($target_color, $f) {
global $cache_dir;

	$t_red = hexdec(substr($target_color,0,2));
	$t_green = hexdec(substr($target_color,2,2));
	$t_blue = hexdec(substr($target_color,4,2));

	$target = array(
		'red'   => $t_red,
		'green' => $t_green,
		'blue'  => $t_blue,
		'alpha' => 0,
		);
	$t = colornormalize($target);

	$src_filename = 'source/'.$f;
	$dst_filename = $cache_dir.$target_color.'/'.$f;

	$img = imagecreatefrompng($src_filename);
	$width = imagesx($img);
	$height = imagesy($img);

	$dst = imagecreatetruecolor($width,$height);
	imagealphablending($dst,false);
	imagesavealpha($dst, true);

	$targetcolors = array();  // cache for allocated colors

	for( $x=0; $x<$width; $x++ ) {
		for( $y=0; $y<$height; $y++ ) {

			$color = imagecolorat($img, $x, $y);
			$c = colornormalize(imagecolorsforindex($img, $color));

			$n = colorinvert(colormultiply(colorinvert($c,true), colorinvert($t)),true);

			// cache the allocated image colors
			$nstr = colorhexalpha($n);
			if( !array_key_exists($nstr, $targetcolors) ) {
				$targetcolors[$nstr] = imagecolorallocatealpha($dst,$n['red']*255,$n['green']*255,$n['blue']*255,$n['alpha']*127);
			}
			$ncol = $targetcolors[$nstr];

			//$ncol = imagecolorallocatealpha($dst,$n['red']*255,$n['green']*255,$n['blue']*255,$n['alpha']*127);

			imagesetpixel($dst, $x, $y, $ncol);

		}
	}

	imagepng($dst);  				// send to browser
	imagepng($dst, $dst_filename);	// save the file locally


	imagedestroy($img);
	imagedestroy($dst);

}


?>