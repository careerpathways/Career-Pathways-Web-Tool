<?php
global $DB, $drawing_id;
$drawing = $DB->SingleQuery("SELECT * FROM post_drawings WHERE id=".intval($_REQUEST['version_id']));
$drawing_main = $DB->SingleQuery("SELECT * FROM post_drawing_main WHERE id=".$drawing['parent_id']);
?>
<div id="toolbar">
	<div id="toolbar_header"></div>
	<div id="toolbar_content">
		<?php if ($publishAllowed) : ?><a href="<?= $_SERVER['PHP_SELF'].'?action=version_info&version_id='.$_REQUEST['version_id'] ?>" class="toolbarButton">publish this version</a><?php endif; ?>
		<a href="javascript:copyPopup('post', <?= $_REQUEST['version_id'] ?>)" class="toolbarButton">copy this version</a>
		<a href="/c/post/<?= $drawing_main['code'] . '/' . $drawing['version_num'] ?>.html?action=print" class="toolbarButton" target="_new">print this version</a>
	</div>
</div>