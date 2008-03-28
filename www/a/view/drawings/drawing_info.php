<?php

/*
	TODO: parse access_log looking for referers linking to each drawing.
		grep "GET /c/published/" access_log
		build a list of web pages that link to a drawing, show here
*/

$drawing = $DB->LoadRecord('drawing_main',$id);

$schools = $DB->VerticalQuery("SELECT * FROM schools ORDER BY school_name",'school_name','id');
if( IsAdmin() ) {
	if( $id != "" ) {
		$school_id = $drawing['school_id'];
	} else {
		$school_id = $_SESSION['school_id'];
	}
} else {
	$school_id = $_SESSION['school_id'];
}

$embed_code = '<iframe width="800" height="600" src="http://'.$_SERVER['SERVER_NAME'].'/c/published/%%"></iframe>';

if( $id != "" ) {
	$published = $DB->SingleQuery("SELECT * FROM drawings WHERE published=1 AND parent_id=".$drawing['id']);
}

?>
<script type="text/javascript" src="/files/greybox.js"></script>
<script type="text/javascript" src="/files/drawing_list.js"></script>
<script type="text/javascript" src="/c/drawings.js"></script>

<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

<p>
<?php if( $id == "" ) { ?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="drawing_form">
<table>
<tr>
	<th>Title</th>
	<td>
		<input type="text" id="drawing_title" name="name" size="80" value="<?= $drawing['name'] ?>" onblur="checkName(this)">
		<span id="checkNameResponse" class="error"></span>
	</td>
</tr>
<tr>
	<th width="80">School</th>
	<td>
	<?php
	if( IsAdmin() ) {
		echo GenerateSelectBox($schools,'school_id',$school_id);
	} else {
		echo '<b>'.$schools[$school_id].'</b>';
	}
	?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="button" class="submit" value="Create" id="submitButton" onclick="submitform()"></td>
</tr>
</table>
<input type="hidden" name="id" value="">
</form>
<?php } else { ?>
<table>
<tr>
	<th>Title</th>
	<td>
		<div id="title_fixed"><span id="title_value"><?= $drawing['name'] ?></span> <a href="javascript:showTitleChange()" class="tiny">edit</a></div>
		<div id="title_edit" style="display:none">
			<input type="text" id="drawing_title" name="name" size="80" value="<?= $drawing['name'] ?>" onblur="checkName(this)">
			<input type="button" class="submit tiny" value="Save" id="submitButton" onclick="savetitle()">
			<span id="checkNameResponse" class="error"></span>
		<div class="tiny error">Warning: changing the drawing title will break any external web pages that link to this drawing.</div>
		</div>
	</td>
</tr>
<tr>
	<th width="80">School</th>
	<td><b><?= $schools[$school_id] ?></b></td>
</tr>
<tr>
	<th>Preview</th>
	<td>
	<?php
		if( is_array($published) ) {
			echo '<a href="javascript:preview_drawing(drawing_code,'.$published['version_num'].')">Preview Published Drawing</a>';
		} else {
			echo 'No versions have been published yet.';
		}
	?>
	</td>
</tr>
<tr>
	<th>Embed Code</th>
	<td>
		<textarea style="width:560px;height:40px;" class="code" id="embed_code"><?= htmlspecialchars(str_replace('%%',$drawing['code'],$embed_code)) ?></textarea>
	</td>
</tr>
<tr>
	<th>Link</th>
	<td>This link, as well as the embed code above, will always link to the published version of this drawing.<br>
		<div id="drawing_link"><?php
		$url = "http://".$_SERVER['SERVER_NAME']."/c/published/".$drawing['code'];
		echo '<a href="'.$url.'">'.$url.'</a>';
		?></div><br>
	</td>
</tr>
<?php
	require('version_list.php');
	/*
	who can delete drawings?
		1. admins
		2. school admins & webmasters at the same school
		3. the owner of the drawing
	*/
	if(
		IsAdmin()
		|| (IsSchoolAdmin() && $_SESSION['school_id'] == $drawing['school_id'] )
		|| $drawing['created_by'] == $_SESSION['user_id']
	) { ?>
<tr>
	<th>Delete</th>
	<td width="545">
		Deleting this drawing will remove all versions. Please be careful. Deleting this drawing will break any links from external web pages to this drawing.
		<p><b>There is no way to recover deleted drawings!</b></p>
		<p>If you are sure you want to delete the entire drawing, click the link below:</p>
		<p><a href="javascript:deleteConfirm()">Delete drawing and all versions</a></p>
		<div id="deleteConfirm"></div>
		<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="delete_form">
			<input type="hidden" name="id" value="<?= $drawing['id'] ?>">
			<input type="hidden" name="delete" value="delete">
		</form>
	</td>
</tr>
<?php } ?>
</table>
<?php } ?>
</p>

