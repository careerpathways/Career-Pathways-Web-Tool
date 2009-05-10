<?php
chdir("..");
require_once("inc.php");

if( !in_array(Request('mode'), array('pathways','post')) )
	die();

$mode = Request('mode');

if( $mode == 'pathways' )
	$drawing = $DB->SingleQuery('SELECT * FROM drawing_main WHERE id='.intval(Request('id')));
elseif( $mode == 'post' )
	$drawing = $DB->SingleQuery('SELECT * FROM vpost_views WHERE id='.intval(Request('id')));

if( is_array($drawing) )
{
	$check = $DB->SingleQuery('SELECT * FROM external_links WHERE drawing_id = '.$drawing['id'].' AND `type` = "'.$mode.'" AND `url` = "'.$DB->Safe(Request('url')).'"');
	if( is_array($check) )
	{
		$DB->Query('UPDATE external_links SET last_seen=NOW(), counter=counter+1 WHERE id='.$check['id']);
	}
	else
	{
		$hit = array();
		$hit['type'] = $mode;
		$hit['drawing_id'] = $drawing['id'];
		$hit['url'] = Request('url');
		$hit['last_seen'] = date('Y-m-d H:i:s');
		$hit['counter'] = 1;
		$DB->Insert('external_links', $hit);
	}
}


?>