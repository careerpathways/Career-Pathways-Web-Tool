<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
     "http://www.w3.org/TR/2001/REC-xhtml11-20010531/DTD/xhtml11-flat.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title><?= $drawing_name ?> &#8226; Career Pathways</title>
  </head>
  <body>

	<?php
		require('chart_include.php');
	?>
	<script type="text/javascript">
		<?php if (Request('action') === 'print') { ?>
		Charts.printing = true;
		<?php } ?>
		Charts.draw();
	</script>

	<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
	</script>
	<script type="text/javascript">
	try {
	var pageTracker = _gat._getTracker("<?=l('google analytics drawings')?>");
	pageTracker._trackPageview();
	} catch(err) {}</script>

  </body>
</html>
