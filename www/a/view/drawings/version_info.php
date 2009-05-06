<?php

switch( $MODE ) {
case 'pathways':
	$main_table = 'drawing_main';
	$drawings_table = 'drawings';
	$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/version/%%/##.html';
	$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/version/%%/##.xml';
	$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/text/%%/##.html';
	break;
}

$drawing = GetDrawingInfo($version_id, $MODE);
$drawing_main = $DB->LoadRecord($main_table, $drawing['parent_id']);

switch( $MODE ) {
case 'pathways':
	$embed_code = '<iframe width="800" height="600" src="http://'.$_SERVER['SERVER_NAME'].'/c/published/'.$drawing_main['code'].'.html" frameborder="0" scrolling="no"></iframe>';
	break;
case 'ccti':
	$embed_code = '<iframe width="800" height="600" src="http://'.$_SERVER['SERVER_NAME'].'/c/post/'.$drawing_main['code'].'.html" frameborder="0" scrolling="no"></iframe>';
	break;
}

$created = ($drawing['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$drawing['created_by']));
$modified = ($drawing['last_modified_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$drawing['last_modified_by']));

$school_name = $DB->GetValue('school_name', 'schools', $drawing_main['school_id']);

$siblings = $DB->SingleQuery("SELECT COUNT(*) AS num FROM drawings WHERE deleted=0 AND parent_id=".$drawing_main['id']);

?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="version_form" name="version_form">

<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

<p>
<table>
<tr>
	<th width="80">Occupation/Program</th>
	<td><span class="drawing_title"><?= $drawing_main['name'] ?></span>
		<a href="<?= $_SERVER['PHP_SELF'].'?action=drawing_info&id='.$drawing_main['id'] ?>" title="Drawing Properties"><img src="/common/silk/cog.png" height="16" width="16" /></a></td>
</tr>
<tr>
	<th>Version</th>
	<td><div class="version_title"><?= $drawing['version_num'].($drawing['published']==1?" (Published)":"") ?></div></td>
</tr>
<tr>
	<th>Organization</th>
	<td><b><?= $school_name ?></b></td>
</tr>
<tr>
	<th>Note</th>
	<td>
		<?php if( IsStaff() ) { ?>
			<div id="note_edit">
				<input type="text" id="version_note" name="name" size="60" value="<?= $drawing['note'] ?>">
				<input type="button" class="submit tiny" value="Save" id="noteButton" onclick="savenote()">
				<input type="button" class="submit tiny" value="Clear" id="noteClearButton" onclick="deletenote()" />
			</div>
		<?php } else { ?>
			<?= $drawing['note'] ?>
		<?php } ?>
	</td>
</tr>
<tr>
	<th>Created</th>
	<td><?php
		echo ($drawing['date_created']==''?'':$DB->Date("m/d/Y g:ia",$drawing['date_created'])).' <a href="/a/users.php?id='.$drawing['created_by'].'">'.$created['name'].'</a>';
	?></td>
</tr>
<tr>
	<th>Modified</th>
	<td><?php
		echo ($drawing['last_modified']==''?'':$DB->Date("m/d/Y g:ia",$drawing['last_modified'])).' <a href="/a/users.php?id='.$drawing['last_modified_by'].'">'.$modified['name'].'</a>';
	?></td>
</tr>
<tr>
	<th>Actions</th>
	<td>
		<a href="/a/drawings.php?action=draw&version_id=<?= $drawing['id'] ?>" title="<?=CanEditVersion($drawing['id'],'pathways') ? 'Draw/Edit Version' : 'View Version'?>"><?= CanEditVersion($drawing['id'],'pathways') ? SilkIcon('pencil.png') : SilkIcon('picture.png') ?></a> &nbsp;
		<a href="javascript:preview_drawing(<?= "'".$drawing_main['code']."', ".$drawing['version_num'] ?>)" title="Preview Version"><?=SilkIcon('magnifier.png')?></a> &nbsp;
		<?php if( IsStaff() ) { ?>
			<a href="javascript:copyPopup('pathways', '<?=$drawing['id']?>')" class="toolbarButton" title="Copy Version"><?= SilkIcon('page_copy.png') ?></a>
		<?php } ?>
	</td>
</tr>
<?php
	if( IsStaff() ) {
?>
<tr>
	<th style="vertical-align:bottom">Editable</th>
	<td>
		<a href="javascript:lock_drawing(<?= $drawing['version_num'] ?>)" title="Lock Version"><img src="/common/silk/lock<?= ($drawing['frozen']?'':'_open') ?>.png" width="16" height="16" id="lock_icon" /></a>
		<div id="drawing_unlocked_msg" style="display: <?= $drawing['frozen']?'none':'inline' ?>">
			<span style="color:#999999">This version is currently editable. Click the lock icon to prevent further edits.</span>
		</div>
		<div id="drawing_locked_msg" style="display:<?= $drawing['frozen']?'inline':'none' ?>">
			<span style="color:#999999">This version is locked. Copy it to a new version to make changes.</span>
		</div>
	</td>
</tr>
<?php
	if( $drawing['published'] ) {
	?>
	<tr>
	<th>Embed Code</th>
	<td><textarea style="width:560px;height:50px;" class="code"><?= htmlspecialchars($embed_code) ?></textarea></td>
	</tr>
<?php
	}
?>
<tr>
	<th valign="top">Link</th>
	<td><?php $url = str_replace(array('%%','##'), array($drawing_main['code'], $drawing['version_num']), $published_link); ?>
	<input type="text" style="width:560px" value="<?= $url ?>" onclick="this.select()" />
	</td>
</tr>
<tr>
	<th valign="top">XML</th>
	<td><?php $url = str_replace(array('%%','##'), array($drawing_main['code'], $drawing['version_num']), $xml_link); ?>
	<input type="text" style="width:560px" value="<?= $url ?>" onclick="this.select()" />
	</td>
</tr>
<tr>
	<th valign="top">Accessible</th>
	<td><?php $url = str_replace(array('%%','##'), array($drawing_main['code'], $drawing['version_num']), $accessible_link); ?>
		<input type="text" style="width:560px" value="<?= $url ?>" onclick="this.select()" />
		<br>
		These are permanent links to <b>this version</b> of the drawing. You can give this link to people to share your in-progress drawing easily.<br>
		<br>
	</td>
</tr>
<tr>
	<th>Delete</th>
	<td width="545">
<?php
	/* who can delete versions?
		1. admins
		2. school admins & webmasters at the same school
		3. the owner of the version
		4. the owner of the drawing
	   no version may be deleted if it's "published"
	   a version may not be deleted if it's the only one for that drawing
	*/
	if( $version_id != "" &&
		$drawing['published'] == 0 &&
		$siblings['num'] > 1 &&
		CanDeleteDrawing($drawing_main['id'], 'pathways')
	) {
?>
		<b>There is no way to recover deleted drawings!</b><br>
		If you are sure you want to delete this version, click the link below:<br>
		<p><a href="javascript:deleteConfirm()" class="noline"><?= SilkIcon('cross.png') ?> Delete this version</a></p>
		<div id="deleteConfirm"></div>
<?php
		$can_delete = true;
	} else {
		if( $siblings['num'] == 1 ) {
			echo '<p>You can\'t delete this version because the drawing has no other versions. If you want to delete the entire drawing, click the "Drawing Properties" link above.</p>';
		} elseif( $drawing['published'] ) {
			echo '<p>This version is currently published. It cannot be deleted.</p>';
		} else {
			echo '<p>You can\'t delete this version</p>';
		}
		$can_delete = false;
	}
?>
	</td>
</tr>
<?php
	} // if IsStaff
	else
	{
		$can_delete = false;
	}
?>
</table>

<?php
/*
// replaced by publish link in sidebar
if( $version_id != "" && $drawing['published'] == 0 && (IsAdmin() || $_SESSION['school_id'] == $drawing_main['school_id']) ) {
	?>
	<p><input type="button" name="publish" class="publish_link" onclick="publishVersion()" value="Publish this version"></p>
	<?php
}
if( $drawing['published'] ) {
	echo '<div class="publish_link_inactive" style="width:100px;text-align:center">Published</div>';
}
*/
?>

<input type="hidden" name="action" id="action_field" value="">
<input type="hidden" name="drawing_id" value="<?= $drawing['id'] ?>">
</form>

<script type="text/javascript" src="/files/greybox.js"></script>
<script type="text/javascript" src="/common/URLfunctions1.js"></script>
<script type="text/javascript" src="/common/jquery-1.3.min.js"></script>
<script type="text/javascript">

var $j = jQuery.noConflict();

function savenote() {
	var note = getLayer('version_note');
	ajaxCallback(cbNoteChanged, '/a/drawings_post.php?mode=<?= $MODE ?>&drawing_id=<?= $version_id ?>&note='+URLEncode(note.value));
}

function cbNoteChanged() {
	var btn = getLayer('noteButton');
	btn.value = 'Saved!';
	btn.style.backgroundColor = '#393';
	setTimeout(function(){
		getLayer('noteButton').value = "Save";
		getLayer('noteButton').style.backgroundColor = '';
	}, 500);
}

function deletenote() {
	$j('#version_note').val('');
	ajaxCallback(function() {
		var btn = getLayer('noteClearButton');
		btn.value = 'Cleared!';
		btn.style.backgroundColor = '#393';
		setTimeout(function(){
			getLayer('noteClearButton').value = "Clear";
			getLayer('noteClearButton').style.backgroundColor = '';
		}, 500);
	}, '/a/drawings_post.php?mode=<?= $MODE ?>&drawing_id=<?= $version_id ?>&note=');
}

function preview_drawing(code,version) {
	chGreybox.create('<div id="dpcontainer"><iframe src="/c/<?= $MODE=='pathways'?'version':'post' ?>/'+code+'/'+version+'.html"></iframe></div>',800,600,null,'Preview');
}

function lock_drawing(version) {
	ajaxCallback(function() {
			getLayer('lock_icon').src = '/common/silk/lock.png';
			getLayer('drawing_locked_msg').style.display = 'inline';
			getLayer('drawing_unlocked_msg').style.display = 'none';
		}, '/a/drawings_post.php?mode=<?= $MODE ?>&action=lock&drawing_id=<?= $version_id ?>')
}

<?php if( $can_delete ) { ?>
function deleteConfirm() {
	getLayer('deleteConfirm').innerHTML = 'Are you sure? <a href="javascript:doDelete()">Yes</a>';
}
function doDelete() {
	getLayer('action_field').value = 'delete';
	getLayer('version_form').submit();
}
<?php } ?>

</script>