<?php

$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/%%.xml';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/text/%%.html';


$drawing = $DB->LoadRecord('post_drawing_main',$id);

// force non-admins to the school of this drawing to prevent attacks
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

if( $id != "" ) {
	$published = $DB->SingleQuery("SELECT * FROM post_drawings WHERE published=1 AND parent_id=".$drawing['id']);
}

?>

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
	<th>Link</th>
	<td>
		<div id="drawing_link"><?php
		$url = str_replace('%%',$drawing['code'],$published_link);
		echo '<a href="'.$url.'">'.$url.'</a>';
		?></div>
	</td>
</tr>
<tr>
	<th valign="top">XML</th>
	<td>
		<div id="drawing_link_xml"><?php
		$url = str_replace('%%',$drawing['code'],$xml_link);
		echo '<a href="'.$url.'">'.$url.'</a>';
		?></div>
	</td>
</tr>
<tr>
	<th valign="top">Accessible</th>
	<td>
		<div id="drawing_link_ada"><?php
		$url = str_replace('%%',$drawing['code'],$accessible_link);
		echo '<a href="'.$url.'">'.$url.'</a>';
		?></div>
		These links, as well as the embed code above, will always link to the <b>published</b> version of this drawing.<br>
		<br>
	</td>
</tr>
<?php
	require('post_version_list.php');
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
