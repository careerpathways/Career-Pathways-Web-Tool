<?php
chdir("../");
include("inc.php");

RedirectSecure();

if( KeyInRequest('change') ) {
	PrintHeader();
	if( KeyInRequest('new') || KeyInRequest("new1") ) {
		if( KeyInRequest('new') && KeyInRequest('new1') &&
			$_REQUEST['new'] != "" && $_REQUEST['new1'] != "" ) {

			if( $_REQUEST['new'] == $_REQUEST['new1'] ) {
				if( $_SESSION['email'] != 'guest' ) {
					$DB->Query("UPDATE users SET password = '".crypt($_REQUEST['new'], $DB->pswdsalt)."' WHERE id = ".$_SESSION['user_id']);
				}
				echo "<p>Your password has been successfully changed.</p>";
				echo '<h2><a href="http://'.$_SERVER['SERVER_NAME'].'/">Continue</a></h2>';
			} else {
				ShowNewPasswordForm("<div style=\"color:red;\">The password you entered don't match.</div>");
			}
		} else {
			ShowNewPasswordForm("<div style=\"color:red;\">You must enter your password twice.</div>");
		}
	} else {
		if( KeyInRequest('loggedin') ) {
			$msg = '<h2>Change your password</h2>';
		} else {
			$msg = "";
		}

		ShowNewPasswordForm($msg);
	}
	echo str_repeat('<br>',20);
	PrintFooter();
} else {
	if( array_key_exists('email', $_REQUEST) ) {
		// look up peopleid by email address
		$user = $DB->SingleQuery("SELECT id, email FROM users WHERE email = '".$DB->Safe($_REQUEST['email'])."'");

		PrintHeader();
		if( is_array($user) ) {
			$user_id = $user['id'];
			$username = $user['email'];

			// generate random 6-character password
			$password = RandPass(6);


			if( $_SESSION['email'] != 'guest' ) {

				// pop encrypted password into the database in the temppassword field
				$DB->Query("UPDATE users SET temp_password = '".crypt($password, $DB->pswdsalt)."' WHERE id = $user_id");
	
				if( $SITE->force_https_login() && !$SITE->is_aaronsdev() ) {
					$url = "https://".$SITE->https_server()."/a/login.php";
				} else {
					$url = "http://".$_SERVER['SERVER_NAME']."/a/login.php";
				}
	
				$email = new SiteEmail('temporary_password');
				$email->IsHTML(false);
				$email->Assign('LOGIN_LINK', $url.'?email='.$username.'&password='.$password);
				$email->Assign('PASSWORD', $password);
				$email->Assign('EMAIL', $username);
	
				$email->Send();

			}

			?>
			<p>A temporary <span title="<?= $password ?>">password</span> has been sent to your email address.</p>
			<b>You'll need to change your password as soon as you log in.</b></p>
			<?php
			echo str_repeat('<br>',20);
		} else {
			echo "<p>The email address you entered could not be found.</p>";
			echo '<p><a href="password.php">Back</a></p>';
		}
		PrintFooter();
	} else {
		PrintHeader();

		if( $SITE->force_https_login() && !$SITE->is_aaronsdev() ) {
			$form_action = "https://".$SITE->https_server()."/a/password.php";
		} else {
			$form_action = "password.php";
		}
		?>
		<form action="<?= $form_action; ?>" method="post">
		<p>Enter your email address below to get a temporary password.</p>
		<p>You'll need to change your password once you log in.</p>
		<p><input type="text" name="email" id="email" size="30"></p>
		<p><input type="submit" value="Submit" class="submit"></p>
		</form>
		<?php
		PrintFooter();
	}
}


function ShowNewPasswordForm($msg="") {
global $SITE;

	if( $SITE->force_https_login() && !$SITE->is_aaronsdev() ) {
		$form_action = "https://".$SITE->https_server()."/a/password.php";
	} else {
		$form_action = "password.php";
	}

	if($_SESSION['email'] == 'guest')
	{
		echo '<p>The guest account password cannot be changed.';
		return;
	}
	
	?>
	<p><?php echo $msg; ?></p>
	<form action="<?= $form_action; ?>" method="post">
	<p>Enter a new password below:</p>
	<p><input type="password" name="new" id="new" size="30"></p>
	<p>Please confirm the password by entering it again:</p>
	<p><input type="password" name="new1" id="new1" size="30"></p>

	<p><input type="submit" value="Change Password" class="submit"></p>
	<input type="hidden" name="change">
	</form>
	<?php
}

?>
