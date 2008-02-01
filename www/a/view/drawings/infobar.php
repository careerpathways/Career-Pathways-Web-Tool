<?php
global $DB, $drawing_id;
$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=$drawing_id");
$parent = $DB->SingleQuery("SELECT * FROM drawing_main WHERE id=".$drawing['parent_id']);
$num_siblings = $DB->GetValue("COUNT(*)", 'drawings', $drawing['parent_id'], 'parent_id');
?>
<div id="infobar">
	<div id="infobar_header"></div>
	<div id="infobar_content">
		<b><a href="<?= $_SERVER['PHP_SELF'] . '?action=drawing_info&amp;id=' . $parent['id'] ?>" title="Drawing Info"><?= $parent['name'] ?></a></b><br>
		<a href="<?= $_SERVER['PHP_SELF'] . '?action=version_info&amp;version_id=' . $drawing_id ?>" title="Version Info">Version <?= $drawing['version_num'] ?>:
		<?php
		if( $drawing['published'] ) {
			echo 'Published';
		} elseif( $drawing['frozen'] ) {
			echo 'Outdated';
		} else {
			echo 'Draft';
		}?></a><br>
		Total Versions: <?= $num_siblings ?>
	</div>
</div>