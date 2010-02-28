<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Copy Version &bull; Pathways</title>
<script type="text/javascript">
	window.parent.location = '/a/<?= $_REQUEST['mode']=='post'?'post_drawings.php':'drawings.php' ?>?action=draw&version_id=<?= $_REQUEST['version_id'] ?>';
</script>
</head>
<body>
<?php if ($_REQUEST['create'] === 'new_version') : ?>
<p>A new version of this drawing has been created.</p>
<?php else : ?>
<p>This version has been copied to a new drawing<?php if ($_REQUEST['copy_to']  === 'user_school') :?> at your organization<?php endif; ?>.</p>
<?php endif; ?>
<p><input type="submit" value="OK" onclick="window.parent.location.href='/a/<?= $_REQUEST['mode']=='post'?'post_drawings.php':'drawings.php' ?>?action=draw&amp;version_id=<?= $_REQUEST['version_id'] ?>';window.close();return false;"/></p>
</body>
</html>