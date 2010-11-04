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
	
	case 'set':
		// Check if it already exists
		$url = $DB->SingleQuery('SELECT id FROM external_links WHERE url = "' . Request('url') . '"');
		$DB->Query('UPDATE external_links SET `primary` = 0 WHERE `drawing_id` = ' . Request('drawing_id'));
		if($url && array_key_exists('id', $url))
		{
			$DB->Query('UPDATE external_links SET `primary` = 1 WHERE `id` = ' . $url['id']);
		}
		else
		{
			$DB->Query('UPDATE external_links SET `primary` = 0 WHERE `drawing_id` = ' . Request('drawing_id'));
			$DB->Insert('external_links', array(
				'type' => Request('mode'),
				'drawing_id' => Request('drawing_id'),
				'url' => Request('url'),
				'`primary`' => 1,
				'last_seen' => date('Y-m-d H:i:s'),
				'counter' => 2
			));
		}
		
}

?>