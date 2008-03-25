<?php
chdir("..");
include("inc.php");

ModuleInit('sessions');

PrintHeader();


$sessions = array();

$session_path = "../sessions";

$files = scandir("../sessions");
foreach( $files as $f ) {
	if( substr($f,0,4) == 'sess' ) {
		$info = unserialize_session_data(file_get_contents($session_path.'/'.$f));
		$sessions[] = $info;
	}
}

$session_keys = array();
foreach( $sessions as $session ) {
	foreach( $session as $key=>$val ) {
		if( !in_array($key, $session_keys) ) {
			$session_keys[] = $key;
		}
	}
}

echo '<table border=1>';
echo '<tr>';
foreach($session_keys as $header) {
	echo '<td>'.$header.'</td>';
}
echo '</tr>';
foreach( $sessions as $session ) {
	if( $session['user_id'] > 0 ) {
	echo '<tr>';
		foreach($session_keys as $key) {
			echo '<td>';
			if( array_key_exists($key, $session) ) {
				if( is_array($session[$key]) ) {
					PA($session[$key]);
				} else {
					echo $session[$key];
				}
			} else {
				echo '&nbsp;';
			}
			echo '</td>';
		}
	echo '</tr>';
	}
}
echo '</table>';

PrintFooter();


function unserialize_session_data( $serialized_string ) {
    $variables = array(  );
    $a = preg_split( "/(\w+)\|/", $serialized_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
    for( $i = 0; $i < count( $a ); $i = $i+2 ) {
        $variables[$a[$i]] = unserialize( $a[$i+1] );
    }
    return( $variables );
}

?>