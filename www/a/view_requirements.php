<?php
chdir("../");
include("inc.php");

if( strtolower($_SERVER['REQUEST_METHOD']) == "post" ) {

        if( Request('id') ) {
                // required fields
                $fields = array('requirement_type','description');
        
                foreach( $fields as $field ) {
                        $data[$field] = Request($field);
                }
        
                $DB->Update('requirements', $data, Request('id'));
                header("Location: ".$_SERVER['PHP_SELF']."?success=".Request('id'));
        } else if(Request('action')=='add') {
            if(Request('requirement_type')==-1){
                header("Location: ".$_SERVER['PHP_SELF']."?action=add&error=".urlencode('Select a requirement type.').'&description='.Request('description'));
            } else if(Request('description')==''){
                header("Location: ".$_SERVER['PHP_SELF']."?action=add&error=".urlencode('Enter the requirement description.').'&type='.Request('requirement_type'));
            } else {
                $fields = array('requirement_type','description');
        
                foreach( $fields as $field ) {
                        $data[$field] = Request($field);
                }
        
                $id = $DB->Insert('requirements', $data);
                header("Location: ".$_SERVER['PHP_SELF']."?added=".$id);
            }
            
        }
} else {

        if( Request('id') ) {
            if(Request('action') && Request('action')=='remove') {
                //DELETE the requirement.
            $sql = "DELETE FROM requirements WHERE id=".intval(Request('id'));
            $result = $DB->MultiQuery($sql);
            header("Location: ".$_SERVER['PHP_SELF']."?removed=1");
        } else {
            PrintHeader();
            //SHOW edit screen...
                //pull requirement from database
                $requirement = $DB->SingleQuery("SELECT * FROM requirements WHERE id='".Request('id')."'");
                if( count($requirement) > 0 ) {
                        $requirement['requirement_type'] = $requirement['requirement_type'];
                        $requirement['description'] = htmlspecialchars($requirement['description']);
    
    
                        //$name = ucwords(str_replace('_',' ',$email['id']));
                        ?>
    
    
                        <h1>Edit Requirement</h1>
    
                        <p><div class="mod_addlink"><a href="<?= $_SERVER['PHP_SELF'] ?>">[back]</a></div></p>
    
                        <p><?= $requirement['description'] ?></p>
    
                        <br>
        
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <table>
                                <tr>
                                        <td valign="top">Type</td>
                                        <td>
                                                <select name="requirement_type" style="width:220px">
                                                        <option value="minimum" <?php echo($requirement['requirement_type']=='minimum'?'selected="selected"':''); ?>>Minimum Criteria</option>
                                                        <option value="extra" <?php echo($requirement['requirement_type']=='extra'?'selected="selected"':''); ?>>Exceeds Minimum Criteria</option>
                                                </select>
                                        </td>
                                </tr>
                                <tr>
                                        <td valign="top">Description</td>
                                        <td><textarea name="description" style="width:420px;height:80px;"><?= $requirement['description'] ?></textarea></td>
                                </tr>
                                <tr>
                                        <td>&nbsp;</td>
                                        <td><input type="submit" value="Submit" class="submit"></td>
                                </tr>
                        </table>
                        <input type="hidden" name="id" value="<?= $requirement['id'] ?>">
                        </form>
    
                        <br>
        
                        <?php
                } else {
                        echo "ERROR: Requirement not found";
                }
        }

        } else if(isset($_GET['action']) && $_GET['action']=='add'){
        PrintHeader();
            ?>
            
            <h1>Add Requirement</h1>
            <?php 
            if(Request('error')){
                echo('<h2 style="color:red;">'.Request('error').'</h2>');
            }
            ?>

                <p><div class="mod_addlink"><a href="<?= $_SERVER['PHP_SELF'] ?>">[back]</a></div></p>

                <br>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <table>
                        <tr>
                                <td valign="top">Type</td>
                                <td>
                                        <select name="requirement_type" style="width:220px">
                                                <option value="-1"<?php echo(Request('type')?'':' selected="selected"'); ?>>Select Type</option>
                                                <option value="minimum"<?php echo(Request('type')=='minimum'?' selected="selected"':''); ?>>Minimum Criteria</option>
                                                <option value="extra"<?php echo(Request('type')=='extra'?' selected="selected"':''); ?>>Exceeds Minimum Criteria</option>
                                        </select>
                                </td>
                        </tr>
                        <tr>
                                <td valign="top">Description</td>
                                <td><textarea name="description" style="width:420px;height:80px;"><?php echo(Request('description')?Request('description'):''); ?></textarea></td>
                        </tr>
                        <tr>
                                <td>&nbsp;</td>
                                <td><input type="submit" value="Submit" class="submit"></td>
                        </tr>
                </table>
                <input type="hidden" name="action" value="add">
                </form>

                <br>
                <?php
        } else {
        PrintHeader();

                if( Request('success') ) {
                        echo '<p><b>The requirement was updated successfully.</b></p>';
                }
            if( Request('added') ) {
                        echo '<p><b>The requirement was successfully added.</b></p>';
                }
            if( Request('removed') ) {
                        echo '<p><b>The requirement has been removed.</b></p>';
                }

                echo '<p>Choose a view requirement to edit or click the link below to add a new requirement.</p>';
                echo '<a href="'.$_SERVER['PHP_SELF'].'?action=add" id="new_requirement">';
                echo '    <img src="/common/silk/add.png" style="padding-right:4px;padding-left:2px;" width="16" height="16"/><span style="vertical-align: 20%;">Add New Requirement</span>';
        echo '</a><br /><br />';

                $header[] = array('text'=>'&nbsp;', 'width'=>20);
                $header[] = array('text'=>"Type", 'width'=>220);
                $header[] = array('text'=>"Description");
                $header[] = array('text'=>'&nbsp;', 'width'=>20);

                $T = new Chart();
                $T->SetHeadElement($header);
                $T->td_valign = "top";

                $requirements = $DB->MultiQuery("SELECT * FROM requirements WHERE requirement_type!='stakeholder' ORDER BY requirement_type DESC;");
                foreach( $requirements as $requirement ) {
                        $row = array();
                        $row[] = '<a href="'.$_SERVER['PHP_SELF'].'?id='.$requirement['id'].'"><img src="/common/silk/page_edit.png" height="16" width="16"></a>';
                        $row[] = $requirement['requirement_type']=='minimum'?'Minimum Criteria':'Exceeds Minimum Criteria';
                        $row[] = $requirement['description']; 
                        $row[] = '<a href="'.$_SERVER['PHP_SELF'].'?action=remove&id='.$requirement['id'].'"><img src="/common/silk/cross.png" height="16" width="16"></a>';

                        $T->AddRow($row);
                }

                $T->Output();

        }

        PrintFooter();
}


?>