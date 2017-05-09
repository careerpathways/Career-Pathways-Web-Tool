<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
     "http://www.w3.org/TR/2001/REC-xhtml11-20010531/DTD/xhtml11-flat.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <title><?= $drawing_name ?> &#8226; Career Pathways</title>
    <link rel="stylesheet" type="text/css" href="<?= getBaseUrl(); ?>/styles-header.css" />
    <?php if(defined('SITE_TEMPLATE') && file_exists(SITE_TEMPLATE . 'styles-header.css')): ?>
	    <link rel="stylesheet" type="text/css" href="<?= getBaseUrl(); ?>/site-template/styles-header.css" />
	<?php endif; ?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" media="screen">
  </head>
  <body>

	<?php
		require('chart_include.php');
	?>
	<script type="text/javascript">
		<?php if (Request('action') === 'print') { ?>
		Charts.printing = true;
		<?php } ?>
		Charts.draw('chartcontainer');

    // Broadcast a message to the parent window so it knows the height of this document.
    // Allows cross-origin communication, since this document is likely loaded from a different domain than the page with the iFrame.
    if(window.postMessage) {
      // messageId helps the message callback identify this message.
      parent.postMessage({messageId: 'drawingDocumentLoaded', drawingHeight: document.body.scrollHeight}, "*");
    }
	</script>

	<?php
	if(isset($SITE) && method_exists($SITE, 'google_analytics')){
		echo $SITE->google_analytics(l('google analytics drawings'));
	}
	?>

  </body>
</html>
