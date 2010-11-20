<script type="text/javascript" src="/c/drawings.js"></script>
<?php
global $DB, $drawing_id;
$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=".intval($_REQUEST['drawing_id']));
if($drawing):

$drawing_main = $DB->SingleQuery("SELECT * FROM drawing_main WHERE id=".$drawing['parent_id']);
?>
<div id="toolbar">
	<div id="toolbar_header"></div>
	<div id="toolbar_content">
		<?php if( CanEditVersion($drawing_id, 'pathways', false) ) { ?>
		<div style="margin-bottom:4px">
			<a href="javascript:lock_drawing(<?= $drawing['version_num'] ?>)" title="Lock Version"><img src="/common/silk/lock<?= ($drawing['frozen']?'':'_open') ?>.png" width="16" height="16" id="lock_icon" /></a>
			<div id="drawing_unlocked_msg" style="display: <?= $drawing['frozen']?'none':'inline' ?>">
				<span style="color:#555555">This version is currently editable. Click the lock icon to prevent further edits.</span>
			</div>
			<div id="drawing_locked_msg" style="display:<?= $drawing['frozen']?'inline':'none' ?>">
				<span style="color:#555555">This version is locked. Copy it to a new version to make changes.</span>
			</div>
		</div>
		<?php } ?>

		<div style="margin-bottom:10px">
			
			<?php if( IsStaff() ) { ?><a href="javascript:copyPopup('pathways', '<?=  $_REQUEST['drawing_id'] ?>')" class="noline"><?= SilkIcon('page_copy.png') ?> copy this version</a><br /><?php } ?>

			<a href="/c/version/<?= $drawing_main['id'] . '/' . $drawing['id'] ?>.html?action=print" target="_new" class="noline"><?= SilkIcon('printer.png') ?> print this version</a>
			<?php if (CanEditVersion($drawing_id, 'pathways', false)) : ?>
				<form action="/a/drawings.php" method="post" id="publishForm">
					<input type="hidden" name="drawing_id" value="<?=$drawing['id']?>" />
					<input type="hidden" name="action" value="publish" />
					<input type="submit" value="Publish" style="display:none" />
				</form>
				<a href="javascript:publishPopup('pathways', <?=$_REQUEST['drawing_id']?>)" id="publishLink" class="noline"><?= SilkIcon('report_go.png') ?> <?=$drawing['published'] == 0?'':'un'?>publish this version</a>
			<?php endif; ?>
		</div>

		<script type="text/javascript">
			function lock_drawing(version) {
				ajaxCallback(function() {
						getLayer('lock_icon').src = '/common/silk/lock.png';
						getLayer('drawing_locked_msg').style.display = 'inline';
						getLayer('drawing_unlocked_msg').style.display = 'none';
						window.location = window.location;
					}, '/a/drawings_post.php?mode=pathways&action=lock&drawing_id=<?= $drawing['id'] ?>');
			}
		</script>
	</div>
</div>
<?php 
endif;
?>