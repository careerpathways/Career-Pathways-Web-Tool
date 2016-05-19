<?php
if(!defined('DIR_CORE')){
	include("inc.php");	
}
include("simple_html_dom.php");
include("Asset_Permission.php");

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
		$asset['imgSrc'] = self::make_asset_url($asset['file_name']);
		$asset['userCanModify'] = Asset_Permission::can_modify($_SESSION['user_id'], $asset['id']);
		$asset['userCanDelete'] = Asset_Permission::can_delete($_SESSION['user_id'], $asset['id']);
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
		$drawingsUsingAsset = self::check_use($asset_id);
		if($drawingsUsingAsset['number_of_drawings_using'] == 0 && Asset_Permission::can_delete($_SESSION['user_id'], $asset_id)){
			$DB->Query('UPDATE assets
				SET active=0
				WHERE id = '.$asset_id);
			return array(
				'status'=>'success',
				'message'=>'Image successfully deleted.'
			);
		} else {
			return array(
				'status'=>'failure',
				'message'=>'You do not have permission to delete this image, or this image is in use.'
			);
		}
	}

	/**
	 * Move an asset to a new school
	 * 
	 * @param  int $asset_id      Id of the asset to move.
	 * @param  int $bucket_id     Id of the bucket to move the asset to.
	 * @return array status array
	 */
	public static function move_asset($asset_id, $bucket_id)
	{
		global $DB;
		if(isset($asset_id) && is_int($asset_id) && $asset_id >= 0
			&& isset($bucket_id) && is_int($bucket_id) && $bucket_id >= 0) {
			
			$_asset = $DB->Query('SELECT * FROM assets WHERE id = '.$asset_id);

			// User is allowed to move an asset if they can delete the original, and they can write to destination bucket.
			if (Asset_Permission::can_delete($_SESSION['user_id'], $asset_id) && Asset_Permission::can_create($_SESSION['user_id'], $bucket_id)) {
				$DB->Query('UPDATE assets_school_ids
					SET school_id='.$bucket_id.'
					WHERE asset_id = '.$asset_id);
				$asset = $DB->SingleQuery('SELECT * 
					FROM assets
					LEFT JOIN assets_school_ids on assets.id = assets_school_ids.asset_id
					LEFT JOIN schools on schools.id = assets_school_ids.school_id
					WHERE assets.id = "'.$asset_id.'"
				');
				if($asset['school_id'] == 0){
					$bucket_name = 'Site Wide';
				} else {
					$bucket_name = $asset['school_name'];
				}
				return array(
					'status'=>'success',
					'message'=>'Image successfully moved to ' . $bucket_name
				);	
			} else {
				return array(
					'status'=>'failure',
					'message'=>'You do not have permission to move images to the selected bucket.'
				);
			}
		} else {
			return array(
				'status'=>'failure',
				'message'=>'Bad asset_id or bucket_id.'
			);
		}
	}

	/**
	 * Get a list of items using the asset specified.
	 * 
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
	 * 
	 * @param  array $options
	 * @return array
	 */
	public static function get_assets($options)
	{
		global $SITE, $DB;
		
		if(isset($options['school_id']) && $options['school_id'] >= 0){
			//return only assets for this school
			$_query = 'SELECT a.*, asi.school_id, u.first_name, u.last_name, s.school_name 
				FROM assets_school_ids asi
					LEFT JOIN assets a
						ON a.id = asi.asset_id
					LEFT JOIN users u
						ON u.id = a.created_by
					LEFT JOIN schools s
						ON s.id = asi.school_id 
				WHERE asi.school_id = ' . (int) $options['school_id'] . '
				AND a.active = true
				ORDER BY a.date_created DESC';
		} else {
			//return all assets
			$_query = 'SELECT * FROM assets';
		}
		$assets = $DB->MultiQuery($_query);
		foreach ($assets as &$a) {
			$a['imgSrc'] = self::make_asset_url($a['file_name']);
			$a['userCanModify'] = Asset_Permission::can_modify($_SESSION['user_id'], $a['id']);
			$a['userCanDelete'] = Asset_Permission::can_delete($_SESSION['user_id'], $a['id']);
		}
		return $assets;
	}


	public static function make_asset_url($assetFileName)
	{
		return getBaseUrl() . '/asset/' . $assetFileName;
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

		//everyone can see this bucket
		$buckets[] = $site_wide_bucket;

		//Add this user's school bucket
		if(isset($_SESSION['school_id']) && $_SESSION['school_id'] > 0){
			$user_school_bucket = $DB->SingleQuery('SELECT id as school_id, school_name FROM schools WHERE id = ' . (int) $_SESSION['school_id']);
			$buckets[] = $user_school_bucket;
		}

		//Get buckets for other schools that have assets
		if(CanEditOtherSchools()){
			//don't get school id 0
			$query = 'SELECT id AS school_id, school_name 
				FROM schools 	
				WHERE id > 0';
			//exclude this user's school (included above)
			if(isset($_SESSION['school_id']) && $_SESSION['school_id'] > 0){
				$query .= ' AND id != ' . (int) $_SESSION['school_id'];
			}
			$query .= ' GROUP BY id ORDER BY school_name ASC';
			$other_schools_buckets = $DB->MultiQuery($query);
			$buckets = array_merge($buckets, $other_schools_buckets);
		}
		
		//Assign permissions to each bucket.
		foreach($buckets as &$bucket){
			//User can create assets in this bucket, or not.
			$bucket['userCanCreate'] = Asset_Permission::can_create($_SESSION['user_id'], $bucket['school_id']);

			if(!IsAdmin() && $bucket['school_id'] == $_SESSION['school_id']){
				$bucket['isOwn'] = true; //If this bucket is the user's "school". Admins default to site-wide as their own bucket.
			}
		}
		return $buckets;
	}

	/**
	 * Replace an asset with another one.
	 * 
	 * @param  int $assetIdOriginal
	 * @param  int $assetIdNew
	 * @return array $result Details about how things went.
	 */
	public static function replace_asset($assetIdOriginal, $assetIdNew)
	{
		$result = array(
			'status'=>'',
			'message'=>'',
			'operation'=> __FUNCTION__,
			'details'=>array()
		);

		//Permit user to replacing the original asset if they are able to "delete" it.
		if(!Asset_Permission::can_delete($_SESSION['user_id'], $assetIdOriginal)){
			$result['status'] = 'not-modified';
			$result['message'] = 'Our apologies, it appears you do not have permission to overwrite that asset.';
			return $result;
		}

		$drawings = self::check_use($assetIdOriginal);

		if($drawings['number_of_drawings_using'] == 0){
			$result['status'] = 'not-modified';
			$result['message'] = "There are no Roadmap or POST Drawings using that image. If you don't wish to keep the image, delete the image instead.";
			return $result;
		}
		
		$original_asset = self::get_asset($assetIdOriginal);
		$new_asset = self::get_asset($assetIdNew);

		foreach($drawings['usages'] as $d){
			if($d['type'] === 'roadmap_drawing'){
				$result['details'][] = self::replace_asset_in_object($d['objects_id'], $original_asset, $new_asset);
			} elseif($d['type'] === 'post_drawing'){
				$result['details'][] = self::replace_asset_in_post_cell($d['post_cell_id'], $original_asset, $new_asset);
			}
		}

		$result['status'] = 'success';
		$result['message'] = 'Updated ' . count($result['details']) . ' Roadmap/POST Drawings.';

		return $result;
	}

	/**
	 * Replace an asset in a Roadmap Object
	 * @param  int $objectId Object Id in the object table in the db.
	 * @param  array $assetOriginal
	 * @param  array $assetNew
	 * @return array $result object
	 */
	private static function replace_asset_in_object($objectId, $assetOriginal, $assetNew)
	{
		global $DB;
		$result = array(
			'status' => '',
			'message' => '',
			'type' => 'roadmap/object',
			'operation'=> __FUNCTION__,
			'object_id' => $objectId
		);

		$object = $DB->SingleQuery('SELECT * FROM objects WHERE id = ' . $objectId);
		if(!isset($object['content'])){
			$result['status'] = 'failure';
			$result['message'] = 'Roadmap/object has no content.';
			return $result;
		}
		$c = unserialize($object['content']);
		$c['config']['content'] = self::replace_asset_in_html($c['config']['content'], $assetOriginal, $assetNew);
		$c['config']['content_html'] = self::replace_asset_in_html($c['config']['content_html'], $assetOriginal, $assetNew);

		$DB->Update('objects',array('content'=>serialize($c)), $objectId);
		
		$result['status'] = 'success';
		$result['message'] = 'Updated roadmap object id ' . $objectId;

		return $result;
	}

	/**
	 * Replace an asset in a Post Drawing Cell
	 * @param  int $postCellId Cell ID in post_cell table in the db.
	 * @param  array $assetOriginal
	 * @param  array $assetNew
	 * @return array $result object
	 */
	private static function replace_asset_in_post_cell($postCellId, $assetOriginal, $assetNew)
	{
		global $DB;
		$result = array(
			'status' => '',
			'message' => '',
			'type' => 'post/post_cell',
			'operation'=> __FUNCTION__,
			'post_cell_id' => $postCellId
		);

		$c = $DB->SingleQuery('SELECT * FROM post_cell WHERE id = ' . $postCellId);
		
		if(!isset($c['content'])){
			$result['status'] = 'failure';
			$result['message'] = 'POST Drawing/post_cell has no content.';
			return $result;
		}
		
		$c['content'] = self::replace_asset_in_html($c['content'], $assetOriginal, $assetNew);
		
		$DB->Update('post_cell',array('content'=>$c['content']), $postCellId);

		$result['status'] = 'success';
		$result['message'] = 'Updated post cell id ' . $postCellId;
		
		return $result;
	}

	/**
	 * Replaces an asset image src and data-asset-id in an html string.
	 * @param  string $htmlString
	 * @param  array $assetOriginal
	 * @param  array $assetNew
	 * @return string Same as $htmlString but with image src and data-asset-id updated. Note: line breaks are removed.
	 */
	private static function replace_asset_in_html($htmlString, $assetOriginal, $assetNew){
		$htmlObj = str_get_html($htmlString);
		foreach($htmlObj->find('img') as $e){
			if($e->getAttribute('data-asset-id') == $assetOriginal['id']){
	       		$e->setAttribute('src', self::make_asset_url($assetNew['file_name']));
	       		$e->setAttribute('data-asset-id', $assetNew['id']);
	       	}
		}
		return (string) $htmlObj;
	}
}
