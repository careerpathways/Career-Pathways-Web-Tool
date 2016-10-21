<?php
function propcase($phrase)
{
	$exceptions = array(
		'ADV',
		'AG',
		'CAD',
		'CADD',
		'CS',
		'CTE',
		'CWE',
		'HSCS',
		'FBLA',
		'FFA',
		'ICT',
		'IND',
		'SEC',

		//Roman Numerals
		'I',
		'II',
		'III',
		'IV',
		'V',
		'VI',
		'VII',
		'VIII',
		'IX',
		'X',
	);
	//$words = explode(' ', $phrase);
	
	preg_match_all('/([A-Za-z\-\/]+)/', $phrase, $matches);
	$words = $matches[1];
	
	foreach($words as &$w){
		if(!in_array($w, $exceptions)){
			$w = ucfirst(strtolower($w));
		}
	}
	return implode(' ', $words);
}
