<?php
/**
 * Return an asset (the actual image file).
 */
require_once('Asset_Manager.php');

//for now, only images are supported.
//$asset = $SITE->asset_path() . Request('asset');
$asset = Asset_Manager::get_asset_by_filename(Request('asset'));
if(!$asset){
    header('HTTP/1.0 404 Not Found');
    echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head>
        <title>404 Not Found</title>
        </head><body>
        <h1>Not Found</h1><p>The asset ' . Request('asset') . ' could not be found.</p><hr>
        </body></html>';
    die();
}

//Set the content-type header as appropriate
$imageInfo = getimagesize($asset);
switch ($imageInfo[2]) {
    case IMAGETYPE_JPEG:
        header("Content-Type: image/jpg");
        break;
    case IMAGETYPE_GIF:
        header("Content-Type: image/gif");
        break;
    case IMAGETYPE_PNG:
        header("Content-Type: image/png");
        break;
    default:
        break;
}

// Set the content-length header
header('Content-Length: ' . filesize($asset));

// Write the image bytes to the client
readfile($asset);
