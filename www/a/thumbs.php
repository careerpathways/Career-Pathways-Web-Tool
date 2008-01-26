<?php
chdir("..");
include("inc.php");

PrintHeader();

$drawings = $DB->ArrayQuery("
	SELECT *
	FROM drawings
	WHERE deleted=0
	ORDER BY version_num");


$T = new WrappingTable();
$T->cols = 6;

foreach( $drawings as $d ) {
	$f = $d['id'].'.'.md5($d['last_modified']).'.gif';
	$T->AddItem('<a href="/files/charts/chart.svg?drawing_id='.$d['id'].'"><img src="/files/charts/chart.gif?drawing_id='.$d['id'].'" width="140" height="100"></a>');
}

$T->Output();

PrintFooter();

?>