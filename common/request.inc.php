<?php

function Request($key) {
// return what is in the request variable if it exists
	if( array_key_exists($key, $_REQUEST) ) {
		return $_REQUEST[$key];
	} else {
		return false;
	}
}

function session($key) {
// return what is in the session variable if it exists
	if( array_key_exists($key, $_SESSION) ) {
		return $_SESSION[$key];
	} else {
		return false;
	}
}


//*************************
// KeyInRequest($key)
//
// Checks the global request variable to
// see if key has been sent to it.
// Saves typing array_key_exists each time
//*************************
function KeyInRequest($key) {
	if( array_key_exists($key,$_REQUEST) ) {
		return TRUE;
	} else {
		return FALSE;
	}
}


function QueryStringRemoveKey($key, $qs="") {
// removes $key from query string and returns new string
	if( $qs == "" ) {
		$qs = $_SERVER['QUERY_STRING'];
	}
	$qs_elts = explode("&",$qs);
	$qs_parts = array();
	foreach($qs_elts as $elt) {
		$part = explode("=",$elt);
		if( $part[0]!="" ) {
			if( array_key_exists(1,$part) ) {
				$equals = "=".$part[1];
			} else {
				$equals = "";
			}
			if($part[0]!=$key) $qs_parts[] = $part[0].$equals;
		}
	}
	return csl($qs_parts,"&");
}

function GetQueryStringVariables() {
// returns a key=>value array of all the query string variables
	$qs_elts = explode("&",$_SERVER['QUERY_STRING']);
	foreach($qs_elts as $elt) {
		$part = explode("=",$elt);
		if( array_key_exists(1,$part) ) {
			$equals = $part[1];
		} else {
			$equals = "";
		}
		$qs_parts[$part[0]] = $equals;
	}
	return $qs_parts;
}




?>