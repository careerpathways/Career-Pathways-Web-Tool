<?php
if(!defined('DIR_CORE')){
	include("inc.php");	
}

/**
 * Contains logic related to Asset Manager permissions (a.k.a. Image Library)
 */
class Asset_Permission
{
	/**
	 * Get a user permission level.
	 * 
	 * @param  string $name name as seen in DB eg: 'State Admin'
	 * @return int level eg: 127
	 */
	public static function get_user_level($name)
	{
		global $DB;
		$user_data = $DB->SingleQuery('SELECT * FROM admin_user_levels WHERE name = "' . $name . '"');
		if (isset($user_data['level'])) {
			return (int) $user_data['level'];
		}
	}
	/**
	 * Determine if a user can create an asset in a given bucket.
	 * 
	 * @param  int $user_id Id of the user trying to create a new asset.
	 * @param  int $bucket_id Bucket where user is trying to create new asset.
	 * @return boolean True if user can create an asset in the bucket.
	 */
	public static function can_create($user_id, $bucket_id)
	{
		global $DB;
		
		if($bucket_id == 0){
			return true; //everyone can create in site-wide
		}

		$user = $DB->SingleQuery('SELECT * FROM users WHERE id = ' . $user_id);
		if(($user['school_id'] == $bucket_id) || ($user['user_level'] >= self::get_user_level('State Admin'))) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if a given user can delete a specific asset.
	 * 
	 * @param  int $user_id User id of the user attempting to delete the asset.
	 * @param  int $asset_id Id of the asset being deleted.
	 * @return boolean True if user can delete the asset.
	 */
	public static function can_delete($user_id, $asset_id)
	{
		global $DB;

		$user = $DB->SingleQuery('SELECT * FROM users WHERE id = ' . $user_id);
		$asset = $DB->SingleQuery('
			SELECT 
				assets.*,
				assets_school_ids.school_id as asset_bucket_id,
				users.school_id as asset_creator_school_id
			FROM assets 
			LEFT JOIN assets_school_ids
				ON assets.id = assets_school_ids.asset_id
			LEFT JOIN users
				ON assets.created_by = users.id
			WHERE assets.id = ' . $asset_id);
		

		if($user['user_level'] >= self::get_user_level('State Admin')){
			return true; //admins can delete anything
		}
		if ($user['id'] == $asset['created_by']) {
			return true; //users can delete own assets
		}
		//if is org admin and asset is in same school, can delete.
		if (($user['school_id'] == $asset['asset_creator_school_id']) && $user['user_level'] >= self::get_user_level('Webmaster')) {
			return true;
		}

		return false;
	}
	
	/**
	 * Determine if a user can modify a given asset.
	 * 
	 * @param  int $user_id User id of the user attempting to modify the asset.
	 * @param  int $asset_id Id of the asset being modified.
	 * @return boolean True if user can modify the asset.
	 */
	public static function can_modify($user_id, $asset_id)
	{
		return self::can_delete($user_id, $asset_id); //for now, if user can delete, user can modify.
	}
}
