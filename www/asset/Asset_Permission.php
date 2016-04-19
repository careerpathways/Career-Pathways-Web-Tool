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

		if(($user['school_id'] == $bucket_id) || ($user['user_level'] == USER_ADMIN)) {
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
		$asset = $DB->SingleQuery('SELECT * FROM assets LEFT JOIN assets_school_ids on assets.id = assets_school_ids.asset_id WHERE assets.id = ' . $asset_id);
		
		if($user['user_level'] == USER_ADMIN){
			return true; //admins can delete anything
		}
		if ($user['id'] == $asset['created_by']) {
			return true; //users can delete own assets
		}
		//if is org admin and asset is in same school, can delete.
		if (($user['school_id'] == $asset['school_id']) && $user['user_level'] == USER_STAFF) {
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