<script type="text/javascript" src="/common/URLfunctions1.js"></script>
<script type="text/javascript">

drawing_code = '<?= $drawing['code'] ?>';
schools = new Array(<?= count($schools) ?>);
<?php
$i=0;
foreach( $schools as $sid=>$school ) {
	echo 'schools['.$i.'] = '.$sid.";\n";
	$i++;
}
?>

function checkName(title) {
	ajaxCallback(verifyName, '/a/drawings_checkname.php?id=<?= $drawing['id'] ?>&title='+URLEncode(title.value)<?= (IsAdmin()?"+'&school_id=".$school_id."'":'') ?>);
}

function verifyName(result) {
	if( result == 0 ) {
		getLayer('checkNameResponse').innerHTML = 'There is already a drawing by that name. Choose a different name.';
	} else {
		getLayer('checkNameResponse').innerHTML = '';
	}
}

function savetitle() {
	var title = getLayer('drawing_title');
	ajaxCallback(verifyNameSubmit, '/a/drawings_checkname.php?id=<?= $drawing['id'] ?>&title='+URLEncode(title.value)<?= (IsAdmin()?"+'&school_id=".$school_id."'":'') ?>);
}

function submitform() {
	var title = getLayer('drawing_title');
	ajaxCallback(verifyNameSubmitNew, '/a/drawings_checkname.php?id=<?= $drawing['id'] ?>&title='+URLEncode(title.value)<?= (IsAdmin()?"+'&school_id=".$school_id."'":'') ?>);
}

function verifyNameSubmitNew(result) {
	if( result == 0 ) {
		verifyName(0);
	} else {
		getLayer('drawing_form').submit();
	}
}

function verifyNameSubmit(result) {
	if( result == 0 ) {
		verifyName(0);
	} else {
		var title = getLayer('drawing_title');
		ajaxCallback(cbNameChanged, '/a/drawings_post.php?id=<?= $drawing['id'] ?>&title='+URLEncode(title.value));
	}
}

function cbNameChanged(drawingCode) {
	drawing_code = drawingCode;
	getLayer('title_value').innerHTML = getLayer('drawing_title').value;
	getLayer('drawing_link').innerHTML = '<a href="http://<?= $_SERVER['SERVER_NAME'] ?>/c/published/'+drawingCode+'">http://<?= $_SERVER['SERVER_NAME'] ?>/c/published/'+drawingCode+'</a>';
	getLayer('embed_code').value = '<?= $embed_code ?>';
	getLayer('embed_code').value = getLayer('embed_code').value.replace(/%%/,drawingCode);
	getLayer('title_edit').style.display = 'none';
	getLayer('title_fixed').style.display = 'block';
}

function showTitleChange() {
	getLayer('title_edit').style.display = 'block';
	getLayer('title_fixed').style.display = 'none';
}

<?php if( IsSchoolAdmin() ) { ?>
function deleteConfirm() {
	getLayer('deleteConfirm').innerHTML = 'Are you sure? <a href="javascript:doDelete()">Yes</a>';
}
function doDelete() {
	getLayer('delete_form').submit();
}
<?php } ?>

</script>
