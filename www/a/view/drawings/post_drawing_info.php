<?php

$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/%%.xml';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/text/%%.html';


$drawing = $DB->LoadRecord('post_drawing_main',$id);

// force non-admins to the school of this drawing to prevent hacks
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

<script type="text/javascript" src="/common/jquery-1.3.min.js"></script>
<script type="text/javascript">
var $j = jQuery.noConflict();
</script>
<script type="text/javascript" src="/files/greybox.js"></script>
<!--<script type="text/javascript" src="/files/post_drawing_list.js"></script>-->

<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

<p>
<?php if( $id == "" ) {
	
	$drawing = array('id'=>'', 'code'=>'', 'name'=>'');	
	
?>
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
		if( Request('type') == 'cc' )
		{
			$these_schools = $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type!="HS" ORDER BY school_name', 'school_name', 'id');
		}
		else
		{
			$these_schools = $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type="HS" ORDER BY school_name', 'school_name', 'id');
		}
		echo GenerateSelectBox($these_schools, 'school_id', $_SESSION['school_id']);
	} else {
		echo '<b>'.$schools[$school_id].'</b>';
	}
	?>
	</td>
</tr>
<?php
if( Request('type') == 'cc' ) {
	?>
	<tr>
		<th>&nbsp;</th>
		<td><br />Choose the initial size of your drawing. You will be able to change this later.</td>
	</tr>
	<tr>
		<th>Terms</th>
		<td>
			<?php
				$range = array(3,6,9,12);
				$options = array();
				foreach( $range as $i )
					$options[$i] = $i;
				echo GenerateSelectBox($options, 'num_terms', 3);
			?>
		</td>
	</tr>
	<tr>
		<th>Empty Rows</th>
		<td>
			<?php
				$range = range(0, 9);
				$options = array();
				foreach( $range as $i )
					$options[$i] = $i;
				echo GenerateSelectBox($options, 'num_extra_rows', 0);
			?>
		</td>
	</tr>
	<tr>
		<th>Columns</th>
		<td>
			<?php
				$range = range(3, 9);
				$options = array();
				foreach( $range as $i )
					$options[$i] = $i;
				echo GenerateSelectBox($options, 'num_columns', 6);
			?>
		</td>
	</tr>
<?php
}
?>
<tr>
	<td>&nbsp;</td>
	<td><input type="button" class="submit" value="Create" id="submitButton" onclick="submitform()"></td>
</tr>
</table>
<input type="hidden" name="id" value="" />
<input type="hidden" name="type" value="<?= Request('type') ?>" />
</form>

<?php
/** end new drawing form **/
} else {

/** begin drawing edit form **/
	?>
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
		<th>Link</th>
		<td>
			<div id="drawing_link"><?php
			$url = str_replace('%%',$drawing['code'],$published_link);
			echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
			?></div>
		</td>
	</tr>
	<!--
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
	-->
	<tr>
		<td>&nbsp;</td>
		<td><!--These links -->This link will always link to the <b>published</b> version of this drawing.</td>
	</tr>
	<?php
		require('post_version_list.php');
	?>
	<tr>
		<th>Delete</th>
		<td width="545">
		<?php if( CanDeleteDrawing($drawing['id']) ) { ?>
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
			Note: Most of the time, you're trying to delete a version. However, there is no need to delete versions, as the Web Tool is designed to maintain archival records of your roadmap designs.
		<?php } ?>
		</td>
	</tr>
	<tr>
		<th>Connections<br /><a href="javascript:createConnection(<?= $drawing['id'] ?>)"><img src="/common/silk/add.png" width="16" height="16" /></a></th>
		<td>This drawing connects to the following <?= ($drawing['type'] == 'HS' ? 'Community College Pathways' : 'High School Programs') ?>:<br /><br />
		<div id="connected_drawing_list">
		<?php
			ShowSmallDrawingConnectionList($drawing['id']);
		?>
		</div>
		</td>
	</tr>
	</table>
	
	<?php
/** end drawing edit form **/
}
?>
</p>

<script type="text/javascript" src="/common/URLfunctions1.js"></script>
<script type="text/javascript">

var MODE = '<?= $MODE ?>';

var drawing_code = '<?= $drawing['code'] ?>';

