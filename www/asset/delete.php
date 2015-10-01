<?php
/**
 * Delete an asset.
 * User must have permission.
 */
if(!defined('DIR_CORE')){
	include("inc.php");	
}
require_once('Asset_Manager.php');

$response = Asset_Manager::delete_asset((int) Request('asset_id'));
header('Content-type: application/json');
echo json_encode($response);
