﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>JustBoil's Result Page</title>
<script language="javascript" type="text/javascript">
	//Provides values from uploader.php to Javascripts
	window.parent.window.jbImagesDialog.uploadFinish({
		asset: {
			imgSrc: '<?php echo $asset["file_name"]; ?>',
			id:'<?php echo $asset["id"]; ?>',
			userCanModify: '<?php echo $asset["userCanModify"]; ?>',
			userCanDelete: '<?php echo $asset["userCanDelete"]; ?>'
		},
		result: '<?php echo $result; ?>',
		resultCode: '<?php echo $resultcode; ?>'
	});
</script>
<style type="text/css">
	body {font-family: Courier, "Courier New", monospace; font-size:11px;}
</style>
</head>

<body>

Result: <?php echo $result; ?>

</body>
</html>
