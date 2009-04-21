<?php

function getemail($logfile="") {

	if( $logfile != "" ) {
		$logf = fopen($logfile.'/'.date("Ymd.His"),"w");
	}


	$data = array();

	/////////////////////////////////////////////////////////////////////
	// Code to parse an incoming email.
	// Based off of http://gvtulder.f2o.org/evolt/mail/mail.php.txt
	//
	// read from stdin
	$fd = fopen("php://stdin", "r");
	$email = "";
	while (!feof($fd)) {
		$email .= fread($fd, 1024);
	}
	fclose($fd);

	// handle email
	$lines = explode("\n", $email);

	// empty vars
	$data['raw'] = $email;
	$data['from'] = "";
	$data['subject'] = "";
	$data['headers'] = array();
	$data['message'] = "";
	$splittingheaders = true;

	for ($i=0; $i<count($lines); $i++) {
		if( $logfile != "" ) {
			fwrite($logf, $lines[$i]."\n");
		}

		if ($splittingheaders) {
			// this is a header
			$data['headers'][] = $lines[$i]."\n";

			// look out for special headers
			if (preg_match("/^Subject: (.*)/", $lines[$i], $matches)) {
				$data['subject'] = $matches[1];
			}
			if (preg_match("/^From: (.*)/", $lines[$i], $matches)) {
				$data['from'] = $matches[1];
			}
			if (preg_match("/^Date: (.*)/", $lines[$i], $matches)) {
				$data['date'] = $matches[1];
			}
		} else {
			// not a header, but message
			$data['message'] .= $lines[$i]."\n";
		}

		if (trim($lines[$i])=="") {
			// empty line, header section has ended
			$splittingheaders = false;
		}
	}
	/////////////////////////////////////////////////////////////////////

	if( $logfile != "" ) {
		fclose($logf);
	}

	return $data;
}

?>