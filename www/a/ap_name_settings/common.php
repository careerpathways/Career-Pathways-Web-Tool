<?php
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'APN_Tools.php');
ini_set('auto_detect_line_endings', true);

if(isset($_GET['dryrun'])){
    $isDryrun = filter_var($_GET['dryrun'], FILTER_VALIDATE_BOOLEAN);
} else {
    $isDryrun = false;
}

if(!$isDryrun && $_GET['file']){
    // User verified and wants to proceed (save to db).
    // Refer to file that has already been uploaded and copied to another spot.
    $file = urldecode($_GET['file']);
} else {
    // User uploaded, process a dryrun.
    // Copy tmp file to another spot, so PHP doesn't delete it after this dryrun.
    $f = explode('/', $_FILES['userfile']['tmp_name']);
    mkdir('/tmp/apn');
    $newPath = '/tmp/apn/'.$f[2];
    move_uploaded_file($_FILES['userfile']['tmp_name'], $newPath);
    $file = $newPath;
}

if (!file_exists($file)) {
    die('Our apologies, there appears to be a problem uploading the file. Please try again or contact your system administrator.');
}
$handle = fopen($file, 'r');
