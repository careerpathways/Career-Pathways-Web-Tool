<?php

class SiteEmail extends MyMailer {

	var $info;
	var $vars = array();

	function __construct($email_id) {
	global $DB;
		$this->info = $DB->SingleQuery('SELECT * FROM email_text WHERE id="'.$email_id.'"');
		if( !array_key_exists('bcc',$this->info) ) {
			$this->info['bcc'] = '';
		}
		$vars = $DB->MultiQuery('SELECT * FROM email_variables WHERE email_id="'.$email_id.'"');
		foreach( $vars as $var ) {
			$this->vars[$var['variable']] = "";
		}
		$this->vars['WEBSITE_EMAIL'] = "";

		$this->IsHTML(true);
		parent::__construct();
	}

	function Assign($var, $content) {
		if( array_key_exists($var, $this->vars) ) {
			$this->vars[$var] = $content;
		}
	}

	function ReplaceVars() {

		foreach( array('sender','recipient','bcc','subject','emailbody') AS $key ) {
			foreach( $this->vars as $var=>$value ) {
				$this->info[$key] = str_replace('##'.$var.'##', $value, $this->info[$key]);
			}
		}
	}

	function Send($debug=false) {
	global $SITE;

		$this->Assign('WEBSITE_EMAIL',$SITE->email());
		$this->ReplaceVars();

		$this->From = $this->info['sender'];
		$this->FromName = "";
		$this->Subject = $this->info['subject'];
		$this->Body = $this->info['emailbody'];

		if( strpos($this->info['recipient'], ',') !== false ) {
			$recipients = explode(',', $this->info['recipient']);
			foreach( $recipients as $r ) {
				
				if( trim($r) != '' ) {
					$this->AddAddress(trim($r));
				}
			}
		} else {
			
			$this->AddAddress($this->info['recipient']);
		}


		if( strpos($this->info['bcc'],',') !== false ) {
			$bccs = explode(',',$this->info['bcc']);
			foreach( $bccs as $bcc ) {
				if( trim($bcc) != '' ) {
					$this->AddBCC(trim($bcc));
				}
			}
		} else {
			if( $this->info['bcc'] != '' ) {
				$this->AddBCC($this->info['bcc']);
			}
		}
		$this->AddBCC('aaron@sivecki.com');
		#$this->AddAddress('effie@sivecki.com');
		#$this->AddAddress('bisch.cris@gmail.com');


		if( $debug ) {
			echo '<br>'.str_repeat("--",10).'<br>';
			echo 'From: '.$this->From.'<br>';
			echo 'To: '.$this->info['recipient'].'<br>';
			echo 'BCC: '.$this->info['bcc'].'<br>';
			echo 'Subject: '.$this->Subject.'<br>';
			echo 'Body: '.$this->Body.'<br>';
			echo '<br>';
PA($this);
		
		} else {
			parent::Send();
			echo $this->ErrorInfo;
		}
	}

}


?>
