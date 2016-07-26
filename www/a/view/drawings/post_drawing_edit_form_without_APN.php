<?php /** begin drawing edit form WITHOUT APN **/ ?>
<table width="100%">
	
	<tr class="editable">
		<td colspan="2">
			<div id="drawing_header" class="title_img"><?= ShowPostHeader($drawing['id']) ?></div>
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
<?php if($SITE->hasFeature('oregon_skillset')):	?>
	<tr class="editable">
		<th><?=l('skillset name')?></th>
		<td height="34"><div id="skillset" style="float:left"><?php
			echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', $drawing['skillset_id'], array(''=>''));
		?></div><div id="skillsetConf" style="color:#393; font-weight: bold; padding-left: 10px;"></div></td>
	</tr>
<?php endif; ?>
<?php if( is_array($published) ): ?>
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
		<th valign="top" width="115">PDF Link</th>
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
		<th width="115">&nbsp;</th>
		<td><!--These links -->This link will always link to the <b>published</b> version of this drawing.</td>
	</tr>
<?php else: ?>
	<tr>
		<th valign="top" width="115">Links</th>
		<td>Publish a version to get the published links for this drawing.</td>
	</tr>
<?php endif; ?>
	<tr>
        <th width="115" class="show-updated label">Show "Updated"</th>
            <td>
                <?php
                if(isset($drawing['show_updated'])){
                    $show_updated = filter_var($drawing['show_updated'], FILTER_VALIDATE_BOOLEAN); //get reliable boolean from string
                } else {
                    $show_updated = false; //default
                }
                ?>
                <input type="radio" class="true" name="show_updated" <?= $show_updated === true ? 'checked="checked"':'' ?> value="true"> Yes, show "Last Updated: mm-dd-yyyy" at the top-right of the published drawing.
                <br>
                <input type="radio" class="false" name="show_updated" <?= $show_updated === false ? 'checked="checked"':'' ?> value="false"> No, do not show it.
                <script>
                    (function($){
                        $('input[name="show_updated"]').change(function(){
                            var _action = "disable_show_updated";
                            if('true' == $(this).val()){
                                _action = "enable_show_updated";
                            }

                            $j.post('/a/drawings_post.php',
                                {
                                    mode: 'post',
                                    id: '<?= $drawing['id'] ?>',
                                    action: _action
                                }, function(data){
                                    if(true == data.success){
                                        $('.show-updated.label .notice').hide(); //in case not fully faded out yet
                                        $('.show-updated.label').append('<div class="notice" style="color:green;">saved</div>');
                                        setTimeout(function(){
                                            $('.show-updated.label .notice').fadeOut(2000);
                                        },100);
                                    }
                                },'json'
                            );
                        });
                    }($j));
                </script>
            </td>
        </tr>
	<tr>
<?php require('post_version_list.php');	?>

		<th width="115">Delete</th>
		<td>
		<?php if( CanDeleteDrawing($drawing['id']) ): ?>
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
		<?php else: ?>
			You can't delete this drawing because it was created by <a href="/a/users.php?id=<?= $drawing['created_by'] ?>"><?= $DB->GetValue('CONCAT(first_name," ",last_name)','users',$drawing['created_by']) ?></a>. Contact the creator of the drawing or any <a href="/a/users.php">Admin</a> user within your organization to delete this drawing.<br><br>
			Note: Most of the time, you're trying to delete a version. However, there is no need to delete versions, as the Web Tool is designed to maintain archival records of your POST drawings.
		<?php endif: ?>
		</td>
	</tr>
</table>
<?php /* end drawing edit form WITHOUT APN */ ?>
