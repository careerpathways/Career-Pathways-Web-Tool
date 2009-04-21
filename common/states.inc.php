<?php

$STATES = array('AL','AK','AJ','AZ','AR','CA','CO','CT','DE','DC','FL',
				'GA','HI','ID','IL','IN','IA','KS','KY','LA','ME',
				'MD','MA','MI','MN','MS','MO','MT','NE','NV','NH',
				'NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI',
				'SC','SD','TN','TX','UT','VT','VA','WA','WV','WI',
				'WY');
$allstates = array();
foreach( $STATES as $state ) {
	$allstates[$state] = $state;
}
$STATES = $allstates;
unset($allstates);


?>
