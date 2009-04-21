<?php
require_once "gd_vwfont.inc.php";


function imageloadfontvw($png,$first_char=" ") {
	return new vwfont($png,$first_char);
}

function imagevwstring($im, $vwfont, $imx, $imy, $text, $color) {

	$target_color = imagecolorsforindex($im, $color);

	$target = array(
		'red'   => $target_color['red'],
		'green' => $target_color['green'],
		'blue'  => $target_color['blue'],
		'alpha' => 0,
		);
	$t = colornormalize($target);

	$targetcolors = array();  // cache for allocated colors

	$cur_x = $imx;
	for( $i=0; $i<strlen($text); $i++ ) {
		$ch = ord(substr($text,$i,1));

		if( array_key_exists($ch, $vwfont->data) ) {
			for( $x=0; $x<$vwfont->data[$ch]['w']; $x++ ) {
				for( $y=1; $y<$vwfont->h; $y++ ) {

					// get the color of the text bitmap
					$text_color = imagecolorat($vwfont->im, $vwfont->data[$ch]['x']+$x, $y);
					$tc = colornormalize(imagecolorsforindex($vwfont->im, $text_color));

					// get the color of the existing image where this pixel will be placed (for blending)
					$bkg_color = imagecolorat($im, $cur_x+$x, $imy+$y-1);
					$bc = colornormalize(imagecolorsforindex($im, $bkg_color));

					$n = colorinvert(colormultiply(colorinvert($tc,true), colorinvert($t)),true);

					// greyscale in font bitmap corresponds to transparency in text
					$n['alpha'] = $tc['red'];

					// cache the allocated image colors
					$nstr = colorhexalpha($n);
					if( !array_key_exists($nstr, $targetcolors) ) {
						$targetcolors[$nstr] = imagecolorallocatealpha($im,$n['red']*255,$n['green']*255,$n['blue']*255,$n['alpha']*127);
					}
					$ncol = $targetcolors[$nstr];

					imagesetpixel($im, $cur_x+$x, $imy+$y-1, $ncol);
				}
			}

			$cur_x += $vwfont->data[$ch]['w'];
		}
	}

}


/****************************************
* class vwfont
*
* variable-width bitmap font
*
* Create a png image to define the font. The first row of pixels defines the
* widths of each character. Use two alternating colors for the top row, such
* as CCCCCC and FFFFFF to define each character's width. Characters should
* be black on a white background. Shades of grey are also supported, and
* define the transparency of the pixel.
*
****************************************/

class vwfont {

	public $im;
	public $data;
	public $h;

	function vwfont($png, $first_char=' ') {
		$this->im = imagecreatefrompng($png);
		$this->h = imagesy($this->im);

		$current_width = 1;
		$current_char = ord($first_char);
		$last_seen = imagecolorat($this->im, 0,0);
		$last_x = 0;

		for( $i=1; $i<imagesx($this->im); $i++ ) {
			$px = imagecolorat($this->im, $i,0);

			if( $px == $last_seen ) {
				$current_width++;
			} else {
				$this->data[$current_char] = array('x'=>$last_x, 'w'=>$current_width, 'c'=>chr($current_char));
				$current_width = 1;
				$current_char++;
				$last_x = $i;
			}

			$last_seen = $px;
		}

	}

}


?>