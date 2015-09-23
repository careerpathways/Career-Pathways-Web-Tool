<?php
require_once('Asset_Manager.php');

if(isset($_GET['school_id'])){
	$asset_list = Asset_Manager::get_assets(array('school_id'=> (int) $_GET['school_id']));
}

//Return a json list of assets
header('Content-type: application/json');
echo json_encode($asset_list);
