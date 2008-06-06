<?php
chdir("..");
include("inc.php");
include("recaptcha-php/recaptchalib.php");

RedirectSecure();


PrintHeader();

if( PostRequest() ) {

	$cap = recaptcha_check_answer( $SITE->recaptcha_privatekey(),
									$_SERVER['REMOTE_ADDR'],
									Request('recaptcha_challenge_field'),
									Request('recaptcha_response_field') );
	if( $cap->is_valid ) {

		$check = $DB->SingleQuery('SELECT * FROM users WHERE email=""');
		if( is_array($check) ) {
			echo '<p>It appears you already have an account registered with this email address.</p>';
			echo '<p>If you have forgotten your password, please visit the <a href="/a/password.php?reset">password reset</a> page</p>';
			echo '<p>If you continue to have trouble, please <a href="/contact.php">Contact Us</a></p>';
		} else {
			$user = array();
			$user['first_name'] = Request('first_name');
			$user['last_name'] = Request('last_name');
			$user['email'] = Request('email');
			$user['job_title'] = Request('job_title');
			$user['phone_number'] = Request('phone_number');
			$user['user_level'] = 16;
			$user['user_active'] = 0;
			$user['new_user'] = 1;
			$user['application_key'] = md5($user['email'].time());
			$user['last_logon'] = $DB->SQLDate();
	
			$referral = '';
			$referral = csl(Request('referral'));
			if( in_array('Other',Request('referral')) ) {
				$referral .= ' "'.Request('referral_other').'"';
			}
			$user['referral'] = $referral;
	
			$recipients = '';
			if( Request('school') != 0 && is_numeric(Request('school')) ) {
				$user['school_id'] = Request('school');
				$admins = $DB->MultiQuery('SELECT email FROM users WHERE school_id='.$user['school_id'].' AND user_level>64');
				foreach( $admins as $m ) {
					$recipients .= $m['email'].', ';
				}
				$user['other_school'] = '';
				$school = $DB->SingleQuery('SELECT school_name FROM schools WHERE id='.$user['school_id']);
				$school_name = $school['school_name'];
			} else {
				$user['school_id'] = 0;
				$user['other_school'] = Request('school_other');
				$school_name = $user['other_school'];
			}

			$DB->Insert('users',$user);

			// compose email to send to help@ctepathways.org as well as any school admins of the school they are applying for

			$user_info = '';
			$user_info .= "Name: ".$user['first_name'].' '.$user['last_name']."\n";
			$user_info .= "Job Title: ".$user['job_title']."\n";
			$user_info .= "Phone Number: ".$user['phone_number']."\n";
			$user_info .= "Email: ".$user['email']."\n";
			$user_info .= "School/Business: ".$school_name."\n";


			$email = new SiteEmail('account_request');
			$email->IsHTML(false);
			$email->Assign('RECIPIENTS', $recipients);
			$email->Assign('RECIPIENTS', 'aaron@parecki.com, effie@effie.bz');
			$email->Assign('APPROVE_LINK', 'http://'.$_SERVER['SERVER_NAME'].'/a/users.php?key='.$user['application_key']);
			$email->Assign('USER_INFO', $user_info);

			$email->Send();


			$email = new SiteEmail('account_request_rt');
			$email->IsHTML(false);
			$email->Assign('EMAIL', $user['email']);
			$email->Assign('APPROVE_LINK', 'http://'.$_SERVER['SERVER_NAME'].'/a/users.php?key='.$user['application_key']);
			$email->Assign('USER_INFO', $user_info);

			$email->Send();
	
	
			echo '<p>Thank you. Your application has been submitted for approval. You can expect a response within one business day.<br><br>Thank you,<br>Pathways Web Tool User Support</p>';
		}

	} else {
		echo '<p>Sorry, the reCAPTCHA was not solved correctly. Go back and try again.</p>';
	}


} else {
	if( KeyInRequest('form') ) {
		ShowApplyForm();
	} else {
		echo '<p>Are you affiliated with an Oregon school or business?</p>';
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'?form">Yes</a> &nbsp;&nbsp; <a href="/a/help.php?a">No</a></p>';
	}
}



PrintFooter();



function ShowApplyForm() {
global $SITE;

	if( $SITE->force_https_login() && !$SITE->is_aaronsdev() ) {
		$form_action = "https://".$SITE->https_server().$SITE->https_port()."/a/apply.php";
	} else {
		$form_action = "apply.php";
	}

	?>

	<br><br>

	<form action="<?= $form_action; ?>" method="post">
	<table align="center">
	<tr>
		<td colspan="2"><h1>New Account Request</h1></td>
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
		<td><input type="text" size="30" name="email"> (This will be your login)</td>
	</tr>
	<tr>
		<th>Job Title</th>
		<td><input type="text" size="20" name="job_title"></td>
	</tr>
	<tr>
		<th>Phone Number</th>
		<td><input type="text" size="12" name="phone_number"></td>
	</tr>
	<tr>
		<th>School or Business</th>
		<td><?php
			echo GenerateSelectBoxDB('schools','school','id','school_name','school_name','',array('0'=>'Other'),'school_name!="Guest"');
		?><br>
		If "Other", please enter here: <input type="textbox" name="school_other" size="30"></td>
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
		<th>Anti-Spam</th>
		<td>(not case-sensitive)<br>
			<?= recaptcha_get_html($SITE->recaptcha_publickey(), '', true) ?>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Submit Application" class="submit"></td>
	</tr>
	</table>
	</form>
	<br><br><br>

	<?php
	echo str_repeat('<br>',20);
}

?>