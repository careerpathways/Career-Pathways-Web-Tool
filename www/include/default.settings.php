<?php
define('AWS_KEY', 'AKIAJTYFFWRURSGGNJ5Q');
define('AWS_SECRET_KEY', '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');

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
        'post_assurances' => true,
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
	function email_name() { return "Oregon CTE Pathways"; }
	function email() { return "helpdesk@ctepathways.org"; }

	function recipient_email() { return "helpdesk@ctepathways.org"; }

	function __construct() {
		$this->DBname = 'pathways';
		$this->DBuser = 'pathways';
		$this->DBpass = 'pathways';

		$this->ConnectDB();
	}

	function base_url() { return $_SERVER['SERVER_NAME']; }
	function cache_path($folder="") { 
		$base_dir = '/web/oregon.ctepathways.org/cache/';
		
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
