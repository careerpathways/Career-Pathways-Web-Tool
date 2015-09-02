<?php
require_once('Asset_Manager.php');

if(isset($_SESSION['school_id'])){
	$asset_list = Asset_Manager::get_assets(array('school_id'=>$_SESSION['school_id']));
} else {
	//return none
	$asset_list = array();
}

header('Content-type: application/json');
echo json_encode($asset_list);
