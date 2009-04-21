<?php

class NameGen {

	var $rules;

	function NameGen($fn="") {
		$file = file($fn,FILE_USE_INCLUDE_PATH);
		foreach( $file as $rule ) {
			if( substr($rule,0,1) != "#" ) {
				$index = substr($rule,0,1);
				$rules = substr($rule,2);
				$this->rules[$index] = trim($rules);
			}
		}
	}

	/* public */ function word() {
		return $this->parse("W");
	}

	/* private */ function parse($rule) {
		$tmp = "";
		for( $i=0; $i<strlen($rule); $i++ ) {
			$cur = substr($rule,$i,1);
			if( ctype_upper($cur) ) {
				$options = explode(" ",$this->rules[$cur]);
				$tmp .= $this->parse($options[rand(0,count($options)-1)]);
			} else {
				$tmp .= $cur;
			}
		}
		return $tmp;
	}
}

?>