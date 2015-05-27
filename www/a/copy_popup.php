<?php
chdir("..");
include("inc.php");

$version = GetDrawingInfo($_REQUEST['version_id'], $_REQUEST['mode']);

$POST = Request('mode') == 'post';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Copy Version &bull; Pathways</title>
<script type="text/javascript" src="/files/prototype.js"></script>
<script type="text/javascript" src="/common/jquery-1.3.min.js"></script>
<script>var jQuery = jQuery.noConflict();</script>
<?php if($SITE->hasFeature('approved_program_name')): ?>
<script type="text/javascript" src="/common/APN.js"></script>
<?php endif; ?>
<style type="text/css">@import "/styles.css";</style>
<style type="text/css">
body {
	padding: 1em;
}

#drawing_name,#version_note {
	width: 100%;
}

fieldset {
	margin: 1em 0;
}
</style>
</head>
<body>
<h2><?= $version['name'] ?></h2>

<form action="<?= $POST?'post_drawings.php':'drawings.php' ?>" method="post">
<input type="hidden" name="from_popup" value="true"/>
<input type="hidden" name="action" value="copy_version"/>
<input type="hidden" name="version_id" value="<?= $version['id'] ?>"/>

<?php
	if( $POST ) {
		if( $version['type'] == 'HS' ) {
			if( IsAdmin() )
				$schools = $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type = "HS" ORDER BY school_name', 'school_name', 'id');
			else
				$schools = GetAffiliatedSchools();
		} else {

			if( IsAdmin() )
				$schools = $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type != "HS" ORDER BY school_name', 'school_name', 'id');
			else {
				$user_school = $DB->SingleQuery('SELECT * FROM schools WHERE id = ' . $_SESSION['school_id']);
	
				if($user_school['organization_type'] == 'Other')
					$schools = GetAffiliatedSchools('CC');
	
				$schools[$_SESSION['school_id']] = $user_school['school_name'];
			}
		}
	} else {
		$schools = $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type != "HS" ORDER BY school_name', 'school_name', 'id');
	}
?>

<?php if (IsWebmaster() || (IsStaff() && $POST && count($schools) > 1)) : ?>
<fieldset id="copy_to">
	<legend>Copy To</legend>
	<?php if( !(	($POST && $version['type'] == 'HS' && IsStaff())
				 || ($POST && $version['type'] == 'CC' && !IsStaff())	 )
			&& $version['school_id'] != $_SESSION['school_id'] ) { ?>
		<input class="radio" type="radio" name="copy_to" value="user_school" id="copy_to_user_school" /> <label for="copy_to_user_school">Your Organization</label><br/>
	<?php } ?>
	<?php if( IsAdmin() || $version['school_id'] == $_SESSION['school_id']  || array_key_exists($version['school_id'], $schools)) { ?>
		<input class="radio" type="radio" name="copy_to" value="same_school" id="copy_to_same_school"/> <label for="copy_to_same_school"><?=$version["school_name"]?></label><br/>
	<?php } ?>
	<?php if( IsAdmin() || (IsStaff() && $POST && $version['type'] == 'HS') || (IsStaff() && $POST && count($schools) > 1) ) { ?>
		<input class="radio" type="radio" name="copy_to" value="othr_school" id="copy_to_othr_school"/> <label for="copy_to_othr_school">Select Organization</label><br/>
	<?php } ?>
</fieldset>

<fieldset id="organization">
	<legend>Organization</legend>
	<?php
	echo GenerateSelectBox($schools, 'target_org_id');
	?>
</fieldset>
<?php endif;

if (IsAdmin() || $_SESSION['school_id'] === $version['school_id'] || ($POST && array_key_exists($version['school_id'], $schools))) { ?>
<fieldset id="create">
	<legend>Create</legend>
	<input class="radio" type="radio" name="create" value="new_version" id="create_new_version" checked="true"/> <label for="create_new_version">New Version</label><br/>
	<input class="radio" type="radio" name="create" value="new_drawing" id="create_new_drawing"/> <label for="create_new_drawing">New Drawing</label><br/>
</fieldset>
<?php } else { ?>
	<p>A new drawing will be created</p>
	<input class="radio" type="radio" name="create" value="new_drawing" id="create_new_drawing" style="display:none;" checked="checked" />
<?php } ?>

