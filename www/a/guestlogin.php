<?php
chdir("..");
include("inc.php");

$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';

// guest login:
// provide a short survey to capture user information, then log them in
// name, email, referred by


if( PostRequest() ) {

	if(Request('ref') != 20) {
		PrintHeader();
		echo '<p>Sorry there was an error.</p>';
		PrintFooter();
		die();
	}

	$requiredFields = array('first_name', 'last_name', 'email', 'school');
	
	$valid = true;
	foreach($requiredFields as $f)
		if(Request($f) == false)
			$valid = false;
	
	if(!$valid)
	{
		PrintHeader();
		echo '<p>Please enter your name, email address and organization in order to log in.</p>';
		PrintFooter();
		die();
	}
	
	if(Request('download') && Request('license') == FALSE)
	{
		PrintHeader();
		echo '<p>You must agree to the license agreement before downloading the source code.</p>';
		PrintFooter();
		die();
	}
	
	// log the info
	$guest = array();
	$guest['date'] = $DB->SQLDate();
	$guest['first_name'] = Request('first_name');
	$guest['last_name'] = Request('last_name');
	$guest['email'] = Request('email');
	$guest['school'] = Request('school');
	$referral = '';
	if(Request('referral'))
	{
		$referral = csl(Request('referral'));
		if( in_array('Other',Request('referral')) ) {
			$referral .= ' "'.Request('referral_other').'"';
		}
	}
	$guest['referral'] = $referral;
	$guest['ipaddr'] = $_SERVER['REMOTE_ADDR'];
	
	if(Request('download'))
		$guest['download'] = 1;

	$DB->Insert('guest_logins', $guest);						
	

	$user = $DB->SingleQuery('SELECT * FROM users WHERE email="guest"');
	$_SESSION['user_id'] = $user['id'];
	$_SESSION['first_name'] = $user['first_name'];
	$_SESSION['last_name'] = $user['last_name'];
	$_SESSION['full_name'] = $user['first_name'].' '.$user['last_name'];
	$_SESSION['email'] = $user['email'];
	$_SESSION['user_level'] = $user['user_level'];
	$_SESSION['school_id'] = $user['school_id'];

	if(Request('download'))
		header("Location: /p/licensing?download");
	else
		header("Location: /");
	die();

} else {

	$TEMPLATE->AddCrumb('/a/guestlogin.php', array_key_exists('download', $_GET) ? 'Download Source Code' : 'Guest Login');
	PrintHeader();

	?>

	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="theForm">
	<table>
	<tr>
		<td colspan="2">
		<?php 
		if(array_key_exists('download', $_GET))
		{
			?>
			<p>The Career Pathways Roadmap Web Tool is available under an open-source license. Please enter your information below to download the source code.</p>
			<?php 
		}
		else
		{
			?>
			<p>Please tell us who you are in order to log in.</p>
			<p>Your information will not be shared with any other parties, and will not be associated with any work you do while logged in.</p>
			<?php 
		}
		?>
		</td>
	</tr>
	<tr>
		<th>First Name*</th>
		<td><input type="text" size="20" name="first_name" /></td>
	</tr>
	<tr>
		<th>Last Name*</th>
		<td><input type="text" size="20" name="last_name" /></td>
	</tr>
	<tr>
		<th>Email*</th>
		<td><input type="text" size="30" name="email" /></td>
	</tr>
	<tr>
		<th valign="top">Organization*</th>
		<td><input type="textbox" name="school" size="30" /></td>
	</tr>
	<tr>
		<th>How did you hear about us?</th>
		<td>
			<input type="checkbox" name="referral[]" value="Friend or Colleague" />Friend/Colleague &nbsp;
			<input type="checkbox" name="referral[]" value="Training Session" />Training Session &nbsp;<br />
			<input type="checkbox" name="referral[]" value="National Conference" />National Conference &nbsp;
			<input type="checkbox" name="referral[]" value="Web Conference" />Web Conference &nbsp;
			<input type="checkbox" name="referral[]" value="Other" />Other: <input type="textbox" name="referral_other" size="20" /> &nbsp;
	</tr>
	<?php 
	if(array_key_exists('download', $_GET))
	{
	?>
	<tr>
		<th>License Agreement*</th>
		<td>
			<div style="width: 600px;">
			<input type="hidden" name="download" value="download" />
			<input type="checkbox" name="license" value="yes" />
			I have read and agree to the <a href="/p/license_agreement">license agreement</a>, and am 
			authorized to agree to the terms and conditions on behalf of my organization.
			</div> 
		</td>
	</tr>
	<?php 
	}
	?>
	<tr>
		<td>&nbsp;</td>
		<td><input id="submitButton" type="button" value="Log In" class="submit"></td>
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



	PrintFooter();

}




?>