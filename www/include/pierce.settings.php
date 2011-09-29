<?php
define('AWS_KEY', 'AKIAJTYFFWRURSGGNJ5Q');
define('AWS_SECRET_KEY', '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');

class ThisSite extends SiteSettings {

	var $debug = true;

	public $olmis_enabled = FALSE;
	public $oregon_skillset_enabled = TRUE;
	
	public $lang_file = 'pierce';
	
	function name() { return "Career Pathways Web Tool"; }
	function email_name() { return "Oregon CTE Pathways"; }
	function email() { return "helpdesk@washingtoncareerpathways.org"; }

	function recipient_email() { return "helpdesk@washingtoncareerpathways.org"; }

	function __construct() {
		$this->DBname = 'pathways_pierce';
		$this->DBuser = 'pathways';
		$this->DBpass = 'pathways';

		$this->ConnectDB();
	}

	function base_url() { return $_SERVER['SERVER_NAME']; }
	function cache_path($folder="") { 
		$base_dir = '/web/pierce.sivecki.com/cache/';
		
		if( $folder ) {
			if( !is_dir($base_dir . $folder) ) 
				mkdir($base_dir . $folder, 0777);
		}
	
		return $base_dir . $folder . '/';
	}

	function https_port() { return ""; }
	function https_server() { return $_SERVER['SERVER_NAME']; }
	function force_https_login() { return false; }

	function recaptcha_publickey() { return '6LfpkQsAAAAAAFDq52bOalzO048kYoRB0-mfC_fL'; }
	function recaptcha_privatekey() { return '6LfpkQsAAAAAAG-sLOdBhoSkLhaFEmu7QhqlN0a1'; }
}

?>
