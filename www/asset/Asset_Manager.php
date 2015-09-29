<?php
if(!defined('DIR_CORE')){
	include("inc.php");	
}

define('URL_ASSET', '/asset/');

class Asset_Manager
{
	/**
	 * Get a single asset file.
	 * @param  int $asset_id Id of the asset.
	 * @return string The asset object.
	 */
	public static function get_asset($asset_id, $include_deleted = false)
	{
		global $SITE, $DB;
		$query = 'SELECT * 
			FROM assets
			WHERE id = '.$asset_id;
		if(!$include_deleted){ //don't include deleted
			$query .= ' AND active = 1';
		}
		$asset = $DB->SingleQuery($query);
		return $asset;
	}

	/**
	 * Get a single asset file.
	 * @param  string $filename Filename of the asset file.
	 * @return string The path to the asset file.
	 */
	public static function get_asset_by_filename($filename)
	{
		global $SITE, $DB;
		$asset = $DB->SingleQuery('SELECT * 
			FROM assets
			WHERE file_name = "'.$filename.'"
			AND active = 1
		');
		if($asset){
			$assetPath = $SITE->asset_path();
			return $assetPath . $filename;	
		}
		
	}

	public static function delete_asset($asset_id)
	{
		global $DB;
		$asset = $DB->SingleQuery('SELECT * 
			FROM assets
			LEFT JOIN assets_school_ids on assets.id = assets_school_ids.asset_id
			WHERE id = "'.$asset_id.'"
		');
		if(CanEditOtherSchools() || $asset['school_id'] == $_SESSION['school_id']){
			$DB->Query('UPDATE assets
				SET active=0
				WHERE id = '.$asset_id);
			return array(
				'status'=>'success',
				'message'=>'Successfully deleted asset with id ' . $asset_id
			);
		} else {
			return array(
				'status'=>'failure',
				'message'=>'You do not have permission to delete asset with id ' . $asset_id
			);
		}
	}

	/**
	 * Get a list of items using the asset specified.
	 * @param  int $asset_id Id of the asset to look for.
	 * @return array Number of, and list of ids of drawings that use the asset.
	 */
	public static function check_use($asset_id)
	{
		global $DB;
		//Adding slashes to avoid mysql syntax errors.
		$tail = addslashes('data-asset-id="' . $asset_id . '"'); // e.g. data-asset-id=\"12\"
		
		$roadmap_drawings = $DB->MultiQuery('SELECT 
				"roadmap_drawing" as type,
				drawings.parent_id as roadmap_drawing_main_id,
				objects.drawing_id as roadmap_drawing_version_id,
				objects.id as objects_id,
				COUNT(objects.id) as times_used_within_version
				
			FROM objects
			LEFT JOIN drawings
				ON objects.drawing_id = drawings.id
			WHERE objects.content 
				LIKE "%'.$tail.'%" 
			GROUP BY roadmap_drawing_version_id
			ORDER BY roadmap_drawing_main_id ASC');

		$post_drawings = $DB->MultiQuery('SELECT 
				"post_drawing" as type,
				post_drawings.parent_id as post_drawing_main_id,
				post_drawings.id as post_drawing_version_id,
				post_cell.id as post_cell_id,
				COUNT(post_cell.id) as times_used_within_version
			FROM post_cell 
				LEFT JOIN post_drawings on post_cell.drawing_id = post_drawings.id
			WHERE content 
				LIKE "%'.$tail.'%"
			GROUP BY post_drawing_version_id
			ORDER BY post_drawing_main_id ASC');
		$res = array_merge($roadmap_drawings, $post_drawings);
		$asset_use = array(
			'number_of_drawings_using' => count($res),
			'usages' => $res
		);
		return $asset_use;
	}

	/**
	 * Get a list of assets.
	 * @param  array $options
	 * @return array
	 */
	public static function get_assets($options)
	{
		global $SITE, $DB;
		
		if(isset($options['school_id']) && $options['school_id'] >= 0){
			//return only assets for this school
			$_query = 'SELECT a.id, a.file_name, asi.school_id FROM assets_school_ids asi
			LEFT JOIN assets a
			ON a.id = asi.asset_id
			WHERE asi.school_id = ' . (int) $options['school_id'] . '
			AND active = true
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
	 * It's basically a school id and a school name, but there is an additional one called "Site Wide"
	 * 
	 * Results are based on user permission level.
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
		$query = 'SELECT school_id, school_name FROM assets_school_ids
			LEFT JOIN schools 
				ON assets_school_ids.school_id = schools.id
			WHERE school_id > 0'; //don't get school id 0, which is null. We add that manually (below, in this function)
		//Restrict the query if the user is not allowed to edit other schools (a.k.a buckets).
		if(!CanEditOtherSchools()){
			if(isset($_SESSION['school_id']) && $_SESSION['school_id'] > 0){
				$query .= ' WHERE school_id = "'.$_SESSION['school_id'].'"';
			}
		}
		$query .= ' GROUP BY school_id ORDER BY school_name ASC';
		$buckets = $DB->MultiQuery($query);

		//everyone can see this bucket
		array_unshift($buckets, $site_wide_bucket);
		
		//Assign permissions to each bucket.
		foreach($buckets as &$bucket){
			if(IsAdmin() || $bucket['school_id'] == $_SESSION['school_id']){
				$bucket['userCanDelete'] = true;
				$bucket['userCanReplace'] = true;	
			}
		}
		return $buckets;
	}
}
