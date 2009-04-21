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
			$this->DB->display_errors = $this->display_sql_errors();
		$this->DB->Connect();
	}

	final function &GetDBH() {
		return $this->DB;
	}

	/*
	//$WEBSITE['root'] = "/";                              # defined in conf.specific.php
	$WEBSITE['name'] = "Full Thread Ahead";
	$WEBSITE['email_name'] = "Full Thread Ahead";
	$WEBSITE['email'] = "hojo@fullthreadahead.com";

	$WEBSITE['force_https_login'] = false;
	$WEBSITE['http_server'] = "fullthread.eposim.com";
	#$WEBSITE['http_server'] = $_SERVER['SERVER_NAME'];
	$WEBSITE['http_port'] = "";
	$WEBSITE['https_server'] = "fullthread.eposim.com";
	$WEBSITE['https_port'] = "";

	$WEBSITE['page_width'] = 850;
	$WEBSITE['leftcol_width'] = 202;

	$WEBSITE['import_command'] = "../xml/import.php";

	$WEBSITE['max_rows_per_page'] = 15;

	$WEBSITE['max_file_size'] = "2mb";

	$WEBSITE['images']['gallery']['full'] = array('width'=>450, 'height'=>300, 'path'=>"files/gallery/images/");
	$WEBSITE['images']['gallery']['thumbs'] = array('width'=>180, 'height'=>120, 'path'=>"files/gallery/thumbs/");

	$WEBSITE['images']['getaway'] = array('width'=>240, 'height'=>160, 'path'=>"files/getaways/");

	$WEBSITE['images']['homepage'] = array('width'=>320, 'height'=>320, 'path'=>"files/homeimages/");

	$WEBSITE['newsletters_path'] = "files/newsletters/";

	$WEBSITE['breadcrumb_on_top'] = true;

	$WEBSITE['trail'] = array();

	$COLORS['highlight'] = "6492B0";
	$COLORS['highlight_dark'] = "333399";
	$COLORS['highlight_light'] = "C0D0DB";

	$COLORS['secondary'] = "D2DFDC";
	$COLORS['tertiary'] = "EBE9C3";

	$COLORS['borders'] = $COLORS['highlight'];
	$COLORS['table_highlight'] = $COLORS['highlight'];
	*/
}

?>
