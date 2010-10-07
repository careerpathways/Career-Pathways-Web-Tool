<?php
header('Content-type: text/javascript');
include('inc.php');
include('CourseDescription.php');

$c = new CourseDescription(request('school_id'), request('subject'), request('number'));
$description = $c->get();
echo json_encode($description);

?>