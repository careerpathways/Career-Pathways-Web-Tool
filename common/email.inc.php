<?php

class EmailEncrypt {

	public static function PrintEmailJavascript() {
	?>
		function swapPairs(s){
		  var res = "";
		  for (var i=0; i<s.length; i++){
			var ch = s.charCodeAt(i) ;
			res += String.fromCharCode(
				   ( ch & 0xF0 ) +
				   ((ch & 0x0C)>>2) +
				   ((ch & 0x03)<<2)
				   );
			}
		  return res;
		}
	<?php
	}

	public static function EmailLink($email, $text="") {
		$text = str_replace("'","\\'",$text);
		if( $text == "" ) { $text = $email; }

		$encrypted = EmailEncrypt::Encrypt('<a href="mailto:'.$email.'">'.$text.'</a>');

		return '<script>document.write(swapPairs("'.$encrypted.'"))</script>';
	}

	private static function Encrypt($email) {
		$res = "";
		for( $i=0; $i<strlen($email); $i++ ) {
			$ch = ord(substr($email,$i,1));
			$res .= chr(($ch & 0xF0) + (($ch & 0x0C) >> 2) + (($ch & 0x03) << 2));
		}
		return $res;
	}


}

?>