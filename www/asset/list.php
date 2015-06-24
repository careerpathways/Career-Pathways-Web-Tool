<?php
include("inc.php");
require_once('Asset_Manager.php');

$asset_list = Asset_Manager::get_assets(array('school_id'=>$_SESSION['school_id']));
var_dump($asset_list);
