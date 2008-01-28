<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Copy Version &bull; Pathways</title>
<script type="text/javascript" src="/files/prototype.js"></script>
<style type="text/css">@import "/styles.css";</style>
<style type="text/css">
body {
	padding: 1em;
}
</style>
</head>
<body>
<h1>Copy Version</h1>
<p>The version has been copied</p>
<p><input type="submit" value="OK" onclick="opener.location.href='/a/drawings.php?action=draw&amp;version_id=<?= $_REQUEST['version_id'] ?>';window.close();return false;"/></p>
</body>
</html>