<fieldset id="drawingName">
	<legend><label for="drawing_title">New Drawing Name</label></legend>
	<?php if($SITE->hasFeature('approved_program_name')): ?>	
	<table>
    	<?php if($SITE->hasFeature('oregon_skillset')):	?>
	    <tr class="editable">
	        <th width="115"><?=l('skillset name')?></th>

	        <td height="34">
	            <div id="skillset" style="float:left">
	                <?php echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', 0, array(''=>'')); ?>
	            </div>
	            <div id="skillsetConf" style="color:#393; font-weight: bold; padding-left: 10px;"></div>
	        </td>
	    </tr>
	<?php endif; ?>
	<tr class="editable">
	    <th width="115"><?=l('program name label')?></th>
	    <td>
	        <div class="approved-program-name">
	            <select name="program_id" id="program_id">
	                <!-- Javascript fills values -->
	            </select>
	            <!-- <input type="button" class="save submit tiny" value="Save <?=l('program name label')?>"> -->
	        </div>

	    </td>
	</tr>
	</table>
	<script>
		var drawingType = "<?= Request('mode') ?>";
		if(drawingType !== 'pathways' && drawingType !== 'post'){
			if(console && console.error){
				console.error('var drawingType needs to be either "pathways" (for roadmap drawings) or "post". Got: ' + drawingType)
			}
		}
	    var apn = new APN({
	        drawingId : undefined, //new drawing
	        drawingType : "<?= Request('mode') ?>", //pathways or post
	        programId: undefined //new drawing
	    });
	</script>
	<?php else: ?>
		<input type="text" name="drawing_name" id="drawing_name" value="<?= $version['name'] ?> Copy">
	<?php endif; ?>
</fieldset>

<?php if( $POST ): ?>
<fieldset id="degreeType">
	<legend><label for="degree_type">Degree Type</label></legend>
    <div class="">
        <?php 
        	$drawingType = $version['type'];
        	$currentDegreeType = $version['sidebar_text_right'];
        	echo GenerateSelectBoxDB('post_sidebar_options', 'degree_type', 'text', 'text', 'text', $currentDegreeType, array(''=>''), "type='$drawingType'"); 
        ?>
    </div>
</fieldset>
<?php endif; ?>
<fieldset id="versionNote">
    <legend><label for="version_note">Version Note</label></legend>
    <input type="text" maxlength="255" name="version_note" id="version_note" />
</fieldset>
<input type="submit" value="OK" id="ok"/> <input type="reset" id="cancel" value="Cancel"/>
</form>
<script type="text/javascript">
var create = $('create');
var organization = $('organization');
var drawingName = $('drawingName');
var createNewDrawing = $('create_new_drawing');
var copyToSameSchool = $('copy_to_same_school');
var copyToUserSchool = $('copy_to_user_school');
var copyToOthrSchool = $('copy_to_othr_school');

if( copyToUserSchool ) { copyToUserSchool.checked = true; }
else if( copyToSameSchool ) { copyToSameSchool.checked = true; }
else if( copyToOthrSchool ) { copyToOthrSchool.checked = true; }

if( createNewDrawing == null ) {
	createNewDrawing = {checked: false};
}
if( create == null ) {
	create = {hide: function(){}};
}

var updateState = function() {
	if (copyToUserSchool && copyToUserSchool.checked) {
		createNewDrawing.checked = true;
		drawingName.show();
		create.hide();
		organization.hide();
	}
	else if (copyToSameSchool && copyToSameSchool.checked) {
		if (create) {
			create.show();
			organization.hide();
		}
		if (!createNewDrawing || createNewDrawing.checked) {
			drawingName.show();
		}
		else {
			drawingName.hide();
		}
	}
	else if (copyToOthrSchool && copyToOthrSchool.checked) {
		createNewDrawing.checked = true;
		drawingName.show();
		create.hide();
		organization.show();
	}
	if( createNewDrawing && createNewDrawing.checked ) {
		drawingName.show();
	} else {
		drawingName.hide();
	}
};
$$('#create input').invoke('observe', 'click', updateState);

$$('#copy_to input').invoke('observe', 'click', updateState);

updateState(); 

$('cancel').observe('click', function() {
	window.parent.chGreybox.close();
});
</script>
</body>
</html>
