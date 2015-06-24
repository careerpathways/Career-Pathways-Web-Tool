<?php
require_once('Asset_Manager.php');

//for now, only images are supported.
$asset = $SITE->asset_path() . Request('asset');

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
