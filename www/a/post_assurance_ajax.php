<?php
require_once('inc.php');
global $DB;
$response = array();
$userId = $_SESSION['user_id'];
$userQuery = "SELECT role_id FROM users_roles WHERE user_id=$userId and role_id=3;";
$results = $DB->MultiQuery($userQuery);
$is_director = false;
foreach($results as $row){
    $is_director = true;
} 
if(Request('action') =='assurance_form'){
    global $DB;
        $view_id = intval($_REQUEST['view_id']);
        
        ?>
        <div style="border: 1px solid rgb(119, 119, 119); margin-left: 15px; margin-right: 15px; background-color: white; padding: 15px;">
        <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
                <p>
                        Are you sure you want to add a new post view assurance agreement? 
                </p>
                <p>
                   Adding a new agreement will invalidate the existing assurance signatures and criteria.
                </p>
                <input type="submit" class="submit" value="Yes" />
                <input type="button" class="submit" value="No" onclick="chGreybox.close()" />
                <input type="hidden" name="view_id" value="<?=$view_id?>" />
                <input type="hidden" name="action" value="invalidate_assurance" />
                <input type="hidden" name="r" value="<?=Request('r')?>" />
        </form>
        </div>
        
        <?php
        return;
} else if(Request('action')=='invalidate_assurance'){
    global $DB;
        $view_id = intval($_REQUEST['view_id']);
        if($is_director){
        //$assurance = $DB->SingleQuery("SELECT id FROM assurances WHERE valid=TRUE AND vpost_view_id=".$view_id);
        
        //update the old assurance to invalidate existing signatures and criteria
        $DB->SingleQuery('UPDATE assurances SET valid=FALSE WHERE vpost_view_id='.$view_id);
        
        //Add a new assurance to allow for collecting of new signatures and criteria
        $data = array('vpost_view_id'=>$view_id,
                      'created_date'=>date('c'),
                      'valid'=>true);
        $DB->Insert('assurances',$data);
        }
        header('Location: '.Request('r'));
} else if(Request('action')=='publish_form'){
    global $DB;
        $view_id = intval($_REQUEST['view_id']);
        $view = $DB->SingleQuery('SELECT * FROM vpost_views WHERE id='.$view_id);
        
        ?>
        <div style="border: 1px solid rgb(119, 119, 119); margin-left: 15px; margin-right: 15px; background-color: white; padding: 15px;">
        <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
                <p>Are you sure you want to <?=$view['published'] == 0?'':'un'?>publish this view? 
                        <?= $view['published'] == 0 ? 
                                'Publishing this view will make it available to the public via embedded links.' :
                                'This view will no longer be visible in any web pages that embed it.' ?>
                </p>
                <input type="submit" class="submit" value="Yes" />
                <input type="button" class="submit" value="No" onclick="chGreybox.close()" />
                <input type="hidden" name="action" value="<?=$view['published'] == 0?'':'un'?>publish" />
                <input type="hidden" name="view_id" value="<?=$view_id?>" />
                <input type="hidden" name="r" value="<?=Request('r')?>" />
        </form>
        </div>
        
        <?php
        return;
} else if(Request('action')=='publish'){
    //publish the sucka!
    if(CanPublishView(Request('view_id'))){
        $DB->MultiQuery('UPDATE vpost_views SET published=TRUE WHERE id='.intval(Request('view_id')));
    }
    header('Location: '.Request('r'));
} else if(Request('action')=='unpublish'){
    //publish the sucka!
    if(CanPublishView(Request('view_id'))){
        $DB->MultiQuery('UPDATE vpost_views SET published=FALSE WHERE id='.intval(Request('view_id')));
    }
    header('Location: '.Request('r'));
} else {   
    $view_id =  (preg_match("/\d+/",$_POST['view_id'])) ? $_POST['view_id'] : FALSE; 
    $req_id =  (preg_match("/\d+/",$_POST['requirement_id'])) ? $_POST['requirement_id'] : FALSE; 
    
    if ($view_id === FALSE or $req_id=== FALSE or !$is_director) {
        $response['rsp'] = FALSE;
        echo json_encode($response);
    } else {
    
        $sql = "SELECT id FROM assurances WHERE valid = TRUE AND vpost_view_id=".$view_id.';';
        $results = $DB->MultiQuery($sql);
        foreach($results as $row){
            
            $a_id = $row['id'];
            $sql = "SELECT requirement_id FROM assurance_requirements_ct WHERE requirement_id=".$req_id.' AND assurance_id = ' .$a_id .';';
            $check = $DB->MultiQuery($sql);
            $response['rsp'] = FALSE;
            if (count($check)> 0) {
                $sql = 'DELETE FROM assurance_requirements_ct WHERE requirement_id='.$req_id.' AND assurance_id = ' .$a_id .';';
                $DB->MultiQuery($sql);
                $response['rsp'] = TRUE;
                break;
                
            } else {
                $sql = 'INSERT INTO assurance_requirements_ct (assurance_id, requirement_id, user_id, date_signed) VALUES('.$a_id.','.$req_id.','.$userId.',NOW());';
                $DB->MultiQuery($sql);
                $response['rsp'] = TRUE;
                break;
            }
        }
        echo json_encode($response);
    }
}