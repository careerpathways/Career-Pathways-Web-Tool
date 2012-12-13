<?php
if($_REQUEST['version_id']):
global $DB;
$drawing = $DB->SingleQuery("SELECT * FROM post_drawings WHERE id=".intval($_REQUEST['version_id']));
if($drawing):
$drawing_main = $DB->SingleQuery("SELECT * FROM post_drawing_main WHERE id=".$drawing['parent_id']);
?>
<div id="toolbar">
	<div id="toolbar_header"></div>
	<div id="toolbar_content">
		<?php if( CanEditVersion($drawing['id'], 'post', false) ) { ?>
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
			<?php if (CanEditVersion($drawing['id'], 'post') && $drawing['published'] == 0) { ?>
				<a href="javascript:configurePopup(<?= $_REQUEST['version_id'] ?>)" class="noline"><?= SilkIcon('table.png') ?> configure rows & cols</a><br />
			<?php } ?>
			<?php // HS drawings can always be copied, but CC drawings can only be copied by staff
			if( $drawing_main['type'] == 'HS' || IsStaff() ) { ?>
			<a href="javascript:copyPopup('post', <?= $_REQUEST['version_id'] ?>)" class="noline"><?= SilkIcon('page_copy.png') ?> copy this version</a><br />
			<?php } ?>
			<a href="/c/post/<?= $drawing_main['id'] . '/' . $drawing['id'] ?>.html?action=print" class="noline" target="_new"><?= SilkIcon('printer.png') ?> print this version</a><br />
			<?php if (CanEditVersion($drawing['id'], 'post', false)) : ?>
				<form action="/a/post_drawings.php" method="post" id="publishForm">
					<input type="hidden" name="drawing_id" value="<?=$drawing['id']?>" />
					<input type="hidden" name="action" value="publish" />
					<input type="submit" value="Publish" style="display:none" />
				</form>
				<a href="javascript:publishPopup('post', <?=$_REQUEST['version_id']?>)" id="publishLink" class="noline"><?= SilkIcon('report_go.png') ?> <?=$drawing['published'] == 0?'':'un'?>publish this version</a>
			<?php endif; ?>
		</div>

		<script type="text/javascript">
			function lock_drawing(version) {
				ajaxCallback(function() {
						getLayer('lock_icon').src = '/common/silk/lock.png';
						getLayer('drawing_locked_msg').style.display = 'inline';
						getLayer('drawing_unlocked_msg').style.display = 'none';
						window.location = window.location;
					}, '/a/drawings_post.php?mode=post&action=lock&drawing_id=<?= $drawing['id'] ?>');
			}
			
			function configurePopup(version_id) {
				jQuery.get("/a/post_drawings.php",
					{action: "configure_rowscols", id: version_id},
					function(data){
						chGreybox.create(data, 600, 450);
						chGreybox.onClose = function() {
							window.location = window.location;
						};
				}, "html");
			}
		</script>
	</div>
</div>
<?php
endif;
endif;
?>