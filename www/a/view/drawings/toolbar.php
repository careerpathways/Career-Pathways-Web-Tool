<script type="text/javascript" src="/c/drawings.js"></script>
<?php
global $DB, $drawing_id;
$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=".intval($_REQUEST['drawing_id']));
$drawing_main = $DB->SingleQuery("SELECT * FROM drawing_main WHERE id=".$drawing['parent_id']);
?>
<div id="toolbar">
	<div id="toolbar_header"></div>
	<div id="toolbar_content">
		<?php if ($publishAllowed) : ?><a href="<?= $_SERVER['PHP_SELF'].'?action=version_info&amp;version_id='.$_REQUEST['drawing_id'] ?>" class="toolbarButton">publish this version</a><?php endif; ?>
		<a href="javascript:copyPopup('pathways', '<?=  $_REQUEST['drawing_id'] ?>')" class="toolbarButton">copy this version</a>
		<a href="/c/version/<?= $drawing_main['code'] . '/' . $drawing['version_num'] ?>.html?action=print" target="_new" class="toolbarButton">print this version</a>
	</div>
</div>