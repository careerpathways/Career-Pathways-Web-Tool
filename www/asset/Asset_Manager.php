<?php
if(!defined('DIR_CORE')){
	include("inc.php");	
}

class Asset_Manager
{
	/**
	 * Get a single asset file.
	 * @param  string $filename Filename of the asset file.
	 * @return string The path to the asset file.
	 */
	public static function get_asset($filename)
	{
		global $SITE;
		$assetPath = $SITE->asset_path();
		return $assetPath . $filename;
	}

	public static function delete_asset($asset_id)
	{
		//TODO check user permissions. Either has to be a member of this school, or state admin.
	}

	/**
	 * Get a list of assets.
	 * @param  array $options
	 * @return array
	 */
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
		foreach ($assets as &$a) {
			$a['imgSrc'] = getBaseUrl() . '/asset/' . $a['file_name'];
		}
		return $assets;
	}

	/**
	 * Get a list of asset buckets for the given user.
	 * A bucket is an id and a label, used to group assets.
	 * Based on user permission level.
	 * @param  object $user
	 * @return array
	 */
	public static function get_buckets()
	{
		global $DB;
		$buckets = array();
		$site_wide_bucket = array(
			'school_id' => 0, //there is no school with this id, it's used for the site-wide bucket.
			'school_name' => 'Site Wide'
		);
		$r = $DB->SingleQuery('SELECT * FROM admin_user_levels WHERE name ="State Admin"');
		$admin_level = $r['level']; //usually an int like 127
		$query = 'SELECT school_id, school_name FROM assets_school_ids
			LEFT JOIN schools 
				on assets_school_ids.school_id = schools.id';
		if(isset($_SESSION['user_level']) && $_SESSION['user_level'] < $admin_level){
			if(isset($_SESSION['school_id']) && $_SESSION['school_id'] > 0){
				$query .= ' WHERE school_id = "'.$_SESSION['school_id'].'"';
			}
		}
		$query .= ' GROUP BY school_id ORDER BY school_name ASC';
		$buckets = $DB->MultiQuery($query);
		array_unshift($buckets, $site_wide_bucket); //everyone can see this bucket
		return $buckets;
	}
}
