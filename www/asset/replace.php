<?php
/**
 * Return a list of buckets available to the user.
 * A user's permission level dictates which buckets they have access to.
 */
if(!defined('DIR_CORE')){
	include("inc.php");	
}
require_once('Asset_Manager.php');

//Required args
if(!Request('asset_id_original') || !Request('asset_id_new')){
	$response = array('status'=>'failure','message'=>'Please provide asset_id_original and asset_id_new.');
	require('template/json.php');
	die();
}

$response = Asset_Manager::replace_asset((int) Request('asset_id_original'), (int) Request('asset_id_new'));

require('template/json.php');
die();
