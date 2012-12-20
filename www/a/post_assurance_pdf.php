<?php
chdir("../");
include("inc.php");

global $DB;
if( strtolower($_SERVER['REQUEST_METHOD']) == "post" && Request('assurance')) {
    
    $assurance = explode('---',Request('assurance'));
    $assurance_id = intval($assurance[0]);
    $view_id = intval($assurance[1]);

    //Send the request to the pdf engine
    header("Location: http://".$_SERVER['SERVER_NAME']."/pdf/post-view-assurance/". $view_id ."/". $assurance_id .".pdf");

} else {
    
    PrintHeader();
    
    // %h:%i:%s
    $assurances = $DB->MultiQuery("SELECT assurances.id, 
                                      DATE_FORMAT(assurances.last_signed_date,'%m/%d/%Y') as 'last_signed_date', 
                                      assurances.vpost_view_id, 
                                      vpost_views.name
                                   FROM assurances 
                                   JOIN vpost_views on assurances.vpost_view_id=vpost_views.id 
                                   WHERE vpost_view_id='".intval(Request('view_id'))."' 
                                   ORDER BY assurances.created_date DESC, assurances.id DESC");
    //die(print_r($assurances,true));
    echo '<h1>Assurances For '.$assurances[0]['name'].'</h1>';
    echo '<p>Choose an assurance agreement.</p>';
    echo '<p>The date reflects the most recent signature on record for this assurance.</p>';
    echo '<form name="form_assurance" id="form_assurance" action="'.$_SERVER['PHP_SELF'].'" method="post" target="_blank">';
    echo '<select name="assurance">';
    foreach($assurances as $assurance){
        $last_signed = isset($assurance['last_signed_date'])?$assurance['last_signed_date']:"Unsigned";
        echo '<option value="'.$assurance['id'].'---'.$assurance['vpost_view_id'].'---'.$assurance['name'].'">'.$last_signed.'</option>';
    }
    echo '</select>';
    echo '<input type="submit" name="select_assurance" id="select_assurance" value="Get PDF" />';
    echo '</form><br /><br />';

    PrintFooter();
    
}