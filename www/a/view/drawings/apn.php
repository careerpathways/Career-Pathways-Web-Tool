<?php if($SITE->hasFeature('oregon_skillset')):	?>
    <tr class="editable">
        <th width="115"><?=l('skillset name')?></th>

        <td height="34">
            <div id="skillset" style="float:left">
                <?php echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', $drawing['skillset_id'], array(''=>'')); ?>
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
            <input type="button" class="save submit tiny" value="Save <?=l('program name label')?>">
        </div>

    </td>
</tr>

<tr class="editable">
    <th width="115"><div id="drawing_title_label">Custom Program Name (not recommended)</div></th>
    <td>
        <input type="text" id="drawing_title" name="name" size="40" value="<?= $drawing['name'] ?>">
        <input type="button" id="title_btn" onclick="saveTitle()" class="submit tiny" value="Save" />
        <input type="button" id="clear_btn" class="submit tiny" value="Clear" />
        <script>
            (function($){
                $('#clear_btn').click(function(){
                    $('#drawing_title').val('');
                    $('#title_btn').trigger('click');
                });
            }(jQuery));
        </script>
    </td>
</tr>


<tr class="editable">
    <th width="115">Organization</th>
    <td><b><?= $schools[$drawing['school_id']] ?></b><input type="hidden" id="school_id" value="<?= $drawing['school_id'] ?>" /></td>
</tr>
