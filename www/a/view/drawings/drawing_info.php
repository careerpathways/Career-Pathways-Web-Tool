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

<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

<p>
<?php if( $id == "" ) { ?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="drawing_form">
<table>
<tr>
	<th>Title</th>
	<td>
		<input type="text" id="drawing_title" name="name" size="20" value="<?= $drawing['name'] ?>" onblur="checkName(this)">
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
			<input type="text" id="drawing_title" name="name" size="20" value="<?= $drawing['name'] ?>" onblur="checkName(this)">
			<input type="button" class="submit tiny" value="Save" id="submitButton" onclick="savetitle()">
			<span id="checkNameResponse" class="error"></span>
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
			//echo '<a href="javascript:preview_drawing('.$published['id'].')"><img src="/files/charts/gif/'.$published['id'].'.gif" height="100" width="140" class="border"></a>';
			echo '<a href="javascript:preview_drawing('.$published['id'].')">Preview Published Drawing</a>';
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
<tr>
	<th valign="top">Versions</th>
	<td>
	<table>
	<?php

		$versions = $DB->MultiQuery("
			SELECT *
			FROM drawings
			WHERE drawings.parent_id=".$drawing['id']."
				AND deleted=0
			ORDER BY version_num");
		foreach( $versions as $v ) {
			$created = ($v['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$v['created_by']));
			$modified = ($v['last_modified_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$v['last_modified_by']));

			echo '<tr'.($v['published']?' class="version_list_published"':'').'>';
			/*
			echo '<td class="border" width="145" height="105">';
				echo '<a href="javascript:preview_drawing('.$v['id'].');">';
				echo '<img src="/files/charts/gif/'.$v['id'].'.gif" height="100" width="140" class="border">';
				echo '</a>';
			echo '</td>';
			*/

			echo '<td class="border" width="400" valign="top"><table height="80">';
				echo '<tr>';
					echo '<td width="60"><b>Version</b></td>';
					echo '<td>'.$v['version_num'].($v['published']?' (Published)':'').'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Created</b></td>';
					echo '<td>'.($v['date_created']==''?'':$DB->Date("m/d/Y g:ia",$v['date_created'])).' by '.$created['name'].'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Modified</b></td>';
					echo '<td>'.($v['last_modified']==''?'':$DB->Date("m/d/Y g:ia",$v['last_modified'])).' by '.$modified['name'].'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Note</b></td>';
					echo '<td>'.$v['note'].'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Actions</b></td>';
					echo '<td>';
						echo '<a href="'.$_SERVER['PHP_SELF'].'?action=draw&amp;version_id='.$v['id'].'">'.($v['published']?'view':'draw').'</a>';
						echo ' &nbsp;&nbsp;&nbsp;';
						echo '<a href="javascript:preview_drawing('.$v['id'].')">preview</a>';
						echo ' &nbsp;&nbsp;&nbsp;';
						echo '<a href="'.$_SERVER['PHP_SELF'].'?action=copy_version&amp;version_id='.$v['id'].'">copy this version</a>';
					echo '</td>';
				echo '</tr>';
			echo '</table></td>';
			echo '</tr>';
		}

	?>
	</table>
	</td>
</tr>
<?php
	/*
	who can delete drawings?
		1. admins
		2. school admins at the same school
	*/
	if(
		IsAdmin() ||
		(IsSchoolAdmin() && $_SESSION['school_id'] == $drawing_main['school_id'])
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

<script type="text/javascript">

schools = new Array(<?= count($schools) ?>);
<?php
$i=0;
foreach( $schools as $sid=>$school ) {
	echo 'schools['.$i.'] = '.$sid.";\n";
	$i++;
}
?>

function checkName(title) {
	ajaxCallback(verifyName, '/a/drawings_checkname.php?id=<?= $drawing['id'] ?>&title='+title.value<?= (IsAdmin()?"+'&school_id=".$school_id."'":'') ?>);
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
	ajaxCallback(verifyNameSubmit, '/a/drawings_checkname.php?id=<?= $drawing['id'] ?>&title='+title.value<?= (IsAdmin()?"+'&school_id=".$school_id."'":'') ?>);
}

function submitform() {
	var title = getLayer('drawing_title');
	ajaxCallback(verifyNameSubmitNew, '/a/drawings_checkname.php?id=<?= $drawing['id'] ?>&title='+title.value<?= (IsAdmin()?"+'&school_id=".$school_id."'":'') ?>);
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
		ajaxCallback(cbNameChanged, '/a/drawings_post.php?id=<?= $drawing['id'] ?>&title='+title.value);
	}
}

function cbNameChanged(drawingCode) {
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

function preview_drawing(id) {
	chGreybox.create('<div id="dpcontainer"><iframe src="/c/view.php?id='+id+'"></iframe></div>',800,600);
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
