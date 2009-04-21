<?php
//*********************************************************
// Generate an image containing text in a custom font.
//
// Version 1.00
// Copyright © 2004 by Aaron Parecki, all rights reserved.
//*********************************************************

class GenerateImage {

	var $font_path;

	var $img_width;
	var $img_height;

	var $margin;

	var $string;

	var $bkg = Array();

	var $text = Array();

	var $max_font_size;

	// End user defined variables
	//********************************************************************//

	function Output() {
		$im = imagecreate($this->img_width, $this->img_height);
		$bkg = imagecolorallocate($im, $this->bkg['red'], $this->bkg['green'], $this->bkg['blue']);
		$text_color = imagecolorallocate($im, $this->text['red'], $this->text['green'], $this->text['blue']);

		$font_size = 180;

		// shrink text to fit width
		$text_size = imagettfbbox( $font_size, 0, $this->font_path, $this->string );
		$text_width = $text_size[2];

		if( $text_width > $this->img_width ) {
			$font_size *= (($this->img_width-($this->margin*2))/$text_width);
		}

		// if the text is too tall, then shrink to maximum height
		if( $font_size > $this->max_font_size ) $font_size = $this->max_font_size;

		// get size again for vertical alignment
		$text_size = imagettfbbox( $font_size, 0, $this->font_path, $this->string );
		$text_height = $text_size[1]-$text_size[7];

		// align text vertically;
		$ypos = ($this->img_height/2)+($text_height/2)-$text_size[1];

		// create text in image
		imagettftext($im, $font_size, 0, $this->margin, $ypos,
		$text_color, "$this->font_path", $this->string);

		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);
	}
}

/*
USAGE:

$img = new GenerateImage;

	$img->font_path = "lt_50062.ttf";

	$img->img_width = 500;
	$img->img_height = 80;

	$img->margin = 10;

	$img->string = "legislative team";

	$img->bkg['red'] = 0;
	$img->bkg['green'] = 102;
	$img->bkg['blue'] = 68;

	$img->text['red'] = 255;
	$img->text['green'] = 255;
	$img->text['blue'] = 255;

	$img->max_font_size = 40;

	$img->GenerateImage();
*/
?>