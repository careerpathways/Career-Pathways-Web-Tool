<?php
chdir("..");
require_once("inc.php");

switch(Request('action'))
{
	case 'primary':
		$DB->Query('UPDATE external_links SET `primary` = 0 WHERE `drawing_id` = ' . intval(Request('drawing_id')));
		$DB->Query('UPDATE external_links SET `primary` = 1 WHERE `id` = ' . intval(Request('url_id')));
		$url = $DB->SingleQuery('SELECT url FROM external_links WHERE id = ' . intval(Request('url_id')));
		die(Request('url_id') . '|' . $url['url']);
}

?>