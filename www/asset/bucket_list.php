<?php
/**
 * Return a list of buckets available to the user.
 * A user's permission level dictates which buckets they have access to.
 */
if(!defined('DIR_CORE')){
	include("inc.php");	
}
require_once('Asset_Manager.php');

$bucket_list = Asset_Manager::get_buckets();

//Return a json list of buckets
header('Content-type: application/json');
echo json_encode($bucket_list);
