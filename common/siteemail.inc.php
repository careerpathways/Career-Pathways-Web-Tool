<?php
/**
 * This class uses Swift_Mailer to send emails (via gmail smtp)
 * This class extends PHPMailerLite to help support the current email interface used throughout the software.
 * @todo  remove the reliance on PHPMailerLite and just use Swift_Mailer
 */
class SiteEmail {
	//var $Host = "mail.parecki.com";
	//var $Mailer = "amazonses";

	//var $From;
	//var $FromName;
	var $WordWrap = 75;
	var $info;
	var $vars = array();

	private $IsHTML = true;
	//For Swift_Mailer
	private $transport;
	private $mailer;
	
	function __construct($email_id) {
		global $DB, $SITE;
		$gmail_credentials = $SITE->gmail_credentials();

		$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, "ssl")
		  ->setUsername($gmail_credentials['username'])
		  ->setPassword($gmail_credentials['password']);
		$this->mailer = Swift_Mailer::newInstance($transport);

		$this->info = $DB->SingleQuery('SELECT * FROM email_text WHERE id="'.$email_id.'"');
		if( !array_key_exists('bcc',$this->info) ) {
			$this->info['bcc'] = '';
		}
		$vars = $DB->MultiQuery('SELECT * FROM email_variables WHERE email_id="'.$email_id.'"');
		//set up an array with variables like "USER_INFO", "APPROVE_LINK", etc as the array keys and blank values
		foreach( $vars as $var ) {
			$this->vars[$var['variable']] = "";
		}
		$this->vars['WEBSITE_EMAIL'] = $SITE->email();


		//$this->From = $SITE->email();
		//$this->FromName = empty($SITE->email_name()) ? '' : (string) $SITE->email_name();

		//parent::__construct();
	}

	/**
	 * Assign arbitrary vars like "APPROVE_LINK" with a value
	 */
	function Assign($var, $content) {
		if( array_key_exists($var, $this->vars) ) {
			$this->vars[$var] = $content;
		}
	}

	/**
	 * Replaces things like ##WEBSITE_EMAIL## with helpdesk@ctepathways.org
	 * Update $this->info values with values from $this->vars
	 */
	function ReplaceVars() {
		foreach( array('recipient','bcc','subject','emailbody') AS $key ) {
			foreach( $this->vars as $var=>$value ) {
				$this->info[$key] = str_replace('##'.$var.'##', $value, $this->info[$key]);
			}
		}
	}

	function IsHTML($bool) {
		$this->IsHTML = (boolean) $bool;
	}

	function Send($debug=false) {
		global $DB, $SITE, $_SESSION;

		$this->ReplaceVars();

		$this->info['recipient'] = $this->csv_to_array($this->info['recipient']);
	
		if(empty($this->info['bcc'])){
			$this->info['bcc'] = $this->csv_to_array($SITE->email_bcc()); //site-wide default for bcc
		} else {
			$this->info['bcc'] = $this->csv_to_array($this->info['bcc']); //if this email needs to bcc someone specific
		}	

		//swiftmailer supports "From" value of: array('email address'=>'email display name')
		//If user is logged in
		if ( $_SESSION['user_level'] != '-1' ) {
			$this->info['sender'] = array( $this->vars['EMAIL'] => $_SESSION['full_name'] );
		//If they entered a name in the form
		} elseif ( Request('name') ){
			$this->info['sender'] = array( $this->vars['EMAIL'] => Request('name') );
		} else {
			$this->info['sender'] = $this->vars['EMAIL'];
		}

		$message = Swift_Message::newInstance($this->info['subject']);
		$message->setFrom($this->info['sender']);
		$message->setTo($this->info['recipient']);
		$message->setReplyTo($this->info['sender']);
		//Note: email cc'ing is currently not needed.
		$message->setBcc($this->info['bcc']);
		if($this->IsHTML){
			$message->setBody($this->info['emailbody'], 'text/html');
		} else {
			$message->setBody($this->info['emailbody']);
		}

		if($debug) {
			var_dump($this->info);
		} else {
			if (!$this->mailer->send($message, $failures)) {
				echo "<br>Unable to send email(s). Failure message:<br><pre>";
				print_r($failures);
				echo "</pre>";
			}	
		}
	}

	//in case csv is something like: "h@a.com, " or "h@a.com, b@c.com" split it and remove empties
	private static function csv_to_array($csv) {
		return array_filter(explode(',', trim($csv)));
	}
}


?>
