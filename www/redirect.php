<?php
include("inc.php");

$source = $_GET['source'];

$data = array();
$data['date'] = date('Y-m-d H:i:s');
$data['type'] = $_GET['type'];
$data['drawing_id'] = intval($_GET['id']);
$data['remoteAddr'] = $_SERVER['REMOTE_ADDR'];
$data['source'] = $_GET['source'];
if(array_key_exists('HTTP_REFERER', $_SERVER))
        $data['referer'] = $_SERVER['HTTP_REFERER'];
$DB->Insert('link_visits', $data);

switch($source)
{
        case 'view':
                $utm_source = '';
                break;
        case 'olmis':
                $utm_source = 'OLMIS';
                break;
}

if($url=getExternalDrawingLink($_GET['id'], 'pathways'))
        $url = $url;
else
        $url = 'http://' . $_SERVER['SERVER_NAME'] . '/c/published/' . $_GET['id'] . '/view.html';

/*
if($utm_source)
{
        if(strpos('?', $url))
                $url .= '&utm_source=' . $utm_source;
        else
                $url .= '?utm_source=' . $utm_source;
}
*/

header('Location: ' . $url);
