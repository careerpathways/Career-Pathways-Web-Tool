<?php


function PercentBar($percent) {
global $COLORS;
	$w = 200;
	$h = 16;
	$query[] = "w=$w";
	$query[] = "h=$h";
	$query[] = "bg=ffffff";
	$query[] = "bd=".$COLORS['highlight_dark'];
	$query[] = "br=".$COLORS['highlight'];
	$query[] = "p=$percent";
	$qs = csl($query, "&");
	return '<img src="/images/bar.php?'.$qs.'" width="'.$w.'" height="'.$h.'">';
}

function txt2html($text) {
// replaces line breaks with <br>, and double spaces with &nbsp;&nbsp;
	$text = str_replace("\n","<br>",$text);
	$text = str_replace("  ","&nbsp;&nbsp;",$text);

	return $text;
}

function TrimString($str, $length, $allow_word_break=false) {
// trims $str to $length characters
// if $str is too long, it puts ... on the end
// if $allow_word_break is true, doesn't split a word in the middle

	if( strlen($str) <= $length ) {
		return $str;
	} else {
		if( $allow_word_break ) {
			return trim(substr($str,0,$length-3))."...";
		} else {
			$newstr = substr($str,0,$length-3);
			return substr($newstr, 0, strrpos($newstr, " "))."...";
		}
	}
}


// Finds email addresses and urls in a body of text, and adds <a> tags
// around them. It will also obfuscate the email address with some
// javascript so that bots don't recognize the email address there.

function ActivateLinks($text, $emailonly=false) {

	$matches = array();
	// regex based on http://www.regular-expressions.info/email.html
	preg_match_all('/\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+[a-zA-Z]{2,4})\b/', $text, $matches);

	if( count($matches[0]) > 0 ) {
		foreach( $matches[0] as $email ) {
			$text = str_replace($email, EmailEncrypt::EmailLink($email), $text);
		}
	}

	if( !$emailonly ) {
		// regex from http://fundisom.com/phparadise/php/string_handling/autolink
		$text = preg_replace( '/(http|ftp)+(s)?:(\/\/)((\w|\.)+)(\/)?(\S+)?/i', '<a href="\0">\4</a>', $text );
	}

	return $text;

}


?>