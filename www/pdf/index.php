<?php
chdir('..');
include('inc.php');

switch(request('mode'))
{
        case 'drawing':
                $url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/published/' . request('drawing_id') . '/view.html';
                $filename = 'published-' . request('drawing_id') . '.pdf';
                $drawing = $DB->SingleQuery('SELECT drawing_main.*,
				IF(drawing_main.name="", p.title, drawing_main.name) AS full_name, s.school_name
			FROM drawing_main
			LEFT JOIN programs AS p ON drawing_main.program_id=p.id
			LEFT JOIN schools AS s ON s.id = drawing_main.school_id
			WHERE drawing_main.id = ' . request('drawing_id'));
		$version = $DB->SingleQuery('SELECT version_num FROM drawings WHERE parent_id = ' . $drawing['id'] . ' AND published = 1');
		//$name = $drawing['school_name'] . ' - ' . $drawing['full_name'] . ' - Version ' . $version['version_num'];
        $name = GetDrawingName($drawing['id'], 'roadmap') . ' - Version ' . $version['version_num'];
                break;

        case 'version':
                $url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/version/' . request('drawing_id') . '/' . request('version_id') . '.html';
                $filename = 'version-' . request('version_id') . '.pdf';
                $drawing = GetDrawingInfo(request('version_id'));
		//$name = $drawing['school_name'] . ' - ' . $drawing['full_name'] . ' - Version ' . $drawing['version_num'];
        $name = GetDrawingName(request('drawing_id'), 'roadmap') . ' - Version ' . $drawing['version_num'];;
                break;
                
        case 'post_drawing':
                $url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/post/' . request('drawing_id') . '/view.html?hidecoursedescription';
                $filename = 'post-published-' . request('drawing_id') . '.pdf';
                $drawing = $DB->SingleQuery('SELECT post_drawing_main.*,
				IF(post_drawing_main.name="", p.title, post_drawing_main.name) AS full_name, s.school_name
			FROM post_drawing_main
			LEFT JOIN programs AS p ON post_drawing_main.program_id=p.id
			LEFT JOIN schools AS s ON s.id = post_drawing_main.school_id
			WHERE post_drawing_main.id = ' . request('drawing_id'));
		$version = $DB->SingleQuery('SELECT version_num FROM post_drawings WHERE parent_id = ' . $drawing['id'] . ' AND published = 1');
		//$name = $drawing['school_name'] . ' - ' . $drawing['full_name'] . ' - Version ' . $version['version_num'];
        $name = GetDrawingName(request('drawing_id'), 'post') . ' - Version ' . $version['version_num'];
                break;
                
        case 'post_version':
                $url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/post/' . request('drawing_id') . '/' . request('version_id') . '.html?hidecoursedescription';
                $filename = 'post-version-' . request('version_id') . '.pdf';
                $drawing = GetDrawingInfo(request('version_id'), 'post');
		//$name = $drawing['school_name'] . ' - ' . $drawing['name'] . ' - Version ' . $drawing['version_num'];
        $name = GetDrawingName(request('drawing_id'), 'post') . ' - Version ' . $version['version_num'];
                break;          
                
        case 'post_view':
                $url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/study/' . request('id') . '/view.html?print&hidecoursedescription';
                $filename = 'post-view-' . request('id') . '.pdf';
                $drawing = $DB->SingleQuery('SELECT name, school_name
			FROM vpost_views v
			JOIN schools s on v.school_id = s.id
			WHERE v.id = ' . request('id'));
                $name = $drawing['school_name'] . ' - ' . $drawing['name'];
                break;

        case 'post_view_assurance':
                logmsg( "post_view_assurance\n" );
                $url = 'http://' . $_SERVER['SERVER_NAME'] . '/a/post_assurance.php?id=' . request('view_id') . '&assurance_id='.request('assurance_id').'&pdf_format';
            //$command  = '"C:\\Program Files (x86)\\wkhtmltopdf\\wkhtmltopdf" "' . $url . '&session_id='.$session_id.'" ' . $fullPath;
                
                $drawing = $DB->SingleQuery('SELECT name, school_name, published, DATE_FORMAT(assurances.created_date,"%m-%d-%Y") as "created_date" 
                        FROM vpost_views v
                        JOIN assurances on v.id=assurances.vpost_view_id
                        JOIN schools s on v.school_id = s.id
                        WHERE v.id = ' . request('view_id'));
                $filename = 'post-view-assurance-' . request('view_id') . '-' . $drawing['created_date'] . '.pdf';
                $name = $drawing['school_name'] . '-' . $drawing['name'] . '('.$drawing['created_date'].')';
                break;
                
                
        default:
                die('error');
}

$fullPath = $SITE->cache_path("pdf").$filename;

if(!file_exists($fullPath) || filemtime($fullPath))
{
        logmsg( "Calling shell_exec.\n" );
        
        //Closing the session to make it available via remote command below.
        $session_id = session_id();
        session_write_close();
        
        $command  =  $SITE->wkhtmltopdf_bin() .' "'. $url . '&session_id='.$session_id.'&isPDF" ' . $fullPath;

        logmsg ("Shell_exec command: " . $command);
        $retval = shell_exec($command);
        //$retval = shell_exec('wkhtmltopdf-amd64 "' . $url . '" ' . $fullPath);
        logmsg( "Shell_exec retval: $retval\n" );
}
else
{
        logmsg( "Using cached version.\n" );
}

$name = str_replace('&', ' and ', $name);
$name = preg_replace('/[ ]+/', ' ', $name);
$name = preg_replace('/[^A-Za-z0-9 -]/', '', $name) . '.pdf';

header('Content-type: application/pdf');
header('Content-disposition: attachment; filename="' . addslashes($name) . '"');
header('Cache-control: no-cache');
header("X-Download-Options: noopen"); // For IE8
header("X-Content-Type-Options: nosniff"); // For IE8
/*
if( function_exists('header_remove') ) {
	header_remove('Pragma');
} else {
	header('Pragma:');
}
*/
// added "Pragma: public" to fix downloads in IE 8 -aaronpk 12/6
header('Pragma: public');
readfile($fullPath);

