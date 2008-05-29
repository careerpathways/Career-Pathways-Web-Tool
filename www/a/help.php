<?php
chdir("..");
include("inc.php");
include("recaptcha-php/recaptchalib.php");


//ModuleInit('help');


if( PostRequest() ) {

	$cap = recaptcha_check_answer( $SITE->recaptcha_privatekey(),
									$_SERVER['REMOTE_ADDR'],
									Request('recaptcha_challenge_field'),
									Request('recaptcha_response_field') );
	
	if( $cap->is_valid ) {

		$content = array();
		$content['date'] = $DB->SQLDate();
		$content['user_id'] = $_SESSION['user_id'];
		$content['subject'] = $_REQUEST['subject'];

		$content['message'] = '';
		if( Request('name') ) {
			$content['message'] .= 'Name: '.Request('name')."\n";
		}
		if( Request('school') ) {
			$content['message'] .= 'School/Business: '.Request('school')."\n";
		}
		$content['message'] .= "\n".$_REQUEST['message'];
		$DB->Insert('helprequests',$content);
	
		$email = new SiteEmail('help_request');
		$email->IsHTML(false);
		$email->Assign('EMAIL', (IsLoggedIn()?$_SESSION['email']:Request('email')));
		$email->Assign('SUBJECT', $_REQUEST['subject']);
		$email->Assign('BODY', $content['message']);
	
		$email->Send();
	
		header("Location: ".$_SERVER['PHP_SELF'].'?submitted');

	} else {
		PrintHeader();
		echo '<p>Sorry, the reCAPTCHA was not solved correctly. Go back and try again.</p>';
		PrintFooter();
	}

} else {

	PrintHeader();

	if( KeyInRequest('submitted') ) {
		echo '<p>Your message was received. We will get back to you within two business days.</p>';
	} else {
		?>
		<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">

		<?php
		if( IsLoggedIn() ) { 
			echo '<p>Please use this form to submit questions or problems with the Web Tool. We will get back to you within two business days.</p>';
		} else {
			echo '<p>Thanks for visiting the Career Pathways Web Tool.</p>';
			if( KeyInRequest('a') ) {
				echo '<p>This website is currently only available to Oregon schools and businesses. Please contact us if you would like to use this tool in your school or business outside Oregon.</p>';
				echo '<p>You can contact us using the form below, or by writing to '.EmailEncrypt::EmailLink('help@ctepathways.org').'</p>';
			}
		}
		?>
		<table>
		<?php if( IsLoggedIn() ) { ?>
			<tr>
				<th>From</th>
				<td><?= $_SESSION['email'] ?></td>
			</tr>
			<tr>
				<th>To</th>
				<td><?= $DB->GetValue('recipient','email_text','help_request') ?></td>
			</tr>
		<?php } else { ?>
			<tr>
				<th>Your Name</th>
				<td><input type="textbox" name="name" size="30"></td>
			</tr>
			<tr>
				<th>Your Email</th>
				<td><input type="textbox" name="email" size="30"></td>
			</tr>
			<?php 
				if( KeyInRequest('a') ) { ?>
					<tr>
						<th>School or Business Name</th>
						<td><input type="textbox" name="school" size="30"></td>
					</tr>
				<?php }		
			  } ?>
		<tr>
			<th>Subject</th>
			<td><input type="text" name="subject" style="width: 500px"></td>
		</tr>
		<tr>
			<th valign="top">Message</th>
			<td><textarea style="width: 500px; height: 300px;" name="message"></textarea></td>
		</tr>
		<?php if( !IsLoggedIn() ) { ?>
		<tr>
			<th>Anti-Spam</th>
			<td>
				<?= recaptcha_get_html($SITE->recaptcha_publickey(), '', true) ?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<th>&nbsp;</th>
			<td><input type="submit" class="submit" value="Submit" id="submitButton"></td>
		</tr>
		</table>

		</form>

		<?php
	}
	PrintFooter();
}


?>