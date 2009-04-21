<?php

function str2bool($str) {
	if( strtolower($str) == "yes" ) {
		return true;
	} else {
		return false;
	}
}

// restrict $variable to within max and min
// min/max is optional, in which case there is
// no min/maximum value
function limit(&$variable, $max="", $min="") {
	if( $max != "" ) {
		if( $variable > $max) {
			$variable = $max;
		}
	}
	if( $min != "" ) {
		if( $variable < $min ) {
			$variable = $min;
		}
	}
}

function emod($x, $y) {
	$z = $x % $y;
	if( $z == 0 ) {
		$z = $y;
	}
	if( $z < 0 ) {
		$z = $y + $z;
	}
	return $z;
}

function csl($arr, $sep=", ", $show_and=false) {
// returns a comma separated list from an array
// element1, element2, element3

	$copy = $arr;

	$str = "";
	if( count($copy) > 0 ) {
		$str .= array_shift($copy);

		if($show_and) $last = array_pop($copy);

		foreach( $copy as $tmp ) {
			$str .= $sep.$tmp;
		}

		if($show_and && count($arr)>1 ) $str .= " and ".$last;
	}
	return $str;
}


/**
  * from: http://php.net/array_keys
  * author: RQuadling at GMail dot com
  * array array_remove ( array input, mixed search_value [, bool strict] )
  **/
function array_remove(&$a_Input, $m_SearchValue, $b_Strict=false) {
    $a_Keys = array_keys($a_Input, $m_SearchValue, $b_Strict);
    foreach($a_Keys as $s_Key) {
        unset($a_Input[$s_Key]);
    }
    return $a_Input;
}



/* returns the key of the item in the array with the largest value in $col.
 * example:
 *
 * $val[0] = array('name'=>"Red", 'num'=>10)
 * $val[1] = array('name'=>"Blue", 'num'=>8)
 * $val[2] = array('name'=>"Green", 'num'=>2)
 *
 * array_array_max($val, 'num')
 * returns: 0
 *
 */
function array_array_max(&$arr, $col) {

	list($mkey, $marr) = each($arr);
	$mval = $marr[$col];

	while( list($nkey, $narr) = each($arr) ) {
		if( $narr[$col] > $mval ) {
			$mkey = $nkey;
			$mval = $narr[$col];
		}
	}

	return $mkey;
}


/* checks whether two values are within $tolerance
 */
function within($target, $test, $tolerance) {
	return (abs($target-$test) <= $tolerance);
}


?>