<?php
chdir("..");
include("inc.php");

ModuleInit('drawings');


// verify a drawing name is not already taken

// $_REQUEST['school_id'] (optional for admins, from session if omitted)
// $_REQUEST['title']
// $_REQUEST['id']      // if modifying an existing drawing, the id of the current one

$school_id = intval((IsAdmin() && Request('school_id'))?Request('school_id'):$_SESSION['school_id']);
$title = $DB->Safe(Request('title'));
$id = intval(Request('id')?Request('id'):0);


$check = $DB->SingleQuery("SELECT * FROM drawing_main WHERE name='".$title."' AND school_id=".$school_id." AND id!=".$id);
if( is_array($check) ) {
	echo 0;
} else {
	echo 1;
}


?>