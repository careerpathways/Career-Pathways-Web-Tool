<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="drawing_form" class="new_drawing">
        <div id="existingDrawings" style="float:right; width:330px;"></div>
        <table>

            <?php if($SITE->hasFeature('oregon_skillset')): ?>
                <tr>
                    <th width="115"><?=l('skillset name')?></th>
                    <td>
                        <div id="skillset">
                            <?php echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', '', array('0'=>'')); ?>
                        </div>
                        <div id="skillsetConf" style="color:#393; font-weight: bold"></div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php if( $school['organization_type'] != 'Other'): ?>
                <tr>
                    <th width="115"><?=l('program name label')?></th>
                    <td>
                        <div id="program">
                            <?php echo GenerateSelectBoxDB('programs', 'program_id', 'id', 'title', 'title', '', array('0'=>'Not Listed')); ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>


            <tr>
                <th valign="bottom"><div id="drawing_title_label">Custom Program Name (not recommended)</div></th>
                <td>
                    <input type="text" id="drawing_title" name="name" size="40" value="<?= $drawing['name'] ?>">
                </td>
            </tr>

            <tr>
                <th width="115">Organization</th>
                <td>
                    <?php
                    if( IsAdmin() ) {
                        echo GenerateSelectBox($schools,'school_id',$school_id);
                    } else {
                        echo '<b>'.$schools[$school_id].'</b><input type="hidden" name="school_id" id="school_id" value="'.$school_id.'" />';
                    }
                    ?>
                </td>
            </tr>

            <tr>
                <th width="115"></th>
                <td>
                    <div style="float:right">
                        <input type="button" class="submit" value="Reset" id="submitButtonReset">
                    </div>
                    <input type="button" class="submit" value="Create" id="submitButtonCreate">
                </td>
            </tr>

        </table>
        <input type="hidden" name="id" value="">
    </form>
