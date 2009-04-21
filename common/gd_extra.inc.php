<?php

function imagettfwraptext($im, $size, $angle, $x, $y, $x_end, $leading, $color, $font, $text) {
// note: currently only tested with 0 degree angle
// todo: include any \n provided in input text

		$text = str_replace("\n"," ",$text);
		
        $cur_x = $x;
        $words = explode(' ',$text);
        foreach( $words as $w ) {
                $bbox = imagettfbbox($size,$angle,$font,$w.' ');
                if( $cur_x + $bbox[2] > $x_end ) {
                        $y += $leading;
                        $cur_x = $x;
                }

                imagettftext($im, $size, $angle, $cur_x, $y, $color, $font, $w.' ');
                $cur_x += $bbox[2];

        }       
        
}

?>