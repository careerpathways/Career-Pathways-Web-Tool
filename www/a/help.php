<?php
chdir("..");
include("inc.php");


ModuleInit('help');


if( PostRequest() ) {

	$content = array();
	$content['date'] = $DB->SQLDate();
	$content['user_id'] = $_SESSION['user_id'];
	$content['subject'] = $_REQUEST['subject'];
	$content['message'] = $_REQUEST['message'];

	$DB->Insert('helprequests',$content);

	$email = new SiteEmail('help_request');
	$email->IsHTML(false);
	$email->Assign('EMAIL', $_SESSION['email']);
	$email->Assign('SUBJECT', $_REQUEST['subject']);
	$email->Assign('BODY', $_REQUEST['message']);

	$email->Send();

	header("Location: ".$_SERVER['PHP_SELF'].'?submitted');

} else {

	PrintHeader();

	if( KeyInRequest('submitted') ) {
		echo '<p>Your request was submitted. We will get back to you within the next business day.</p>';
	} else {
		?>
		<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">

		<p>Please use this form to submit questions or problems with the Web Tool. We will get back to you within the next business day.</p>
		<table>
		<tr>
			<th>From</th>
			<td><?= $_SESSION['email'] ?></td>
		</tr>
		<tr>
			<th>To</th>
			<td><?= $DB->GetValue('recipient','email_text','help_request') ?></td>
		</tr>
		<tr>
			<th>Subject</th>
			<td><input type="text" name="subject" style="width: 500px"></td>
		</tr>
		<tr>
			<th valign="top">Message</th>
			<td><textarea style="width: 500px; height: 300px;" name="message"></textarea></td>
		</tr>
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