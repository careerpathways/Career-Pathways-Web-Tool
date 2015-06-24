<?php
if(!defined('DIR_CORE')){
	include("inc.php");	
}

class Asset_Manager
{

	public static function get_asset($filename)
	{
		global $SITE;
		$assetPath = $SITE->asset_path();
		return $assetPath . $filename;
	}


	public static function get_assets($options)
	{
		if(isset($options['school_id']) && $options['school_id'] > 0){
			//return only assets for this school
			$assets = array('school1', 'school2');
		} elseif (isset($options['all']) && $options['all'] === true){
			//todo return all
			$assets = array();
		} else {
			//return no assets
			$assets = array();	
		}
		
		return $assets;
	}

}
