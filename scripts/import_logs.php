#!/usr/bin/php
<?php
chdir('/web/oregon.ctepathways.org/www');
set_include_path('.:../www/inc:../common');
include('inc.php');

$log_dir = '../logs';

$files = explode("\n",shell_exec('ls -1 '.$log_dir.' | grep access'));
array_pop($files); // remove last filename (always empty)

foreach( $files as $file ) {

	$check = $DB->SingleQuery('SELECT COUNT(*) AS num FROM logs_processed WHERE filename="'.$file.'"');
	if( $file != 'access_log' && $check['num'] == 0 ) {

		echo $file."\n";

		$lines = file_get_contents($log_dir.'/'.$file);

		preg_match_all('~([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}) - - \[([^\]]*)\] "GET (/c/(?:version|published|text)/([^\./ ]+)[^ ]*) HTTP/1\.[01]*" (\d+) (\d+) "([^"]*)" "([^"]*)"\n~',$lines,$matches);
		for( $i=0; $i<count($matches[0]); $i++ ) {
			$rec['remote_addr'] 		= $matches[1][$i];
			$rec['date'] 				= date("Y-m-d H:i:s",strtotime($matches[2][$i]));
			$rec['url'] 				= $matches[3][$i];
			$rec['drawing_code']		= $matches[4][$i];

			$lookup = $DB->SingleQuery('SELECT * FROM drawing_main WHERE code="'.$rec['drawing_code'].'"');
			$rec['drawing_id']			= intval($lookup['id']);

			$rec['status_code'] 		= $matches[5][$i];
			$rec['bytes_transferred'] 	= $matches[6][$i];
			$rec['referer'] 			= $matches[7][$i];
			$rec['user_agent'] 			= $matches[8][$i];
			$DB->Insert('logs',$rec);
		}

		$DB->Insert('logs_processed', array('filename'=>$file, 'date_processed'=>$DB->SQLDate()));
	}
}

?>
