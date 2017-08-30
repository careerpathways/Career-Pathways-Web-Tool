<?php
define('AWS_KEY', '');
define('AWS_SECRET_KEY', '');

class ThisSite extends SiteSettings {

	public $debug = true;
	
    /*
    *  Set site-specific features in this array then use hasFeature
    *  or feature to access the data.
    *
    */
    private $site_features = array(
        'olmis' => false,
        'oregon_skillset' => true,
        'post_assurances' => false,
        'approved_program_name' => true
    );

    public function hasFeature( $feature )
    {
        return $this->feature( $feature ) !== false;
    }

    public function feature( $feature )
    {
        if (!$feature) return true;

        $featureLC = strtolower( $feature );
        if (isset($this->site_features[$featureLC])) {
            return $this->site_features[$featureLC];
        }
        return false;
    }

    //JGD: Need to change?:
	public $lang_file = 'oregon';
	
	function name() { return "Career Pathways Web Tool"; }
	function email_name() { return "CTE Pathways"; }
	function email() { return "default@email.com"; }
	function email_bcc() { return 'default@email.com'; }
	function recipient_email() { return "default@email.com"; }

	function __construct() {
		$this->DBname = 'cpwt_core';
		$this->DBuser = '';
		$this->DBpass = '';

		$this->ConnectDB();
	}

	function base_url() { return $_SERVER['SERVER_NAME']; }
	function cache_path($folder="") { 
		$base_dir = '/home/project/cache/';
		
		if( $folder ) {
			if( !is_dir($base_dir . $folder) ) 
				mkdir($base_dir . $folder, 0777);
		}
	
		return $base_dir . $folder . '/';
	}

	function asset_path() {
		return '/home/project/assets/';
	}

	function https_port() { return ""; }
	function https_server() { return $_SERVER['SERVER_NAME']; }
	function force_https_login() { return false; }

	function recaptcha_publickey() { return ''; }
	function recaptcha_privatekey() { return ''; }
}

?>
