<?php
/**
 * This is a class that is designed for generic string manipulation. There are unit tests for this code located at:
 * /home/project/cpwt_oregon_template/core/tests/unit/common/string_tools/String_Tools_Test
 */
class String_Tools
{
	/**
	 * this function strips out any characters that are not alphanumeric.
	 * @param  string $string this is the string to be cleaned.
	 * @return string         this is your cleaned string.
	 */
	public static function clean($string){
		$string = preg_replace("/[^A-Za-z0-9 ]/", "", $string);
		return $string;
	}

	/**
	 * Turns all underscores and dashes into spaces.
	 * @param  string $string This is your string to clean.
	 * @return string         Returned string. Should have spaces instead of dashes and underscores.
	 */
	public static function clean_spacing($string){
		$string = preg_replace("/[-_ ]/", " ", $string);
		return $string;
	}

	/**
	 * Capitalizes the first letter of a string.
	 * @param  string $string String to be properly cased.
	 * @return string         Return string that is properly cased.
	 */
    public static function prop_case($string){
		preg_match_all('/([A-Za-z\-\/]+)/', $string, $matches);
		$letters = $matches[1];

		foreach($letters as &$l){
			$l = ucfirst(strtolower($l));
		}
		return implode(' ', $letters);
	}

	/**
	 * Will do the same as prop case, but takes an array of exceptions and uses whatever casing the exception uses.
	 * @param  string $string     The string to properly case.
	 * @param  array  $exceptions List of exceptions that will not be used for proper casing.
	 * @return string             String that has its first letter capitalized unless it is on the exception list.
	 */
	public static function prop_case_exceptions($string, $exceptions){
		$words = explode(" ", $string);
		if (count($exceptions) > 0){
			foreach($words as $i => $word){
				$match = false;
				foreach ($exceptions as $exception) {
					if (strtolower($word) == strtolower($exception)){
						$match = true;
                        break;
					}
				}
				if (!$match){
					$words[$i] = String_Tools::prop_case($word);
				}
			}
			$string = implode(" ", $words);
		} else {
			$string = String_Tools::prop_case($string);
		}
		return $string;
	}


	/**
	 * Fixes double,  triple, etc. spacing by reducing them to one space.
	 * @param  string $string input string to be cleaned.
	 * @return string         string with only single spaces.
	 */
	public static function remove_multi_space($string){
        $string = preg_replace("/\s+/", " ", $string);
        return $string;
    }

    /**
     * Remove white-spaces from string
     * @param  string $string string to be altered
     * @return string         space-less string
     */
	public static function remove_spaces($string){
        $string = preg_replace("/ +/", "", $string);
        return $string;
    }
}
