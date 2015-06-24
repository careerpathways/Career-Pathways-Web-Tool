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
		global $SITE, $DB;
		
		if(isset($options['school_id']) && $options['school_id'] > 0){
			//return only assets for this school
			$_query = 'SELECT a.id, a.file_name, asi.school_id FROM assets_school_ids asi
			LEFT JOIN assets a
			ON a.id = asi.asset_id
			WHERE asi.school_id = ' . (int) $options['school_id'] . '
			ORDER BY a.date_created DESC';
		} else {
			//return all assets
			$_query = 'SELECT * FROM assets';
		}
		$assets = $DB->MultiQuery($_query);
		return $assets;
	}

}
