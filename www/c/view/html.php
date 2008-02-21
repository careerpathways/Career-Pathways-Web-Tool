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
			<?php if (Request('action') === 'print') : ?>
			document.observe('chart:drawn', function() {
				window.print();
			});
			
			Charts.printing = true;
			<?php endif; ?>
			Charts.draw();
		</script>
  </body>
</html>