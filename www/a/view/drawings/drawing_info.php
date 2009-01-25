<?php

$php_page = 'drawings.php';
$main_table = 'drawing_main';
$drawings_table = 'drawings';
$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/%%.xml';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/text/%%.html';

$drawing = $DB->LoadRecord($main_table,$id);

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

$embed_code = '<iframe width="800" height="600" src="'.$published_link.'" frameborder="0" scrolling="no"></iframe>';

if( $id != "" ) {
	$published = $DB->SingleQuery("SELECT * FROM $drawings_table WHERE published=1 AND parent_id=".$drawing['id']);
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
	<th valign="bottom">Title</th>
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
	<th width="80">Organization</th>
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
	<td>
		<div id="drawing_link"><?php
		$url = str_replace('%%',$drawing['code'],$published_link);
		echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
		?></div>
	</td>
</tr>
<tr>
	<th valign="top">XML</th>
	<td>
		<div id="drawing_link_xml"><?php
		$url = str_replace('%%',$drawing['code'],$xml_link);
		echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
		?></div>
	</td>
</tr>
<tr>
	<th valign="top">Accessible</th>
	<td>
		<div id="drawing_link_ada"><?php
		$url = str_replace('%%',$drawing['code'],$accessible_link);
		echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
		?></div>
		These links, as well as the embed code above, will always link to the <b>published</b> version of this drawing.<br>
		<br>
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
?>
<tr>
	<th>Delete</th>
	<td width="545">
	<?php if( CanDeleteDrawing($drawing) ) { ?>
		Deleting this drawing will remove all versions. Please be careful. Deleting this drawing will break any links from external web pages to this drawing.
		<p><b>There is no way to recover deleted drawings!</b></p>
		<p>If you are sure you want to delete the entire drawing, click the link below:</p>
		<p><a href="javascript:deleteConfirm()">Delete drawing and all versions</a></p>
		<div id="deleteConfirm"></div>
		<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="delete_form">
			<input type="hidden" name="id" value="<?= $drawing['id'] ?>">
			<input type="hidden" name="delete" value="delete">
		</form>
	<?php } else { ?>
		You can't delete this drawing because it was created by <a href="/a/users.php?id=<?= $drawing['created_by'] ?>"><?= $DB->GetValue('CONCAT(first_name," ",last_name)','users',$drawing['created_by']) ?></a>. Contact the creator of the drawing or any <a href="/a/users.php#SchoolAdmin">School Admin</a> user within your school to delete this drawing.<br><br>
		Note: Most of the time, what you really want to do is delete a version. There is no need to delete versions, as the Web Tool is designed to maintain archival records of your roadmap designs.
	<?php } ?>
	</td>
</tr>
</table>
<?php } ?>
</p>

<script type="text/javascript" src="/common/URLfunctions1.js"></script>
<script type="text/javascript">

var MODE = '<?= $MODE ?>';

var drawing_code = '<?= $drawing['code'] ?>';
var schools = new Array(<?= count($schools) ?>);
<?php
$i=0;
foreach( $schools as $sid=>$school ) {
	echo 'schools['.$i.'] = '.$sid.";\n";
	$i++;
}
?>

var published_link = "<?= $published_link ?>";
var xml_link = "<?= $xml_link ?>";
var accessible_link = "<?= $accessible_link ?>";

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
		ajaxCallback(cbNameChanged, '/a/drawings_post.php?mode=<?= $MODE ?>&id=<?= $drawing['id'] ?>&title='+URLEncode(title.value));
	}
}

function cbNameChanged(drawingCode) {
	drawing_code = drawingCode;
	getLayer('title_value').innerHTML = getLayer('drawing_title').value;
	getLayer('drawing_link').innerHTML = '<a href="'+published_link.replace(/%%/,drawingCode)+'">'+published_link.replace(/%%/,drawingCode)+'</a>';
	getLayer('drawing_link_xml').innerHTML = '<a href="'+xml_link.replace(/%%/,drawingCode)+'">'+xml_link.replace(/%%/,drawingCode)+'</a>';
	getLayer('drawing_link_ada').innerHTML = '<a href="'+accessible_link.replace(/%%/,drawingCode)+'">'+accessible_link.replace(/%%/,drawingCode)+'</a>';
	getLayer('embed_code').value = '<?= $embed_code ?>';
	getLayer('embed_code').value = getLayer('embed_code').value.replace(/%%/,drawingCode);
	getLayer('title_edit').style.display = 'none';
	getLayer('title_fixed').style.display = 'block';
}

function showTitleChange() {
	getLayer('title_edit').style.display = 'block';
	getLayer('title_fixed').style.display = 'none';
}

<?php if( CanDeleteDrawing($drawing) ) { ?>
function deleteConfirm() {
	getLayer('deleteConfirm').innerHTML = 'Are you sure? <a href="javascript:doDelete()">Yes</a>';
}
function doDelete() {
	getLayer('delete_form').submit();
}
<?php } ?>

</script>