var published_link = "<?= $published_link ?>";
var xml_link = "<?= $xml_link ?>";
var accessible_link = "<?= $accessible_link ?>";

var selected_drawings = Array();

Array.prototype.remove = function(s) {
	var i = this.indexOf(s);
	if(i != -1) this.splice(i, 1);
}

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
	/*
	getLayer('drawing_link_xml').innerHTML = '<a href="'+xml_link.replace(/%%/,drawingCode)+'">'+xml_link.replace(/%%/,drawingCode)+'</a>';
	getLayer('drawing_link_ada').innerHTML = '<a href="'+accessible_link.replace(/%%/,drawingCode)+'">'+accessible_link.replace(/%%/,drawingCode)+'</a>';
	*/
	getLayer('title_edit').style.display = 'none';
	getLayer('title_fixed').style.display = 'block';
}

function showTitleChange() {
	getLayer('title_edit').style.display = 'block';
	getLayer('title_fixed').style.display = 'none';
}

function preview_drawing(code,version) {
	chGreybox.create('<div id="dpcontainer"><iframe src="/c/post/'+code+'/'+version+'.html"></iframe></div>',800,600, null, 'Preview');
}

<?php if( $drawing['id'] && CanDeleteDrawing($drawing['id']) ) { ?>
function deleteConfirm() {
	getLayer('deleteConfirm').innerHTML = 'Are you sure? <a href="javascript:doDelete()">Yes</a>';
}
function doDelete() {
	getLayer('delete_form').submit();
}
<?php } ?>

<?php if( $drawing['id'] ) { ?>

function createConnection(drawing_id)
{
	$j.get("post_drawings.php",
		{drawing_id: <?= intval($drawing['id']) ?>, action: 'drawing_list', showForm: 1},
		function(data) {
			chGreybox.create(data, 700,500);
		}
	);
}

function loadDrawingPreview(code)
{
	$j('#drawing_preview_box iframe').attr('src', '/c/post/'+code+'.html');
	$j('#drawing_preview_box').css('display', 'block');
}

function select_organization(val)
{
	$j('#drawing_preview_box').css('display', 'none');
	$j('#drawing_preview_box iframe').attr('src', '');
	$j.get("post_drawings.php",
		{
		 school_id: $j("#list_schools option:selected").val(),
		 action: 'drawing_list'
		},
		function(data) {
			$j('#list_of_drawings').html(data);
			$j('#submit_btn').css({display:'none'});
			$j('.drawing_select').hover(
				function() {
					$j(this).css({'background-color': '#FFFFAA', cursor: 'pointer'});
				},
				function() {
					$j(this).css({'background-color': '#FFFFFF', cursor: 'normal'});
				}
			);
			$j('.drawing_select > td:not(.preview)').click(
				function(e) {
					var dr_id = $j(this).parent().attr('id').split('_')[1];
					if( selected_drawings.indexOf(dr_id) == -1 )
					{
						$j(this).parent().children('.icon').children().css({opacity: 0, display: "block"}).animate({opacity:1}, 150);
						selected_drawings.push(dr_id);
					}
					else
					{
						$j(this).parent().children('.icon').children().css({opacity: 1, display: "block"}).animate({opacity:0}, 150, function() {
								$j(this).css({display:'none'});
							});
						selected_drawings.remove(dr_id);
					}
					if( selected_drawings.length > 0 )
					{
						$j('#submit_btn').css({display:'block'});
					}
					else
					{
						$j('#submit_btn').css({display:'none'});
					}
				}
			);
		}
	);
}

function save_drawing_selection()
{
	if( selected_drawings.length > 0 )
	{
		$j.post("post_drawings.php",
			{action: 'drawing_list',
			 drawing_id: <?= intval($drawing['id']) ?>,
			 save: 1,
			 drawings: selected_drawings.join(",")
			},
			function(data) {
				$j('#connected_drawing_list').html(data);
				chGreybox.close();
				selected_drawings = Array();
			});
	}
}

function remove_connection(id)
{
		$j.post("post_drawings.php",
			{action: 'drawing_list',
			 drawing_id: <?= intval($drawing['id']) ?>,
			 delete: id
			},
			function(data) {
				$j('#connected_drawing_list').html(data);
			});
}

<?php } ?>

</script>
