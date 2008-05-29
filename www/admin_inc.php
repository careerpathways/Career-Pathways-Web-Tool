<?php


// abstractions to work with USER_ definitions in general.inc.php
function IsAdmin() {
	return $_SESSION['user_level'] >= USER_ADMIN;
}
function IsSchoolAdmin() {
	return $_SESSION['user_level'] >= USER_STAFF;
}
function IsStaff() {
	return $_SESSION['user_level'] >= USER_MEMBER;
}




function UserCanEditCategory($cat_id) {
global $DB;
	if( IsAdmin() ) {
		return TRUE;
	} else {
		if( $_SESSION['user_level'] >= USER_MEMBER ) {
			if( !is_numeric($cat_id) ) {
				$DB->Query("SELECT id FROM admin_module WHERE name = '$cat_id'");
				$line = $DB->NextRecord();
				$cat_id = $line['id'];
			}
			$sql = "SELECT * FROM admin_level_module WHERE level<=".$_SESSION['user_level']." AND module_id=$cat_id";
			return (count($DB->MultiQuery($sql)) > 0);
		} else {
			return FALSE;
		}
	}
}


function GetCategoriesForUser($id, $where="", $get_inverse=FALSE) {
global $DB;

	if( $get_inverse == TRUE ) {
	// get all categories the user is not in




	} else {
		$user = $DB->SingleQuery("SELECT user_level FROM users WHERE id = '$id'");

		if( $user['user_level'] == USER_ADMIN ) {
			$sql = "SELECT module.id, module.friendly_name AS name, module.name AS internal_name
					FROM admin_module AS module
					WHERE active = 1
					ORDER BY `order`";
		} else {
			$sql = "SELECT module.id, module.friendly_name AS name, module.name AS internal_name
					FROM admin_level_module AS level_module,
						admin_module AS module, users AS users
					WHERE (level_module.level <= ".$user['user_level']."
						AND level_module.module_id = module.id
						AND users.id = ".$id."
						AND active = 1)
					ORDER BY `order`";
		}
	}

	return $DB->MultiQuery($sql);
}

function ModuleInit($module) {
global $DB, $TEMPLATE, $MODULE_NAME, $MODULE_PAGETITLE;

	if( !UserCanEditCategory($module) ) {
		header("Location: /a/login.php?next=".urlencode($_SERVER['REQUEST_URI']));
		die();
	} else {
		$DB->Query("UPDATE users SET last_module = '$module' WHERE id = '".$_SESSION['user_id']."'");
		$info = $DB->SingleQuery("SELECT page_title, friendly_name FROM admin_module WHERE name = '$module'");
		$MODULE_NAME = $info['friendly_name'];
		$MODULE_PAGETITLE = $info['page_title'];
	}
	$TEMPLATE->AddCrumb('/admin/', "Admin");
	$TEMPLATE->AddCrumb('/modules/'.$module.'.php', $MODULE_PAGETITLE);

}

?>