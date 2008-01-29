<?php
global $DB, $drawing_id;
$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=".intval($_REQUEST['drawing_id']));
$drawing_main = $DB->SingleQuery("SELECT * FROM drawing_main WHERE id=".$drawing['parent_id']);
?>
<div id="toolbar">
	<div id="toolbar_header"></div>
	<div id="toolbar_content">
		<?php if ($publishAllowed) : ?><a href="<?= $_SERVER['PHP_SELF'].'?action=version_info&amp;version_id='.$_REQUEST['drawing_id'] ?>" class="publish">publish this version</a><?php endif; ?>
		<a href="copy_popup.php?version_id=<?=  $_REQUEST['drawing_id'] ?>" class="publish" onclick="return showCopy(this);">copy this version</a>
	</div>
</div>
<script type="text/javascript">
var showCopy = function(anchor) {
	window.open(anchor.href, 'Copy', 'menubar=no,scrollbars=yes,width=300,height=300,screenX=100,screenY=100');
	return false;
	
};
</script>