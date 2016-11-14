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
    @mkdir('/tmp/apn'); //Don't warn if dir already exists.
    $newPath = '/tmp/apn/'.$f[2];
    move_uploaded_file($_FILES['userfile']['tmp_name'], $newPath);
    $file = $newPath;
}

if (!file_exists($file)) {
    die('Our apologies, there appears to be a problem uploading the file. Please try again or contact your system administrator.');
}
$handle = fopen($file, 'r');



// After user submits, save/update exception list that they've entered.
// Do not run this on the non-dryrun pass, since these values aren't POSTed by
// the form.
if($isDryrun && isset($_POST['exceptions'])){
    echo 'Saving exceptions.';
    // TODO standardize newline char
    // Update the exceptions and exclusions in the DB
    // This function runs Safe() on submitted data.
    $DB->InsertOrUpdate('apn_import', array('field'=>'exceptions', 'value'=>$_POST['exceptions']), 'field');
}

// Query for these, even if we've just saved them during dry-run.
$exceptions = $DB->GetValue('value', 'apn_import', 'exceptions', 'field');

$apn_exceptions = explode("\r\n", $exceptions);
