<?php

function RGBtoXYZ($rgb=array()) {
	// a PHP implementation of easyrgb.com's formula
	// http://www.easyrgb.com/math.php?MATH=M2#text2

	$var_R = ( $rgb['r'] / 255 );        //Where R = 0 ÷ 255
	$var_G = ( $rgb['g'] / 255 );        //Where G = 0 ÷ 255
	$var_B = ( $rgb['b'] / 255 );        //Where B = 0 ÷ 255

	if ( $var_R > 0.04045 ) {
		$var_R = pow(( ( $var_R + 0.055 ) / 1.055 ), 2.4);
	} else {
		$var_R = $var_R / 12.92;
	}
	if ( $var_G > 0.04045 ) {
		$var_G = pow(( ( $var_G + 0.055 ) / 1.055 ), 2.4);
	} else {
		$var_G = $var_G / 12.92;
	}
	if ( $var_B > 0.04045 ) {
		$var_B = pow(( ( $var_B + 0.055 ) / 1.055 ), 2.4);
	} else {
		$var_B = $var_B / 12.92;
	}

	$var_R = $var_R * 100;
	$var_G = $var_G * 100;
	$var_B = $var_B * 100;

	//Observer. = 2°, Illuminant = D65
	$X = $var_R * 0.412424 + $var_G * 0.357579 + $var_B * 0.180464;
	$Y = $var_R * 0.212656 + $var_G * 0.715158 + $var_B * 0.0721856;
	$Z = $var_R * 0.0193324 + $var_G * 0.119193 + $var_B * 0.950444;

	return array('x'=>$X, 'y'=>$Y, 'z'=>$Z);
}

function XYZtoLab($xyz=array()) {
	// a PHP implementation of easyrgb.com's formula
	// http://www.easyrgb.com/math.php?MATH=M2#text7

	$ref_X = 95.047;
	$ref_Y = 100.000;
	$ref_Z = 108.883;

	$var_X = $xyz['x'] / $ref_X;          //ref_X =  95.047  Observer= 2°, Illuminant= D65
	$var_Y = $xyz['y'] / $ref_Y;          //ref_Y = 100.000
	$var_Z = $xyz['z'] / $ref_Z;          //ref_Z = 108.883

	if ( $var_X > 0.008856 ) {
		$var_X = pow($var_X,(1/3));
	} else {
		$var_X = ( 7.787 * $var_X ) + ( 16 / 116 );
	}
	if ( $var_Y > 0.008856 ) {
		$var_Y = pow($var_Y,(1/3));
	} else {
		$var_Y = ( 7.787 * $var_Y ) + ( 16 / 116 );
	}
	if ( $var_Z > 0.008856 ) {
		$var_Z = pow($var_Z,(1/3));
	} else {
		$var_Z = ( 7.787 * $var_Z ) + ( 16 / 116 );
	}

	$L = ( 116 * $var_Y ) - 16;
	$a = 500 * ( $var_X - $var_Y );
	$b = 200 * ( $var_Y - $var_Z );

	return array('L'=>$L, 'a'=>$a, 'b'=>$b);
}

function RGBtoHSL($rgb) {
	// input: hex string or array
	// output: array, with keys H, S, and L

	// a PHP implementation of easyrgb.com's formula
	// http://www.easyrgb.com/math.php?MATH=M2#text18

	if( is_array($rgb) ) {
		$R = ( $rgb['r'] );
		$G = ( $rgb['g'] );
		$B = ( $rgb['b'] );
	} else {
		$R = hexdec(substr($rgb,0,2));
		$G = hexdec(substr($rgb,2,2));
		$B = hexdec(substr($rgb,4,2));
	}

	$var_R = ( $R / 255 );                     //Where RGB values = 0 ÷ 255
	$var_G = ( $G / 255 );
	$var_B = ( $B / 255 );

	$var_Min = min( $var_R, $var_G, $var_B );    //Min. value of RGB
	$var_Max = max( $var_R, $var_G, $var_B );    //Max. value of RGB
	$del_Max = $var_Max - $var_Min;             //Delta RGB value

	$L = ( $var_Max + $var_Min ) / 2;

	if ( $del_Max == 0 )                     //This is a gray, no chroma...
	{
	   $H = 0;                                //HSL results = 0 ÷ 1
	   $S = 0;
	}
	else                                    //Chromatic data...
	{
	   if ( $L < 0.5 ) $S = $del_Max / ( $var_Max + $var_Min );
	   else           $S = $del_Max / ( 2 - $var_Max - $var_Min );

	   $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
	   $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
	   $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

	   if      ( $var_R == $var_Max ) $H = $del_B - $del_G;
	   else if ( $var_G == $var_Max ) $H = ( 1 / 3 ) + $del_R - $del_B;
	   else if ( $var_B == $var_Max ) $H = ( 2 / 3 ) + $del_G - $del_R;

	   if ( $H < 0 ) ; $H += 1;
	   if ( $H > 1 ) ; $H -= 1;
	}

	return array('H'=>$H, 'S'=>$S, 'L'=>$L);
}


function RGBtoLab($rgb) {
// input: hex string
// output: array, with keys L, a and b

	$array['r'] = hexdec(substr($rgb,0,2));
	$array['g'] = hexdec(substr($rgb,2,2));
	$array['b'] = hexdec(substr($rgb,4,2));

	return XYZtoLab(RGBtoXYZ($array));
}


function GetMostCommonColor($im, $round=true) {
// parameter is a gd image object. should be pretty small so it doesn't run forever

    $imgWidth = imagesx($im);
    $imgHeight = imagesy($im);
    for ($y=0; $y < $imgHeight; $y++)
    {
        for ($x=0; $x < $imgWidth; $x++)
        {
            $index = imagecolorat($im,$x,$y);
            $Colors = imagecolorsforindex($im,$index);
			if( $round ) {
	            $Colors['red']=intval((($Colors['red'])+15)/32)*32;    //ROUND THE COLORS, TO REDUCE THE NUMBER OF COLORS, SO THE WON'T BE ANY NEARLY DUPLICATE COLORS!
	            $Colors['green']=intval((($Colors['green'])+15)/32)*32;
	            $Colors['blue']=intval((($Colors['blue'])+15)/32)*32;
			}
            if ($Colors['red']>=256) $Colors['red']=240;
            if ($Colors['green']>=256) $Colors['green']=240;
            if ($Colors['blue']>=256) $Colors['blue']=240;

			// force the value to be a string otherwise the array_count_values function treats numbers like 002020 as 2020
			// prepend the "#" to make this a string instead of a number
            $hexarray[]="#".sprintf("%02X%02X%02X",$Colors['red'],$Colors['green'],$Colors['blue']);
        }
    }
    $hexarray=array_count_values($hexarray);
	arsort($hexarray);
    return $hexarray;
}




?>