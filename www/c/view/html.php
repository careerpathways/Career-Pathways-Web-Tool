<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
     "http://www.w3.org/TR/2001/REC-xhtml11-20010531/DTD/xhtml11-flat.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title><?= $drawing_name ?> &#8226; Career Pathways</title>
  </head>
  <body>

	<?php
	if( KeyInRequest('d') ) {
	?>
		<script type="text/javascript" src="view.js?d=<?= Request('d') ?>"></script>
	<?php
	} else {
		require('chart_include.php');
	?>
		<script type="text/javascript">Charts.draw();</script>
	<?php
	}
	?>
  </body>
</html>