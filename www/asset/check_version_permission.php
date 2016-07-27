<?php
/**
 * Checks if user can edit drawing version by id.
 */

include('inc.php');

$can_edit_version = CanEditVersion($_GET['version_id'], 'post', false);

header('Content-type: application/json');
echo json_encode($can_edit_version);
