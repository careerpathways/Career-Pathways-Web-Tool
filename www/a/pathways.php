<?php
chdir("..");
include("inc.php");


PrintHeader();

?>
<style type="text/css">
td {
	font-size: 9px;
}
</style>
<?php


$target = array(
	'red'   => 255,
	'green' => 0,
	'blue'  => 0,
	'alpha' => 0,
	);
$t = colornormalize($target);


$img = imagecreatefrompng("i/source/tbox_se.png");
$width = imagesx($img);
$height = imagesy($img);

$dst = imagecreatetruecolor($width,$height);
imagealphablending($dst,false);
imagesavealpha($dst, true);

$inv = imagecreatetruecolor($width,$height);
imagealphablending($inv,false);
imagesavealpha($inv, true);

$mult = imagecreatetruecolor($width,$height);
imagealphablending($mult,false);
imagesavealpha($mult, true);


echo '<table>';


echo '<tr>';

	echo '<td>a: Source</td>';
	echo '<td>b=~a: Source Inverse <br>(preserve alpha)</td>';
	echo '<td>c: Target Color Inverse</td>';
	echo '<td>d=b*c: Multiplication</td>';
	echo '<td>Result=~d: (invert, preserve alpha)</td>';



echo '<tr style="background-color:#cf9d2b;">';

	echo '<td>';
		echo '<img src="/i/000000/tbox_se.png">';
	echo '</td>';

	echo '<td>';
		echo '<img src="/images/inverse.png">';
	echo '</td>';

	echo '<td>&nbsp;</td>';

	echo '<td>';
		echo '<img src="/images/multiplication.png">';
	echo '</td>';

	echo '<td>';
		echo '<img src="/images/result.png">';
	echo '</td>';

echo '</tr>';


echo '<tr>';

echo '<td>';
	echo '<table cellpadding=0>';
	for( $x=0; $x<$width; $x++ ) {
		echo '<tr>';
		for( $y=0; $y<$height; $y++ ) {
			echo '<td>';

			$color = imagecolorat($img, $x, $y);
			$c = colornormalize(imagecolorsforindex($img, $color));
			echo colorshow($c);

			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
echo '</td>';

echo '<td>';
	echo '<table cellpadding=0>';
	for( $x=0; $x<$width; $x++ ) {
		echo '<tr>';
		for( $y=0; $y<$height; $y++ ) {
			echo '<td>';

			$color = imagecolorat($img, $x, $y);
			$c = colornormalize(imagecolorsforindex($img, $color));

			$n = colorinvert($c,true);

			$ncol = imagecolorallocatealpha($inv,$n['red']*255,$n['green']*255,$n['blue']*255,$n['alpha']*127);
			imagesetpixel($inv, $x, $y, $ncol);

			echo colorshow($n);

			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
echo '</td>';

echo '<td>';
	echo '<table cellpadding=0>';
	for( $x=0; $x<$width; $x++ ) {
		echo '<tr>';
		for( $y=0; $y<$height; $y++ ) {
			echo '<td>';

			echo colorshow(colorinvert($t));

			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
echo '</td>';

echo '<td>';
	echo '<table cellpadding=0>';
	for( $x=0; $x<$width; $x++ ) {
		echo '<tr>';
		for( $y=0; $y<$height; $y++ ) {
			echo '<td>';

			$color = imagecolorat($img, $x, $y);
			$c = colornormalize(imagecolorsforindex($img, $color));

			$n = colormultiply(colorinvert($c,true), colorinvert($t));

			$ncol = imagecolorallocatealpha($mult,$n['red']*255,$n['green']*255,$n['blue']*255,$n['alpha']*127);
			imagesetpixel($mult, $x, $y, $ncol);

			echo colorshow($n);

			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
echo '</td>';


echo '<td>';
	echo '<table cellpadding=0>';
	for( $x=0; $x<$width; $x++ ) {
		echo '<tr>';
		for( $y=0; $y<$height; $y++ ) {
			echo '<td>';

			$color = imagecolorat($img, $x, $y);
			$c = colornormalize(imagecolorsforindex($img, $color));

			$n = colorinvert(colormultiply(colorinvert($c,true), colorinvert($t)),true);

			// cache the allocated image colors
			//$nstr = colorhexalpha($n);
			//if( !array_key_exists($nstr, $targetcolors) ) {
			//	$targetcolors[$nstr] = imagecolorallocatealpha($dst,$n['red']*255,$n['green']*255,$n['blue']*255,$n['alpha']*127);
			//}
			//$ncol = $targetcolors[$nstr];

			$ncol = imagecolorallocatealpha($dst,$n['red']*255,$n['green']*255,$n['blue']*255,$n['alpha']*127);

			imagesetpixel($dst, $x, $y, $ncol);

			echo colorshow($n);

			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
echo '</td>';


echo '</tr>';

echo '</table>';


echo "Numbers in the cells indicate the alpha values of the pixels. 0=opaque, 1=transparent<br>";
echo "<br>";
echo "For multiplication, all RGB and alpha values are normalized to values between 0 and 1. ";
echo "As a result, white times any other color results in the original color, and black times any other color results in black. For transparency, 0 is opaque and 1 is transparent.";


imagepng($dst,  "images/result.png");
imagepng($mult, "images/multiplication.png");
imagepng($inv,  "images/inverse.png");







function colornormalize($colorarray) {
	$result = array();
	foreach( array('red','green','blue') as $key ) {
		$result[$key] = ($colorarray[$key]/255);
	}
	$result['alpha'] = $colorarray['alpha']/127;
	return $result;
}

function colormultiply($ca1, $ca2) {
	// multiplies each value in $ca1 with the corresponding value in $ca2.
	// assumes the array keys will match
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

function colorshow($color) {
	$hex = sprintf("%02x",($color['red']*255));
	$hex .= sprintf("%02x",($color['green']*255));
	$hex .= sprintf("%02x",($color['blue']*255));
	return '<div style="color:#009900;background-color:#'.$hex.';width:15px;height:15px;">'.str_replace("0.",".",round($color['alpha'],1)).'</div>';
}

function colortext($color) {
	$str = "";
	$str .= 'r'.round($color['red'],2).':';
	$str .= 'g'.round($color['green'],2).':';
	$str .= 'b'.round($color['blue'],2).':';
	$str .= 'a'.round($color['alpha'],2);
	return $str;
}

function colorhexalpha($color) {
	// returns an html-like string (RGBA) e.g. FF0000FF
	$hex = sprintf("%02x",($color['red']*255));
	$hex .= sprintf("%02x",($color['green']*255));
	$hex .= sprintf("%02x",($color['blue']*255));
	$hex .= sprintf("%02x",($color['alpha']*255));
	return $hex;
}



echo str_repeat('<br>',20);


PrintFooter();



?>