<?php

// replacement function for php < 5.2

if( !function_exists('json_encode') ) {

	function json_encode($array, $objName=false) {
	  if ($objName) return 'var '.$objName.'='.json_encode_r($array).';';
	  else return json_encode_r($array);
	}

	function json_encode_r($array) {
	  if(!is_array($array) && !is_object($array)) {
		if ($array===null) return 'null';
		return'"'.str_replace(array("\r\n","\n",'<','>'),array('\r\n','\n','\<','\>'),addslashes($array)).'"';
	  }
	  $retVal = array();
	  foreach($array as $key => $value) {
		if (is_string($key)) $key='"'.$key.'"';
		$retVal[].= $key.':'.json_encode_r($value);
	  }
	  return "{".implode(",",$retVal)."}";
	}

}

	// the json_encode_r function seems to have trouble distinguishing between an array and an object.
	// this method allows us to force an array to be created (all indices numeric and sequential)
	// (must be defined outside of function_exists method, because it does not replace a standard php function)
	function json_encode_array($array) {
	  if(!is_array($array)) {
		if ($array===null) return 'null';
		if (is_numeric($array)) return $array;
		return'"'.str_replace(array("\r\n","\n",'<','>'),array('\r\n','\n','\<','\>'),addslashes($array)).'"';
	  }
	  $retVal = array();
	  foreach($array as $key => $value) {
		$retVal[] = json_encode_array($value);
	  }
	  return "[".implode(",",$retVal)."]";
	}


?>