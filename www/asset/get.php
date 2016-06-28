<?php
/**
 * Check the use of an asset, either in Roadmap drawings or elsewhere.
 */
if(!defined('DIR_CORE')){
	include("inc.php");	
}
require_once('Asset_Manager.php');

//Required args
if(!Request('asset_id')){
	$response = array('status'=>'failure','message'=>'Please provide an asset_id.');
	require('template/json.php');
	die();
}

$response = Asset_Manager::get_asset((int) Request('asset_id'));
require('template/json.php');
die();
