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

	if( $wastemplink ) {
		header("Location: /a/password.php?change&loggedin");
	} else {
		if( Request('next') ) {
			header("Location: http://".$server.Request('next'));
		} else {
			if( IsAdmin() || IsSchoolAdmin() ) {
				header("Location: http://$server/");
			} else {
				header("Location: http://$server/a/drawings.php");
			}
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

function ShowLoginForm($email="") {
global $SITE;

	if( $SITE->force_https_login() && !$SITE->is_aaronsdev() ) {
		$form_action = "https://".$SITE->https_server().':'.$SITE->https_port()."/a/login.php";
	} else {
		$form_action = "login.php";
	}

	if( IsIE() && !strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 7.0') ) {
		echo '<div style="font-size:19pt; font-weight: bold; color: #cf9d2b">Notice for Internet Explorer 6 Users</div>';
		echo '<p>IE 6 is not yet fully supported by this website. Most things will work, but you may experience slight glitches.</p>';
		echo '<p>We recommend using <a href="http://www.mozilla.com/en-US/firefox/">Firefox</a> or Internet Explorer 7 instead. Or you can continue logging in below.</p>';
	}

	?>

	<br><br>
	<form action="<?= $form_action; ?>" method="post">
	<table align="center">
	<tr>
		<td>Email:</td>
		<td><input type="text" size="20" name="email" id="email" value="<?= $email; ?>"></td>
		<td width="50">&nbsp;</td>
		<td><span class="login_button"><a href="/a/apply.php">Apply for an account</a></span></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" size="20" name="password" id="password"></td>
		<td width="50">&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Log In" class="submit"></td>
		<td width="50">&nbsp;</td>
		<td><span class="login_button"><a href="/a/help.php">Questions/Problems?</a></span></td>
	</tr>
	<tr>
		<td colspan="2"><hr></td>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><br><span class="button_link"><a href="/a/guestlogin.php">Guest Login</a></span></td>
	</tr>
	</table>

	<input type="hidden" name="next" value="<?= (Request('next')) ?>">
	</form>

	<?php
	echo str_repeat('<br>',20);
}

?>