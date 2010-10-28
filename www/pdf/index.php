<?php
chdir('..');
include('inc.php');

switch(request('mode'))
{
	case 'drawing':
		$url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/published/' . request('drawing_id') . '/view.html';
		$filename = 'published-' . request('drawing_id') . '.pdf';
		$drawing = $DB->SingleQuery('SELECT drawing_main.*,
				IF(drawing_main.name="", p.title, drawing_main.name) AS full_name
			FROM drawing_main
			LEFT JOIN programs AS p ON drawing_main.program_id=p.id
			WHERE drawing_main.id = ' . request('drawing_id'));
		$name = $drawing['full_name'] . '.pdf';
		break;

	case 'version':
		$url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/version/' . request('drawing_id') . '/' . request('version_id') . '.html';
		$filename = 'version-' . request('version_id') . '.pdf';
		$drawing = GetDrawingInfo(request('version_id'));
		$name = $drawing['full_name'] . ' - Version ' . $drawing['version_num'] . '.pdf';
		break;
		
	case 'post_drawing':
		$url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/post/' . request('drawing_id') . '/view.html?hidecoursedescription';
		$filename = 'post-published-' . request('drawing_id') . '.pdf';
		$drawing = $DB->SingleQuery('SELECT post_drawing_main.*,
				IF(post_drawing_main.name="", p.title, post_drawing_main.name) AS full_name
			FROM post_drawing_main
			LEFT JOIN programs AS p ON post_drawing_main.program_id=p.id
			WHERE post_drawing_main.id = ' . request('drawing_id'));
		$name = $drawing['full_name'] . '.pdf';
		break;
		
	case 'post_version':
		$url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/post/' . request('drawing_id') . '/' . request('version_id') . '.html?hidecoursedescription';
		$filename = 'post-version-' . request('version_id') . '.pdf';
		$drawing = GetDrawingInfo(request('version_id'), 'post');
		$name = $drawing['name'] . ' - Version ' . $drawing['version_num'] . '.pdf';
		break;		
		
	default:
		die('error');
}

$fullPath = 'pdf/tmp/' . $filename;

if(!file_exists($fullPath) || filemtime($fullPath))
	shell_exec('/usr/bin/wkhtmltopdf-i386 "' . $url . '" ' . $fullPath);

header('Content-type: application/pdf');
header('Content-disposition: attachment; filename="' . addslashes($name) . '"');
readfile($fullPath);

?>
