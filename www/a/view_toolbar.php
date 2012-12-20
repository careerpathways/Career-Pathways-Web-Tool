<?php
if(isset($_REQUEST['id']) && !empty($_REQUEST['id'])):
global $DB;
global $SITE;
$view_id = intval($_REQUEST['id']);
$view = $DB->SingleQuery("SELECT * FROM vpost_views WHERE id=".$view_id);
$userId = $_SESSION['user_id'];
$userQuery = "SELECT role_id FROM users_roles WHERE user_id=$userId and role_id=3;";
$results = $DB->MultiQuery($userQuery);
$is_director = false;
$is_view_page = $_SERVER['PHP_SELF']=="/a/post_views.php";
//print($_SERVER['PHP_SELF']);
foreach($results as $row){
    if(isset($row)){
        $is_director = true;
    }
} 
?>
<div id="toolbar">
        <div id="toolbar_header"></div>
        <div id="toolbar_content">
                <div style="margin-bottom:10px">
                <!-- //@TODO generate PDF Print Feature -->
                        
                <?php if(CanPublishView($_REQUEST['id']) && $is_view_page){?>
                    <a href="javascript:publishViewPopup(<?=$_REQUEST['id']?>, <?=$view['published']?>, '<?=$_SERVER['PHP_SELF']?>?id=<?=$view_id?>')" id="publishLink" class="noline"><?= SilkIcon('report_go.png') ?> <?=$view['published'] == 0?'':'un'?>publish this view</a>
                    <br />
                <?php }

                //POST Assurance only
                if ($SITE->hasFeature('post_assurances')){?>
                    <a href="post_assurance_pdf.php?view_id=<?=$view_id?>" target="_blank" class="noline"><?= SilkIcon('page_white_acrobat.png') ?> assurance PDF</a>
                    <br />
                    <?php if($is_director){?>
                    <a href="javascript:viewAssurancePopup(<?=$_REQUEST['id']?>, '<?=$_SERVER['PHP_SELF']?>?id=<?=$view_id?>')" class="noline"><?= SilkIcon('add.png') ?> add assurance agreement</a>
                    <?php } 
                }?>
                </div>
        </div>
</div>
<?php 
endif;
?>