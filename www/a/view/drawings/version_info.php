<?php

switch( $MODE ) {
case 'pathways':
	$main_table = 'drawing_main';
	$drawings_table = 'drawings';
	$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/version/%%/##.html';
	$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/version/%%/##.xml';
	$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/text/%%/##.html';
	break;
case 'ccti':
	$main_table = 'ccti_drawing_main';
	$drawings_table = 'ccti_drawings';
	$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/%%/##.html';
	$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/%%/##.xml';
	$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/text/%%/##.html';
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

$siblings = $DB->SingleQuery("SELECT COUNT(*) AS num FROM drawings WHERE parent_id=".$drawing_main['id']);

?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="version_form" name="version_form">

<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

<p>
<table>
<tr>
	<th width="70">Drawing</th>
	<td><span class="drawing_title"><?= $drawing_main['name'] ?></span>
		<a href="<?= $_SERVER['PHP_SELF'].'?action=drawing_info&id='.$drawing_main['id'] ?>"><img src="/common/silk/cog.png" height="16" width="16" /></a></td>
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
	<th>Preview</th>
	<td>
		<a href="javascript:preview_drawing(<?= "'".$drawing_main['code']."', ".$drawing['version_num'] ?>)">Preview Drawing</a>
	</td>
</tr>
<tr>
	<th>Note</th>
	<td>
		<div id="note_fixed"><span id="note_value"><?= $drawing['note'] ?></span> <a href="javascript:showNoteChange()" class="tiny">edit</a></div>
		<div id="note_edit" style="display:none">
			<input type="text" id="version_note" name="name" size="60" value="<?= $drawing['note'] ?>">
			<input type="button" class="submit tiny" value="Save" id="submitButton" onclick="savenote()">
		</div>
	</td>
</tr>
<?php if( $drawing['published'] ) {
	?>
	<tr>
	<th>Embed Code</th>
	<td><textarea style="width:560px;height:50px;" class="code"><?= htmlspecialchars($embed_code) ?></textarea></td>
	</tr>
<?php } ?>
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
		(
			IsAdmin()
			|| (IsSchoolAdmin() && $_SESSION['school_id'] == $drawing_main['school_id'] )
			|| $drawing['created_by'] == $_SESSION['user_id']
			|| $drawing_main['created_by'] == $_SESSION['user_id']
	    )
	) { ?>
<tr>
	<th>Delete</th>
	<td width="545">
		<b>There is no way to recover deleted drawings!</b><br>
		If you are sure you want to delete this version, click the link below:<br>
		<p><a href="javascript:deleteConfirm()">Delete this version</a></p>
		<div id="deleteConfirm"></div>
	</td>
</tr>
<?php
		$can_delete = true;
	} else {
		$can_delete = false;
	}
?>
</table>

<?php
if( $version_id != "" && $drawing['published'] == 0 && (IsAdmin() || $_SESSION['school_id'] == $drawing_main['school_id']) ) {
	?>
	<p><input type="button" name="publish" class="publish_link" onclick="publishVersion()" value="Publish this version"></p>
	<?php
}
if( $drawing['published'] ) {
	echo '<div class="publish_link_inactive" style="width:100px;text-align:center">Published</div>';
}
?>

<input type="hidden" name="action" id="action_field" value="">
<input type="hidden" name="drawing_id" value="<?= $drawing['id'] ?>">
</form>

<script type="text/javascript" src="/files/greybox.js"></script>
<script type="text/javascript">

function publishVersion() {
	getLayer('action_field').value = "publish";
	getLayer('version_form').submit();
}

function savenote() {
	var note = getLayer('version_note');
	ajaxCallback(cbNoteChanged, '/a/drawings_post.php?mode=<?= $MODE ?>&drawing_id=<?= $version_id ?>&note='+note.value);
}

function cbNoteChanged() {
	getLayer('note_value').innerHTML = getLayer('version_note').value;
	getLayer('note_edit').style.display = 'none';
	getLayer('note_fixed').style.display = 'block';
}

function showNoteChange() {
	getLayer('note_edit').style.display = 'block';
	getLayer('note_fixed').style.display = 'none';
}

function preview_drawing(code,version) {
	chGreybox.create('<div id="dpcontainer"><iframe src="/c/<?= $MODE=='pathways'?'version':'post' ?>/'+code+'/'+version+'.html"></iframe></div>',800,600);
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