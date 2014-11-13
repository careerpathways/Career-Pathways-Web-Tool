<?php

abstract class SiteSettings {

	private $locals = array('localhost');
	private $DB;

	//*******************************************************
	// BEGIN CONFIGURATION OPTIONS
	//
	// Override these methods to change the options if necessary.
	// note: every website must define its name() and DBname

	protected $DBhost = 'localhost';
	protected $DBuser = 'localhost';
	protected $DBpass = 'localhost';
	protected $DBname;

	abstract function name();
	abstract function email();
	abstract function email_name();

	function root() { return "/"; }

	function force_https_login() { return false; }

	function breadcrumb_separator() { return " &laquo; "; }
	function title_separator() { return " &#8226; "; }

	function reverse_breadcrumb() { return true; }
	function reverse_pagetitle() { return true; }

	function hide_home_link() { return false; }

	function display_sql_errors() { return true; }

	public $debug = false;

	//*******************************************************




	// these methods should never be overridden, as they use the data
	// that has been set per site

	final function is_aaronsdev() {
		if( array_key_exists('SERVER_NAME',$_SERVER) ) {
			return in_array($_SERVER['SERVER_NAME'],$this->locals);
		} else {
			return false;
		}
	}

	final function add_local_name($name) {
		$this->locals[] = $name;
	}

	final function ConnectDB() {
		$this->DB = new common_db;
			$this->DB->host   = $this->DBhost;
			$this->DB->user   = $this->DBuser;
			$this->DB->pass   = $this->DBpass;
			$this->DB->name   = $this->DBname;
			$this->DB->tp = ""; // prefix for the tables in the database
			$this->DB->pswdsalt = "Sa";
			$this->DB->display_errors = TRUE;

		// Attempt to connect to the database
		$this->DB->halt_on_error = FALSE;

		$result = $this->DB->Connect();

		if($result == 0)
			$this->DB = FALSE;
		else
			$this->DB->halt_on_error(TRUE);
	}

	final function &GetDBH() {
		return $this->DB;
	}

	function google_analytics($id)
	{
		if(!$id || $id === ''){ return '';}
		$str = '<!-- Google Analytics -->'
		. '<script type="text/javascript">
		var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
		document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
		try {
		var pageTracker = _gat._getTracker("' . $id . '");
		pageTracker._trackPageview();
		} catch(err) {}</script>';
		return $str;
	}
}
