<?php
/**
 * Move an asset to a different bucket.
 * User must have permission.
 */
if(!defined('DIR_CORE')){
	include("inc.php");	
}
require_once('Asset_Manager.php');

$response = Asset_Manager::move_asset((int) Request('asset_id'), (int) Request('bucket_id'));
header('Content-type: application/json');
echo json_encode($response);
