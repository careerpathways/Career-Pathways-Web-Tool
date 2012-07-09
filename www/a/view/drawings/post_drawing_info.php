<?php

$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/$$/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/%%.xml';
$pdf_link = 'http://'.$_SERVER['SERVER_NAME'].'/pdf/post/$$/%%.pdf';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/text/%%.html';


$drawing = $DB->LoadRecord('post_drawing_main',$id);

// force non-admins to the school of this drawing to prevent hacks
$schools = $DB->VerticalQuery("SELECT * FROM schools ORDER BY school_name",'school_name','id');
$schls = $DB->VerticalQuery("SELECT * FROM schools ORDER BY school_name",'school_abbr','id');
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
<?php 
/** begin new drawing form **/
if( $id == "" ) {
	
	$drawing = array('id'=>'', 'code'=>'', 'name'=>'');	
	
?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="drawing_form">
<table>
<tr>
	<th valign="bottom">Occupation/Program</th>
	<td>
		<input type="text" id="drawing_title" name="name" size="80" value="<?= $drawing['name'] ?>" onblur="checkName(this)">
		<div id="checkNameResponse" class="error"></div>
	</td>
</tr>
<tr>
	<th width="80">Organization</th>
	<td>
	<?php

		$user_school = $DB->SingleQuery('SELECT * FROM schools WHERE id = ' . $school_id);

		if( Request('type') == 'cc' )
		{
			if(IsAdmin()) {
				$these_schools = $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type!="HS" ORDER BY school_name', 'school_name', 'id');
			} else {
				if($user_school['organization_type'] == 'Other')
					$these_schools = GetAffiliatedSchools('CC');
	
				$these_schools[$school_id] = $user_school['school_name'];
			}
		}
		else
		{
			$these_schools = GetAffiliatedSchools();

			// Add the user's school if their school is the same type as the new drawing
			if( strtolower(Request('type')) == strtolower($user_school['organization_type']) )
				$these_schools[$school_id] = $user_school['school_name'];
				
		}

		// REPLACED THIS CODE BELOW --> if(count($these_schools) == 1)
		//JGD: I don't know how this happens, but I ran into a case where these_schools only had one entry, but it didn't match the school id.
		//JGD: So I show the selection box if that is the case. Not sure if that's the perfect solution...
		//logmsg( "school_id: $school_id\n" );
		//logmsg( "these_schools: ".varDumpString($these_schools)."\n" );
		if( (count($these_schools) == 1) && (isset($these_schools[$school_id])) )
		{
			echo '<b>'.$these_schools[$school_id].'</b>';
		}
		else
		{
			echo GenerateSelectBox($these_schools, 'school_id', $_SESSION['school_id']);
		}

	?>
	</td>
</tr>
<?php
if($SITE->hasFeature('oregon_skillset')){
?>
<tr>
	<th><?=l('skillset name')?></th>
	<td valign="top"><span id="skillset"><?php
		echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', '', array(''=>''));
	?></span>(optional)</td>
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
	<table width="100%">
	<tr class="editable">
		<td colspan="2">
			<div id="drawing_header" class="title_img" style="height:19px;font-size:0px;overflow:hidden;background-color:#295a76"><?= ShowPostHeader($drawing['id']) ?></div>
		</td>
	</tr>
	<tr class="editable">
		<th><?=l('program name label')?></th>
		<td>
			<div id="title_fixed"><span id="title_value" style="font-size: 12pt;"><?= $drawing['name'] ?></span> 
				<?=(IsAffiliatedWith($drawing['school_id']) || $drawing['school_id']==$_SESSION['school_id'] ? '<a href="javascript:showTitleChange()" class="tiny">edit</a>' : '')?></div>
			<div id="title_edit" style="display:none">
				<input type="text" id="drawing_title" name="name" size="80" value="<?= $drawing['name'] ?>" onblur="checkName(this)">
				<input type="button" class="submit tiny" value="Save" id="submitButton" onclick="savetitle()">
				<div id="checkNameResponse" class="error"></div>
			</div>
		</td>
	</tr>
	<tr class="editable">
		<th width="80">Organization</th>
		<td><b><?= $schools[$drawing['school_id']] ?></b><input type="hidden" id="school_id" value="<?= $drawing['school_id'] ?>" /></td>
	</tr>
	<?php
	if($SITE->hasFeature('oregon_skillset')){
	?>
	<tr class="editable">
		<th><?=l('skillset name')?></th>
		<td height="34"><div id="skillset" style="float:left"><?php
			echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', $drawing['skillset_id'], array(''=>''));
		?></div><div id="skillsetConf" style="color:#393; font-weight: bold; padding-left: 10px;"></div></td>
	</tr>
	<?php
	}

	if( is_array($published) ) {
?>
	<tr>
		<th>HTML Link</th>
		<td>
			<div style="width:16px; float: left;"><a href="javascript:preview_drawing(<?=$published['parent_id'].','.$published['id']?>)"><?=SilkIcon('magnifier.png')?></a></div>
			<div id="drawing_link"><?php
			$url = str_replace(array('$$','%%'),array($drawing['id'],CleanDrawingCode($schls[$drawing['school_id']].'_'.$drawing['name'])),$published_link);
			echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
			?></div>
		</td>
	</tr>
	<tr>
		<th valign="top">PDF Link</th>
		<td><?php 
			$url = str_replace(array('$$','%%'),array($id,CleanDrawingCode($schls[$drawing['school_id']].'_'.$drawing['name'])),$pdf_link);
			?>
			<div style="width:16px; float:left; margin-right: 2px;"><a href="<?=$url?>"><?=SilkIcon('page_white_acrobat.png')?></a></div>
			<div id="drawing_link_pdf">
				<input type="text" style="width:542px" value="<?=$url?>" onclick="this.select()" />
			</div>
		</td>
	</tr>
	<!--
	<tr>
		<th valign="top">XML Link</th>
		<td>
			<div id="drawing_link_xml"><?php
			$url = str_replace('%%',$drawing['code'],$xml_link);
			echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
			?></div>
		</td>
	</tr>
	-->
	<tr>
		<td>&nbsp;</td>
		<td><!--These links -->This link will always link to the <b>published</b> version of this drawing.</td>
	</tr>
<?php
	} else {
?>
<tr>
	<th valign="top">Links</th>
	<td>Publish a version to get the published links for this drawing.</td>
</tr>
<?php	
	}
	
	require('post_version_list.php');		
?>
	<tr>
		<th>Delete</th>
		<td width="545">
		<?php if( CanDeleteDrawing($drawing['id']) ) { ?>
			<p><a href="javascript:deleteConfirm()" class="noline"><?=SilkIcon('cross.png')?> Delete this drawing and remove <b>all</b> versions</a></p>
			<div id="deleteConfirm" style="display: none">
				<p>Please be careful. Deleting this drawing will break any links from external web pages to this drawing.</p>
				<p><b>There is no way to recover deleted drawings!</b></p>
				<p>Are you sure? <a href="javascript:doDelete()">Yes</a></p>
			</div>
			<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="delete_form">
				<input type="hidden" name="id" value="<?= $drawing['id'] ?>">
				<input type="hidden" name="delete" value="delete">
			</form>
		<?php } else { ?>
			You can't delete this drawing because it was created by <a href="/a/users.php?id=<?= $drawing['created_by'] ?>"><?= $DB->GetValue('CONCAT(first_name," ",last_name)','users',$drawing['created_by']) ?></a>. Contact the creator of the drawing or any <a href="/a/users.php">Admin</a> user within your organization to delete this drawing.<br><br>
			Note: Most of the time, you're trying to delete a version. However, there is no need to delete versions, as the Web Tool is designed to maintain archival records of your POST drawings.
		<?php } ?>
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

Array.prototype.remove = function(s) {
	var i = this.indexOf(s);
	if(i != -1) this.splice(i, 1);
}

function checkName(title) {
	$j.get('/a/drawings_checkname.php',
		  {mode: 'post',
		   id: '<?= $drawing['id'] ?>',
		   title: title.value<?php if(IsAdmin()) { ?>,
		   school_id: $j("#school_id").val()
		   <?php } ?>
		  },
		  verifyName);
}

function verifyName(result) {
	if( result == 0 ) {
		getLayer('checkNameResponse').innerHTML = 'There is already a drawing by that name. Choose a different name.';
		$j('#submitButton').css('color', '#666666');
	} else {
		getLayer('checkNameResponse').innerHTML = '';
		$j('#submitButton').css('color', '');
	}
}

function savetitle() {
	var title = getLayer('drawing_title');
	$j.get('/a/drawings_checkname.php',
		  {mode: 'post',
		   id: '<?= $drawing['id'] ?>',
		   title: title.value<?php if(IsAdmin()) { ?>,
		   school_id: $j("#school_id").val()
		   <?php } ?>
		  },
		  verifyNameSubmit);
}

function submitform() {
	var title = getLayer('drawing_title');
	$j.get('/a/drawings_checkname.php',
		  {mode: 'post',
		   id: '<?= $drawing['id'] ?>',
		   title: title.value<?php if(IsAdmin()) { ?>,
		   school_id: $j("#school_id").val()
		   <?php } ?>
		  },
		  verifyNameSubmitNew);
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
	getLayer('title_edit').style.display = 'none';
	getLayer('title_fixed').style.display = 'block';
}

function showTitleChange() {
	getLayer('title_edit').style.display = 'block';
	getLayer('title_fixed').style.display = 'none';
}

function preview_drawing(code,version) {
	if( version == null )
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/post/'+code+'.html"></iframe></div>',800,600, null, 'Preview');
	else
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/post/'+code+'/'+version+'.html"></iframe></div>',800,600, null, 'Preview');
}

<?php if( $drawing['id'] && CanDeleteDrawing($drawing['id']) ) { ?>
function deleteConfirm() {
	getLayer('deleteConfirm').style.display = "block";
}
function doDelete() {
	getLayer('delete_form').submit();
}
<?php } ?>

<?php if( $drawing['id'] ) { ?>

$j(document).ready(function(){
	$j('#skillset select').bind('change', function() {
		$j('#skillsetConf').html('Saved!');
		$j('#skillset select').css({backgroundColor: '#99FF99'});
		setTimeout(function() {
			$j('#skillset select').css({backgroundColor: '#FFFFFF'});
			$j('#skillsetConf').html('');
		}, 500);
		$j.post('drawings_post.php',
			{action: 'skillset',
			 mode: 'post',
			 id: <?= intval($drawing['id']) ?>,
			 skillset_id: $j('#skillset select').val()
			},
			function() {
			}
		);
	});
});
					


<?php } ?>

</script>
