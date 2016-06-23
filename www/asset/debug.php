<?php
/**
 * Return a list of buckets available to the user.
 * A user's permission level dictates which buckets they have access to.
 */
if(!defined('DIR_CORE')){
	include("inc.php");	
}
require_once('Asset_Permission.php');

$resultOrWhatever = self::get_user_level('Webmaster');

var_dump($resultOrWhatever);
