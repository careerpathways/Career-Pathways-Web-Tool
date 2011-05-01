<?php
chdir("..");
include("inc.php");
require_once 'Pager.php';

ModuleInit('guestlogins');


PrintHeader();

$num_recs = $DB->SingleQuery("SELECT COUNT(*) AS num FROM guest_logins WHERE download = 0");

$params = array(
	'mode'		=> 'Sliding',
	'perPage'	=> 40,
	'delta'		=> 2,
	'totalItems'=> $num_recs['num'],
	'spacesBeforeSeparator' => 1,
	'spacesAfterSeparator' => 1,
	'urlVar' => 'pg',
	'curPageSpanPre' => '',
	'curPageSpanPost' => '',
	'nextImg' => '&gt;',
	'prevImg' => '&lt;',
	'curPageLinkClassName' => 'active',
	'clearIfVoid' => true,
);
$pager = &Pager::factory($params);
$offset = $pager->getOffsetByPageId();


$logins = $DB->MultiQuery('SELECT * FROM guest_logins
	WHERE download = 0
	ORDER BY date DESC
	LIMIT '.($offset[0]-1).', '.($params['perPage']));

if( $pager->links != "" ) {
	echo '<p><div class="pager_links">'.$pager->links.'</div></p>';
} else {
	echo '<br>';
}

echo '<table width="100%">';
echo '<tr>';
	echo '<th>Date</th>';
	echo '<th>Name</th>';
	echo '<th>Email</th>';
	echo '<th>School/Business</th>';
	echo '<th>Referral</th>';
	echo '<th>IP Address</th>';
echo '</tr>';
foreach( $logins as $log ) {
	echo '<tr>';	
	echo '<td>'.$DB->Date('m/d/y g:ia',$log['date']).'</td>';
	echo '<td>'.$log['first_name'].' '.$log['last_name'].'</td>';
	echo '<td>'.$log['email'].'</td>';
	echo '<td>'.$log['school'].'</td>';
	echo '<td>'.$log['referral'].'</td>';
	echo '<td>'.$log['ipaddr'].'</td>';
	echo '</tr>';
}
echo '</table>';

PrintFooter();

?>