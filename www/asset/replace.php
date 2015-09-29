<?php
/**
 * Return a list of buckets available to the user.
 * A user's permission level dictates which buckets they have access to.
 */
if(!defined('DIR_CORE')){
	include("inc.php");	
}
require_once('Asset_Manager.php');

$result = Asset_Manager::replace_asset((int) $_GET['asset_id_original'], (int) $_GET['asset_id_new']);
require('template/json.php');
die();
