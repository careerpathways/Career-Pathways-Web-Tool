<?php
chdir("..");
include("inc.php");

RedirectSecure();

if( KeyInRequest('password') ) {
	if( $DB->Query("SELECT id, first_name, last_name, email, user_level, school_id
					FROM users
					WHERE (password = '".crypt($_REQUEST['password'],$DB->pswdsalt)."'
						OR temp_password = '".crypt($_REQUEST['password'],$DB->pswdsalt)."')
						AND user_active = 1
						AND email = '".$DB->Safe($_REQUEST['email'])."';" ) !== FALSE ) {

		if( $DB->NumRecords() == 1 ) {
			$user = $DB->NextRecord();

			$_SESSION['user_id'] = $user['id'];
			$_SESSION['first_name'] = $user['first_name'];
			$_SESSION['last_name'] = $user['last_name'];
			$_SESSION['full_name'] = $user['first_name'].' '.$user['last_name'];
			$_SESSION['email'] = $user['email'];
			$_SESSION['user_level'] = $user['user_level'];
			$_SESSION['school_id'] = $user['school_id'];


			$DB->Query("SELECT id FROM users
						WHERE (temp_password = '".crypt($_REQUEST["password"],$DB->pswdsalt)."')
							AND email = '".$DB->Safe($_REQUEST['email'])."';" );
			if( $DB->NumRecords() == 1 ) {
				$wastemplink = true;
			} else {
				$wastemplink = false;
			}

			$DB->Query("UPDATE users SET temp_password='', last_logon_ip='".$_SERVER['REMOTE_ADDR']."', last_logon='".date("Y-m-d H:i:s")."' WHERE id=".$_SESSION['user_id']);

			LoginSuccess($wastemplink);
		} else {
			$WEBSITE['breadcrumb'][] = "Log In";
			PrintHeader();
			ShowLoginFailure();
			ShowLoginForm($_REQUEST['email']);
			PrintFooter();
		}

	} else {
		echo "Database error: ".$DB->Error();
	}
} else {

	if( KeyInRequest('reset') )
	{
		if( array_key_exists('original_user_id', $_SESSION) )
		{
			$user = $DB->SingleQuery('SELECT * FROM users WHERE id = '.$_SESSION['original_user_id']);
	
			unset($_SESSION['original_user_id']);
			unset($_SESSION['original_user_name']);
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['first_name'] = $user['first_name'];
			$_SESSION['last_name'] = $user['last_name'];
			$_SESSION['full_name'] = $user['first_name'].' '.$user['last_name'];
			$_SESSION['email'] = $user['email'];
			$_SESSION['user_level'] = $user['user_level'];
			$_SESSION['school_id'] = $user['school_id'];
		}
		
		header("Location: /");
		die();
	}

	if( KeyInRequest('logout') ) {
		$_SESSION['user_level'] = -1;
		session_destroy();
		header("Location: /");
		die();
	}

	if( $_SESSION['user_level'] > -1 ) {
		// already logged in!
		header("Location: /");
	} else {
		PrintHeader();
		//if( KeyInRequest('logout') ) echo "<p>You have been successfully logged out.</p>";
		ShowLoginForm();
		PrintFooter();
	}
}



function LoginSuccess($wastemplink=false) {
global $SITE, $DB, $TEMPLATE;

	$server = $_SERVER['SERVER_NAME'];

	$info['date'] = $DB->SQLDate();
	$info['user_id'] = $_SESSION['user_id'];
	$info['name'] = $_SESSION['full_name'];
	$info['ip_address'] = $_SERVER['REMOTE_ADDR'];
	$info['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	$DB->Insert('login_history', $info);
	
	if( $wastemplink ) {
		header("Location: /a/password.php?change&loggedin");
	} else {
		if( Request('next') ) {
			header("Location: http://".$server.Request('next'));
		} else {
			header("Location: http://$server/");
		}
	}

}


function ShowLoginFailure() {
	?>
	<p>You entered an invalid email or password. Check the spelling and try again.</p>
	<p>Did you forget your password?<br>
	   <a href="password.php">Reset</a></p>
	<?php
}

?>