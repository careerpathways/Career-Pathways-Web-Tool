<?php
chdir("..");
include("inc.php");
$version = GetDrawingInfo($_REQUEST['version_id']);
?>
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

#drawing_name {
	width: 100%;
}

fieldset {
	margin: 1em 0;
}
</style>
</head>
<body>
<h1>Copy Version</h1>
<h2><?= $version['name'] ?></h2>

<form action="drawings.php" method="post">
<input type="hidden" name="from_popup" value="true"/>
<input type="hidden" name="action" value="copy_version"/>
<input type="hidden" name="version_id" value="<?= $version['id'] ?>"/>

<?php if (IsAdmin() && $_SESSION['school_id'] !== $version['school_id']) : ?>
<fieldset id="copy_to">
<legend>Copy To</legend>
<input type="radio" name="copy_to" value="user_school" id="copy_to_user_school" checked="true"/> <label for="copy_to_user_school">Your School</label><br/>
<input type="radio" name="copy_to" value="same_school" id="copy_to_same_school"/> <label for="copy_to_same_school">This School</label><br/>
</fieldset>
<?php endif;

if (IsAdmin() || $_SESSION['school_id'] === $version['school_id']) : ?>
<fieldset id="create">
<legend>Create</legend>
<input type="radio" name="create" value="new_version" id="create_new_version" checked="true"/> <label for="create_new_version">New Version</label><br/>
<input type="radio" name="create" value="new_drawing" id="create_new_drawing"/> <label for="create_new_drawing">New Drawing</label><br/>
</fieldset>
<?php else : ?>
<p>A new drawing will be created in your school.</p>
<?php endif; ?>

<fieldset id="drawingName">
<legend><label for="drawing_title">New Drawing Name</label></legend>
<input type="text" name="drawing_name" id="drawing_name" value="<?= $version['name'] ?> Copy">
</fieldset>

<p><input type="submit" value="OK" id="ok"/> <input type="reset" id="cancel" value="Cancel"/>
</form>
<script type="text/javascript">
var create = $('create');
var drawingName = $('drawingName');
var createNewDrawing = $('create_new_drawing');
var copyToUserSchool = $('copy_to_user_school');
var updateState = function() {
	if (copyToUserSchool && copyToUserSchool.checked) {
		createNewDrawing.checked = true;
		drawingName.show();
		create.hide();
	}
	else {
		if (create) {
			create.show();
		}
		if (!createNewDrawing || createNewDrawing.checked) {
			drawingName.show();
		}
		else {
			drawingName.hide();
		}
	}
};
$$('#create input').invoke('observe', 'change', updateState);

$$('#copy_to input').invoke('observe', 'change', updateState);

updateState(); 

$('cancel').observe('click', function() {
	window.close();
});
</script>
</body>
</html>