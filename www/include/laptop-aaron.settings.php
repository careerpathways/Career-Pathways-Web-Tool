<?php

class ThisSite extends SiteSettings {

	var $debug = true;

	public $olmis_enabled = TRUE;
	public $oregon_skillset_enabled = TRUE;
	
	function name() { return "Career Pathways Web Tool"; }
	function email_name() { return "Oregon CTE Pathways"; }
	function email() { return "aaron@parecki.com"; }

	function recipient_email() { return "aaron@parecki.com"; }

	function __construct() {
		$this->DBname = 'pathways';
		$this->DBuser = 'pathways';
		$this->DBpass = 'pathways';

		$this->ConnectDB();
	}

	function base_url() { return $_SERVER['SERVER_NAME']; }
	function cache_path($folder="") { 
		$base_dir = 'C:/Users/Aaron Parecki/Aptana Studio Workspace 1.5/Pathways/cache/';
		
		if( $folder ) {
			if( !is_dir($base_dir . $folder) ) 
				mkdir($base_dir . $folder, 0777);
		}
	
		return $base_dir . $folder . '/';
	}

	function https_port() { return ""; }
	function https_server() { return $_SERVER['SERVER_NAME']; }
	function force_https_login() { return false; }

	function recaptcha_publickey() { return '6Ldg9wEAAAAAADD5_LekXYwr2W6xeSDvPSrn2ULE'; }
	function recaptcha_privatekey() { return '6Ldg9wEAAAAAAHq3SbV8Ko0VEpcUEzg-QFq1DIx6'; }
}

?>