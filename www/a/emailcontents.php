<?php
chdir("../");
include("inc.php");

ModuleInit('emailcontents');

if( strtolower($_SERVER['REQUEST_METHOD']) == "post" ) {

	if( Request('id') ) {
		// required fields
		$fields = array('sender','recipient','bcc','subject','emailbody');
	
		foreach( $fields as $field ) {
			$data[$field] = Request($field);
		}
	
		$DB->Update('email_text', $data, Request('id'));
		header("Location: ".$_SERVER['PHP_SELF']."?success=".Request('id'));

	}

} else {
	PrintHeader();
	
	if( Request('id') ) {
	
		// pull email body from database
		$email = $DB->SingleQuery("SELECT * FROM email_text WHERE id='".Request('id')."'");
		if( count($email) > 0 ) {
			$email['subject'] = htmlspecialchars($email['subject']);
			$email['body'] = htmlspecialchars($email['emailbody']);


			$name = ucwords(str_replace('_',' ',$email['id']));
			?>


			<h1><?= $name ?></h1>

			<p><div class="mod_addlink"><a href="<?= $_SERVER['PHP_SELF'] ?>">[back]</a></div></p>

			<p><?= $email['description'] ?></p>

			<br>
	
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<table>
				<tr>
					<td valign="top">Sender</td>
					<td><input type="textbox" name="sender" style="width:650px" value="<?= $email['sender'] ?>"></td>
				</tr>
				<tr>
					<td valign="top">Recipient</td>
					<td><input type="textbox" name="recipient" style="width:650px" value="<?= $email['recipient'] ?>"></td>
				</tr>
				<tr>
					<td valign="top">BCC</td>
					<td><input type="textbox" name="bcc" style="width:650px" value="<?= $email['bcc'] ?>"></td>
				</tr>
				<tr>
					<td valign="top">Subject</td>
					<td><input type="textbox" name="subject" style="width:650px" value="<?= $email['subject'] ?>"></td>
				</tr>
				<tr>
					<td valign="top">Body</td>
					<td><textarea style="width:650px;height: 300px" name="emailbody"><?= $email['emailbody'] ?></textarea>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value="Submit" class="submit"></td>
				</tr>
			</table>
			<input type="hidden" name="id" value="<?= $email['id'] ?>">
			</form>
	
			<div class="small">
			Note the words surrounded by ##. These are special variables which will be
			replaced by content specific to the user when it is sent. A list of the variables
			is below:
			</div>
			<br>
	
			<table>
			<?php
			$vars = $DB->MultiQuery("SELECT * FROM email_variables WHERE email_id='".$email['id']."' OR email_id='all' ORDER BY variable");

			foreach( $vars as $v ) {
				$v['description'] = str_replace("%%WEBSITE_EMAIL%%",$SITE->email(),$v['description']);

				?>
				<tr>
					<td width="160" class="small" valign="top"><?= $v['variable'] ?></td>
					<td class="small"><?= $v['description'] ?></td>
				</tr>
				<?php
			}
			?>
			</table>
			<br>
	
			<?php
		} else {
			echo "ERROR: Email contents not found in database";
		}

	} else {

		if( Request('success') ) {
			echo '<p><b>"'.ucwords(str_replace('_',' ',Request('success'))).'" was updated successfully.</b></p>';
		}

		echo '<p>Choose an email template to edit.</p>';

		$header[] = array('text'=>'&nbsp;', 'width'=>20);
		$header[] = array('text'=>"Email", 'width'=>220);
		$header[] = array('text'=>"Description");

		$T = new Chart();
		$T->SetHeadElement($header);
		$T->td_valign = "top";

		$emails = $DB->MultiQuery("SELECT * FROM email_text ORDER BY id");
		foreach( $emails as $e ) {
			$row = array();
			$row[] = '<a href="'.$_SERVER['PHP_SELF'].'?id='.$e['id'].'"><img src="/common/silk/page_edit.png" height="16" width="16"></a>';
			$row[] = ucwords(str_replace('_',' ',$e['id'])).'<br>&nbsp;';
			$row[] = $e['description']; 

			$T->AddRow($row);
		}

		$T->Output();

	}

	PrintFooter();
}


?>