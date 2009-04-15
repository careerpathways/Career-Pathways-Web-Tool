<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Help &bull; Career Pathways Roadmap Web Tool</title>
<style type="text/css">@import "/styles.css";</style>
<style type="text/css">
#content {
	padding: 10px;
}
h1 {
	background-color: #444444;
	color: white;
	padding: 2px 10px;
	width: 100%;
}
ul {
	margin-top: 2px;
	padding-left: 20px;
}
dt {
	font-weight: bold;
}

dd {
	margin-bottom: 1em;
}
</style>
</head>
<body>

<h1>Help</h1>

<div id="content">
<?php
chdir("..");
include("inc.php");

if( KeyInRequest('post') ) $id = 2000; else $id = 1000;

$text = $DB->SingleQuery('SELECT * FROM news WHERE category="help" AND sort_index='.$id);
echo $text['text'];
?>
</div>

</body>
</html>