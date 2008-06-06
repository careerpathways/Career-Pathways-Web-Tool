<?php
chdir("..");
include("inc.php");
include("recaptcha-php/recaptchalib.php");


// guest login:
// provide a short survey to capture user information, then log them in
// name, email, referred by




if( PostRequest() ) {

	$cap = recaptcha_check_answer( $SITE->recaptcha_privatekey(),
									$_SERVER['REMOTE_ADDR'],
									Request('recaptcha_challenge_field'),
									Request('recaptcha_response_field') );
	
	if( $cap->is_valid ) {

		if( Request('first_name') && Request('last_name') && Request('email') ) {

			// log the info
			$guest = array();
			$guest['date'] = $DB->SQLDate();
			$guest['first_name'] = Request('first_name');
			$guest['last_name'] = Request('last_name');
			$guest['email'] = Request('email');
			$guest['school'] = Request('school');
			$referral = '';
			$referral = csl(Request('referral'));
			if( in_array('Other',Request('referral')) ) {
				$referral .= ' "'.Request('referral_other').'"';
			}
			$guest['referral'] = $referral;
			$guest['ipaddr'] = $_SERVER['REMOTE_ADDR'];

			$DB->Insert('guest_logins', $guest);						
			
		
			$user = $DB->SingleQuery('SELECT * FROM users WHERE email="guest"');
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['first_name'] = $user['first_name'];
			$_SESSION['last_name'] = $user['last_name'];
			$_SESSION['full_name'] = $user['first_name'].' '.$user['last_name'];
			$_SESSION['email'] = $user['email'];
			$_SESSION['user_level'] = $user['user_level'];
			$_SESSION['school_id'] = $user['school_id'];
	
			header("Location: /");
			die();

		} else {
			PrintHeader();
			echo '<p>Please enter your name and email address in order to log in.</p>';
			PrintFooter();
		}
	} else {
		PrintHeader();
		echo '<p>Sorry, the reCAPTCHA was not solved correctly. Go back and try again.</p>';
		PrintFooter();
	}

} else {

	PrintHeader();

	?>

	<br><br>

	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
	<table align="center">
	<tr>
		<td colspan="2"><h1>Guest Login</h1>
			<p>Please tell us who you are in order to log in.</p>
			<p>Your information will not be shared with any other parties, and will not be associated with any work you do while logged in.</p></td>
	</tr>
	<tr>
		<th>First Name</th>
		<td><input type="text" size="20" name="first_name"></td>
	</tr>
	<tr>
		<th>Last Name</th>
		<td><input type="text" size="20" name="last_name"></td>
	</tr>
	<tr>
		<th>Email</th>
		<td><input type="text" size="30" name="email"></td>
	</tr>
	<tr>
		<th valign="top">School or Business</th>
		<td><input type="textbox" name="school" size="30"></td>
	</tr>
	<tr>
		<th>How did you hear about us?</th>
		<td>
			<input type="checkbox" name="referral[]" value="Friend or Colleague">Friend/Colleague &nbsp;
			<input type="checkbox" name="referral[]" value="Training Session">Training Session &nbsp;<br>
			<input type="checkbox" name="referral[]" value="National Conference">National Conference &nbsp;
			<input type="checkbox" name="referral[]" value="Web Conference">Web Conference &nbsp;
			<input type="checkbox" name="referral[]" value="Other">Other: <input type="textbox" name="referral_other" size="20"> &nbsp;
	</tr>
	<tr>
		<th valign="top">Anti-Spam</th>
		<td>(not case-sensitive)<br>
			<?= recaptcha_get_html($SITE->recaptcha_publickey(), '', true) ?>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Log In" class="submit"></td>
	</tr>
	</table>
	</form>
	<br><br><br>

	<?php
	echo str_repeat('<br>',20);



	PrintFooter();

}




?>