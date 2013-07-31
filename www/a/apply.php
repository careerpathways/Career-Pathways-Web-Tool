<?php
chdir("..");
include("inc.php");

$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';

RedirectSecure();

RequireLogin();


PrintHeader();

if( PostRequest() ) {

	if( Request('ref') == 20 ) {

		$check = $DB->SingleQuery('SELECT * FROM users WHERE email="'.Request('email').'"');
		if( is_array($check) ) {
			echo '<p>It appears you already have an account registered with this email address.</p>';
			echo '<p>If you have forgotten your password, please visit the <a href="/a/password.php?reset">password reset</a> page</p>';
			echo '<p>If you continue to have trouble, please <a href="/a/help.php">contact us</a></p>';
		} else {
			$user = array();
			$user['first_name'] = Request('first_name');
			$user['last_name'] = Request('last_name');
			$user['email'] = Request('email');
			$user['job_title'] = Request('job_title');
			$user['phone_number'] = Request('phone_number');
			$user['user_level'] = 16;
			$user['user_active'] = 0;
			$user['date_created'] = $DB->SQLDate();
			$user['new_user'] = 1;
			$user['application_key'] = md5($user['email'].time());
			$user['last_logon'] = $DB->SQLDate();
	
			$referral = '';
			if( !is_array(Request('referral')) )
				$_REQUEST['referral'] = array();
			$referral = csl(Request('referral'));
			if( in_array('Other',Request('referral')) ) {
				$referral .= ' "'.Request('referral_other').'"';
			}
			$user['referral'] = $referral;
	
			$recipients = '';
			if( Request('school') != 0 && is_numeric(Request('school')) ) {
				$user['school_id'] = Request('school');
				
				$admins = $DB->MultiQuery('SELECT email FROM users WHERE school_id='.$user['school_id'].' AND user_level>64 AND user_active=1');
				//print('http://'.$_SERVER['SERVER_NAME'].'/a/users.php?key='.$user['application_key']."<pre>".print_r($admins,true)."<pre>");
				foreach( $admins as $m ) {
					$recipients .= $m['email'].', ';
				}
				$user['other_school'] = '';
				$school = $DB->SingleQuery('SELECT school_name,organization_type FROM schools WHERE id='.$user['school_id']);
				if($school['organization_type']=='HS'){
					$user['user_level'] = 8;
				}
				$school_name = $school['school_name'];
			/*
			// disable requests from schools not in the system
			} else {
				$user['school_id'] = 0;
				$user['other_school'] = Request('school_other');
				$school_name = $user['other_school'];
			*/
			}

			$DB->Insert('users',$user);

			// compose email to send to helpdesk@ctepathways.org as well as any school admins of the school they are applying for

			$user_info = '';
			$user_info .= "Name: ".$user['first_name'].' '.$user['last_name']."\n";
			$user_info .= "Job Title: ".$user['job_title']."\n";
			$user_info .= "Phone Number: ".$user['phone_number']."\n";
			$user_info .= "Email: ".$user['email']."\n";
			$user_info .= "Organization: ".$school_name."\n";


			$email = new SiteEmail('account_request');
			$email->IsHTML(false);
			$email->Assign('RECIPIENTS', $recipients);
			//$email->Assign('RECIPIENTS', 'aaron@parecki.com, effie@effie.bz');
			$email->Assign('APPROVE_LINK', 'http://'.$_SERVER['SERVER_NAME'].'/a/users.php?key='.$user['application_key']);
			$email->Assign('USER_INFO', $user_info);

			$email->Send();


			$email = new SiteEmail('account_request_rt');
			$email->IsHTML(false);
			$email->Assign('EMAIL', $user['email']);
			$email->Assign('APPROVE_LINK', 'http://'.$_SERVER['SERVER_NAME'].'/a/users.php?key='.$user['application_key']);
			$email->Assign('USER_INFO', $user_info);

			$email->Send();
	
	
			echo '<p>Your application has been submitted for approval. You can expect a response within one business day.<br><br>Thank you,<br>Pathways Web Tool User Support</p>';
		}

	} else {
		echo '<p>Sorry, there was an error. Go back and try again.</p>';
	}


} else {
	if( KeyInRequest('form') ) {
		ShowApplyForm();
	} else {
		echo '<p>Are you affiliated with an Oregon school or business?</p>';
		echo '<p><a href="'.$_SERVER['PHP_SELF'].'?form">Yes</a> &nbsp;&nbsp; <a href="/a/help.php?template=outside">No</a></p>';
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

	<form action="<?= $form_action; ?>" method="post" id="theForm">
	<table>
	<tr>
		<td colspan="2"><h1>New Account Request</h1><br></td>
	</tr>
	<tr>
		<th>First Name*</th>
		<td><input type="text" size="20" name="first_name"></td>
	</tr>
	<tr>
		<th>Last Name*</th>
		<td><input type="text" size="20" name="last_name"></td>
	</tr>
	<tr>
		<th>Organization Email*</th>
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
		<th>Organization*</th>
		<td><?php
			echo GenerateSelectBoxDB('schools','school','id','school_name','school_name','',array(0=>''),'school_name!="Guest"');
		?></td>
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
		<td>&nbsp;</td>
		<td><input type="submit" value="Submit Application" class="submit" id="submitButton"></td>
	</tr>
	</table>
	<input type="hidden" name="ref" id="ref" value="10" />
	</form>
	<br><br><br>

	<script type="text/javascript">
		$(function(){
			$("#submitButton").click(function(){
				$("#ref").val(20);
				$("#theForm").submit();
			});
		});
	</script>

	<?php
	echo str_repeat('<br>',20);
}

?>
