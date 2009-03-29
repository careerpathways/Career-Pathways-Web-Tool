<?php
$topics = array(
	'general',
	'box_menu',
	'connection_menu',
	'simple_widget_menu',
	'chart_menu',
	'grid'
);

if( array_key_exists('topic', $_REQUEST) )
{
	$topic = $_REQUEST['topic'];
}
else 
{
	$topic = 'all';
}

function requireTopic($topic) {
	?>
	<!-- <?= $topic ?> -->
	<div id="<?= $topic ?>">
	<?php
		require('view/drawings/help/topics/' . $topic . '.php');
	?>
	</div>
	<?php
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Help &bull; Pathways</title>
<style type="text/css">@import "/styles.css";</style>
<style type="text/css">
body {
	padding: 1em;
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
<?php
if ($topic === 'all') {
	foreach ($topics as $topic) {
		requireTopic($topic);
	}
}

else {
	requireTopic($topic);
}
?>
</body>
</html>