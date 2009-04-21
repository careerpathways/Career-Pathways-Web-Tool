<?php

function pluralize($word, $number) {
// adds s or ies if the number is not 1
	if( $number == 1 ) {
		$plural = $word;
	} else {
		if( substr($word,-1) == "y" && !is_vowel(substr($word,-2,1)) ) {
			$plural = substr($word,0,strlen($word)-1)."ies";
		} else {
			$plural = $word."s";
		}
	}
	return $plural;
}

function posessive($word) {
// adds ' or 's depending on last letter
	if( substr($word,-1) == "s" ) {
		$new = $word."'";
	} else {
		$new = $word."'s";
	}
	return $new ;
}

function is_vowel($c) {
	return in_array($c,array('a','e','i','o','u'));
}

function add_indefinite_article($str) {
	if( substr_count("aeiouAEIOU", substr($str,0,1) ) == 1 ) {
		$n = "n";
	} else {
		$n = "";
	}
	return "a$n ".$str;
}


//*****************************************
// takes a number and returns the number
// with the ordinal suffix
//*****************************************
function ordinalize($number, $numeric=false) {
	$driver = substr("$number", -1); // get last character

	// show first, second, third until 12, then show 13th, 21st, etc
	if( $number <= 12 && $numeric == false) {
		switch($number)
		{
			case '1':
				$number = "first";
				break;
			case '2':
				$number = "second";
				break;
			case '3':
				$number = "third";
				break;
			case '4':
				$number = "fourth";
				break;
			case '5':
				$number = "fifth";
				break;
			case '6':
				$number = "sixth";
				break;
			case '7':
				$number = "seventh";
				break;
			case '8':
				$number = "eighth";
				break;
			case '9':
				$number = "ninth";
				break;
			case '10':
				$number = "tenth";
				break;
			case '11':
				$number = "eleventh";
				break;
			case '12':
				$number = "twelfth";
				break;
		}
	} else {
		switch($driver)
		{
			case '1':
				$number .= "st";
				break;
			case '2':
				$number .= "nd";
				break;
			case '3':
				$number .= "rd";
				break;
			default:
				$number .= "th";
				break;
		}
	}

	return $number;
}

function deordinalize($str)
{
	$str = strtolower($str);

	$ord['first'] = 1;
	$ord['second'] = 2;
	$ord['third'] = 3;
	$ord['fourth'] = 4;
	$ord['fifth'] = 5;
	$ord['sixth'] = 6;
	$ord['seventh'] = 7;
	$ord['eighth'] = 8;
	$ord['ninth'] = 9;
	$ord['tenth'] = 10;
	$ord['eleventh'] = 11;
	$ord['twelfth'] = 12;

	if( array_key_exists($str, $ord) )
		return $ord[$str];
	else
		return $str;

}



?>
