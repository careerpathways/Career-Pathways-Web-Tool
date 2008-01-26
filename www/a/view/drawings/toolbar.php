<?php
global $DB, $drawing_id;
$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=".intval($_REQUEST['drawing_id']));
$drawing_main = $DB->SingleQuery("SELECT * FROM drawing_main WHERE id=".$drawing['parent_id']);
?>
<div id="toolbar">
	<div id="toolbar_header"></div>
	<div id="toolbar_content">
		<?php if ($publishAllowed) : ?><a href="<?= $_SERVER['PHP_SELF'].'?action=version_info&amp;version_id='.$_REQUEST['drawing_id'] ?>" class="publish">publish this version</a><?php endif; ?>
		<?php if( IsAdmin() && $_SESSION['school_id'] != $drawing_main['school_id'] ) { ?>
			<a href="<?= $_SERVER['PHP_SELF'].'?action=copy_version&sameschool&version_id='.$_REQUEST['drawing_id'] ?>" class="publish">copy this version<br><div style="font-size:7pt">new version at this school</div></a>
			<a href="<?= $_SERVER['PHP_SELF'].'?action=copy_version&copytomyschool&version_id='.$_REQUEST['drawing_id'] ?>" class="publish">copy this version<br><div style="font-size:7pt">new drawing at my school</div></a>
		<?php } else { ?>
			<a href="<?= $_SERVER['PHP_SELF'].'?action=copy_version&amp;version_id='.$_REQUEST['drawing_id'] ?>" class="publish">copy this version</a>
		<?php } ?>
	</div>
</div>