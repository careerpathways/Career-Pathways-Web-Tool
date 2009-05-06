<?php
chdir("..");
include("inc.php");

switch( Request('format') )
{
	case 'csv':
	case 'xml':
		$file = $SITE->cache_path('olmis').'olmis.'.Request('format');
		switch( Request('format') )
		{
			case 'csv':
				header('Content-type: text/plain');
				break;
			case 'xml':
				header("Content-type: text/xml");
				break;
		}
		header('Last-modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
		readfile($file);
		break;
	default:
		break;
}

?>