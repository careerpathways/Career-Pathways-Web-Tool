<?php
chdir("..");
include("inc.php");

if(!isset($_REQUEST['view_id'])) {
    die("<p>No View specified!</p>");
}

$id = $_REQUEST['view_id'];
$post = $_REQUEST['action'] == 'copy_view_post';
$view = $DB->SingleQuery('SELECT * FROM vpost_views WHERE id='.$id);

if($post) {
    if(!isset($_REQUEST['view_title'])) {
        die(json_encode(array('rsp'=>0, 'msg'=>'Please enter a valid title.')));
    } else if(strlen($_REQUEST['view_title']) > 200) {
        die(json_encode(array('rsp'=>0, 'msg'=>'The title may not be longer than 200 characters.')));
    }

    $query = array(
        'school_id'=>$view['school_id'],
        'code'=>$view['code'],
        'name'=>$_REQUEST['view_title'],
        'date_created'=>$DB->SQLDate(),
        'last_modified'=>$DB->SQLDate(),
        'created_by'=>$_SESSION['user_id'],
        'last_modified_by'=>$_SESSION['user_id']
    );
    $result = $DB->Insert('vpost_views', $query);
    if($result == '') {
        die(json_encode(array('rsp'=>0, 'msg'=>'There was a problem connecting to the database:\nNo identifier returned.')));
    } else {
        $new_view_id = $result;
        $links = $DB->MultiQuery('SELECT * FROM vpost_links WHERE vid = ' . $id);
        foreach($links as $link) {
            $query = array(
                'vid'=>$new_view_id,
                'post_id'=>$link['post_id'],
                'tab_name'=>$link['tab_name'],
                'sort'=>$link['sort']
            );
            $DB->Insert('vpost_links', $query);
        }
        echo json_encode(array('rsp'=>1, 'msg'=>'Success!', 'newid'=>$new_view_id));
    }
} else { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Copy View &bull; Pathways</title>
        <script type="text/javascript" src="/files/js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript">
        $(document).ready(function() {
            $('#ok').click(function(e) {
                e.preventDefault();
                var $form = $('#copy-view-popup-form');
                $.post($form.attr('action'), $form.serializeArray(), function(obj) {
                    if(obj.rsp) {
                    	window.parent.location = '/a/post_views.php?id=' + obj.newid;
                    } else {
                        alert(obj.msg);
                    }
                }, 'json');
            });
            $('#cancel').click(function(e) {
                e.preventDefault();
                window.parent.chGreybox.close();
            });
        });
        </script>
    </head>
    <body>
        <div style="margin-left: 5px;">
            <form id="copy-view-popup-form" action="/a/copy_view_popup.php?action=copy_view_post&view_id=<?=$id?>" method="post">
                <div class="input-container">
                    <input type="text" name="view_title" size="40" value="Copy of <?=$view['name']?>" />
                </div>
                <div class="input-container">
                    <input type="submit" id="ok" value="OK" />
                    <input type="submit" id="cancel" value="Cancel"/>
                </div>
            </form>
        </div>
    </body>
</html>
<?php } ?>