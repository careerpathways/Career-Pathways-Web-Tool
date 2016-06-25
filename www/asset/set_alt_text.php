<?php
/**
 * Set Alt text of an image.
 * User must have permission.
 */

require_once('Asset_Manager.php');



if( isset( $_GET['asset_id'] ) && isset( $_GET['alt_text'] ) ){
	$is_successful = Asset_Manager::set_asset_alt_text($_GET['asset_id'], $_GET['alt_text']);
}


header('Content-type: application/json');
echo json_encode($is_successful);
