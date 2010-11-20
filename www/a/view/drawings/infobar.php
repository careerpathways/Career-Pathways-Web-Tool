<?php
global $DB, $drawing_id;
$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=$drawing_id");
if($drawing):

$parent = $DB->SingleQuery("SELECT m.*, IF(m.name='', programs.title, m.name) AS name
	FROM drawing_main AS m 
	LEFT JOIN programs ON m.program_id=programs.id
	WHERE m.id=".$drawing['parent_id']);
$siblings = $DB->SingleQuery("SELECT COUNT(*) AS num FROM drawings WHERE deleted=0 AND parent_id=".$drawing['parent_id']);
?>
<div id="infobar">
	<div id="infobar_header"></div>
	<div id="infobar_content">
		<a href="<?= $_SERVER['PHP_SELF'] . '?action=drawing_info&amp;id=' . $parent['id'] ?>" title="Drawing Properties">
			<img src="/common/silk/cog.png" width="16" height="16" style="float: left; margin-right: 4px;" />
		</a>
		<b><?= $parent['name'] ?></b><br /><br />

		<a href="<?= $_SERVER['PHP_SELF'] . '?action=version_info&amp;version_id=' . Request('version_id') ?>" style="float:left; margin-right: 4px;" title="Version Settings"><?=SilkIcon('wrench.png')?></a>

		Version <?= $drawing['version_num'] ?>:
		<?php
		if( $drawing['published'] ) {
			echo 'Published';
		} elseif( $drawing['frozen'] ) {
			echo 'Locked';
		} else {
			echo 'Draft';
		}?><br />
		<br />
		Total Versions: <?= $siblings['num'] ?>
	</div>
</div>
<?php 
endif;
?>