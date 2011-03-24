<?php
date_default_timezone_set('America/Los_Angeles');

include("database.inc.php");
include("template.inc.php");
include("sitesettings.inc.php");

if(array_key_exists('CONFIG_FILE', $_SERVER))
	include($_SERVER['CONFIG_FILE']);
else
	include("default.settings.php");
	
define('CPUSER_HIGHSCHOOL', 8);
define("CPUSER_STAFF", 16);
define("CPUSER_SCHOOLADMIN", 64);
define("CPUSER_WEBMASTER", 32);
define("CPUSER_STATEADMIN", 127);

include('site-template.inc.php');
include('formatting-functions.inc.php');

include("general.inc.php");
include("admin_inc.php");
include("json_encode.php");
include('Cycler.php');

include('Localize.php');
include('lang/' . $SITE->lang_file . '.php');

/*
 * Is this even used anywhere?
 */
function logmsg($message) {
	global $SITE;
	$fp = fopen($SITE->cache_path('log') . 'log.txt', 'a');
	fwrite($fp, date("Y-m-d H:i:s").' '.$_SERVER['REMOTE_ADDR'].' ['.$_SERVER['PHP_SELF'].'] u:'.$_SESSION['user_id'].' '.$message."\n");
	fclose($fp);
}

function l($str=NULL)
{
	static $loc;
	if(!isset($loc))
		$loc = new Localize_Strings();
	
	if($str === NULL)
		// return the localize object so methods can be run off it directly
		return $loc;
	else
		// default to lookup up a string from the get() method
		return $loc->get($str);
}

function IsLoggedIn() {
	if( $_SESSION['user_level'] > -1 ) {
		return TRUE;
	} else {
		return FALSE;
	}
}

function IsGuestUser() {
	return array_key_exists('email', $_SESSION) && $_SESSION['email'] == 'guest';
}

function RandPass($len = 8){
	$str = "";
    for($i=1;$i<=$len;$i++) $str .= base_convert(rand(0,15),10,16);
    return strtoupper($str);
}


function RequireLogin() {
	if( $_SESSION['user_level'] == -1 ) {
		header("Location: /a/login.php?next=".urlencode($_SERVER['REQUEST_URI']));
	}
}

function CanEditOtherSchools() {
	return $_SESSION['user_level'] == CPUSER_STATEADMIN;
}


function CreateDrawingCodeFromTitle($title, $school_id, $drawing_id=0, $mode='pathways') {
global $DB;
	// replace spaces with underscores
	$dirty_code = preg_replace('/\s+/','-',strtolower($title));

	// remove any character that is not a letter or number
	$clean_code = CleanDrawingCode($dirty_code);

	// remove any duplicate underscores
	$clean_code = preg_replace('/_+/','-',$clean_code);

	// get the coded version of the school abbreviation (removes spaces)
	$school_abbr = CleanDrawingCode($DB->GetValue('school_abbr','schools',$school_id));

	// this is the ideal code for this drawing. but it may already exist in the database
	$proposed_code = strtolower($school_abbr.'-'.$clean_code);

	// look for conflicting codes
	$code = $proposed_code;
	while( DrawingCodeAlreadyExists($code, $drawing_id, $mode) ) {
		// keep trying new codes until we get one that is unique
		$code = $proposed_code.'-'.rand(100,999);
	}

	return $code;
}

function CleanDrawingCode($code) {
	return trim(strtolower(preg_replace('/[^a-z0-9\-]+/i','-',$code)), ' -');
}

function DrawingCodeAlreadyExists($code, $drawing_id, $mode) {
global $DB;
	$num = $DB->SingleQuery("SELECT COUNT(*) AS num FROM ".($mode=='pathways'?'drawing_main':'post_drawing_main')."
		WHERE code='".$code."'
		AND id != ".$drawing_id);
	return $num['num'] == 1;
}

function GetDrawingInfo($drawing_id, $type='pathways') {
global $DB;
	if( $type == 'pathways' ) {
		$drawing = $DB->SingleQuery("SELECT drawing_main.*, drawings.*, 
				drawings.id AS drawings_id, sk.title AS skillset, school_name,
				IF(drawing_main.name='', p.title, drawing_main.name) AS full_name
			FROM drawing_main
			JOIN drawings ON drawings.parent_id=drawing_main.id
			LEFT JOIN oregon_skillsets AS sk ON sk.id = drawing_main.skillset_id
			LEFT JOIN schools ON drawing_main.school_id=schools.id
			LEFT JOIN programs AS p ON drawing_main.program_id=p.id
			WHERE drawings.id=".$drawing_id);
	} elseif( $type == 'post' ) {
		$drawing = $DB->SingleQuery("SELECT post_drawing_main.*, post_drawings.*, post_drawings.id drawings_id, school_name
			FROM post_drawing_main, post_drawings, schools
			WHERE post_drawings.parent_id=post_drawing_main.id
			AND post_drawing_main.school_id=schools.id
			AND post_drawings.id=".$drawing_id);
	}
	return $drawing;
}

function GetSchoolName($school_id)
{
	global $DB;
	return $DB->GetValue('school_name', 'schools', intval($school_id));
}


function GetAssociatedDrawings($drawing_id, $mode='connections', $type=null)
{
	global $DB;
	$drawing_id = intval($drawing_id);

	if( $mode == 'connections' )
	{
		$type = $DB->GetValue('type', 'post_drawing_main', $drawing_id);
		return $DB->VerticalQuery('SELECT * FROM post_conn WHERE '.($type=='HS'?'hs':'cc').'_id='.$drawing_id, ($type=='HS'?'cc':'hs').'_id');
	}
	else
	{
		return $DB->VerticalQuery('SELECT post_id
									FROM vpost_links AS v
									JOIN post_drawing_main AS d ON v.post_id=d.id
									WHERE type="'.$type.'" AND vid='.$drawing_id, 'post_id');
	}
}

function GetHSAffiliations($cc_id)
{
	global $DB;
	return $DB->MultiQuery('SELECT s.*, a.id AS af_id
		FROM hs_affiliations AS a
		JOIN schools AS s on s.id=hs_id
		WHERE cc_id='.$cc_id.'
		ORDER BY school_name');
}

/**
 * Tries to find the external link to the specified drawing in the external_links table.
 * Filters out things like file:/// links and known testing URLs.
 * 
 * @param int $drawing_id
 * @param string $type 'post' or 'pathways'
 * @return string
 */
function getExternalDrawingLink($drawing_id, $type)
{
	global $DB;
	
	$drawing_id = intval($drawing_id);
	$type = ($type == 'pathways' ? 'pathways' : 'post');
	
	$link = $DB->SingleQuery('
		SELECT url
		FROM external_links 
		WHERE drawing_id = ' . $drawing_id . ' 
			AND type = "' . $type . '"
			AND `primary` = 1
		');

	// If there was no "best link" marked in the database, find one and mark it
	$url = FALSE;
	if($link == FALSE || count($link) == 0)
	{
		$links = getExternalDrawingLinks($drawing_id, $type);
		if(array_key_exists(0, $links))
		{
			$DB->Update('external_links', array('`primary`'=>1), $links[0]['id']);
			$url = $links[0]['url'];
		}
	}
	else
		$url = $link['url'];
	
	return $url;
}

function getExternalDrawingLinks($drawing_id, $type, $orderBy='counter DESC')
{
	global $DB;
	
	$drawing_id = intval($drawing_id);
	$type = ($type == 'pathways' ? 'pathways' : 'post');
	
	$links = $DB->MultiQuery('
		SELECT id, url, counter, last_seen, `primary`
		FROM external_links 
		WHERE drawing_id = ' . $drawing_id . ' 
			AND type = "' . $type . '"
			AND counter > 1
		ORDER BY ' . $orderBy, 'url');
	$exclude = $DB->VerticalQuery('SELECT pattern FROM external_link_exclude', 'pattern');
	
	$bestLinks = array();
	// start with the least likely, refining the search as we look through the list
	foreach($links as $link)
	{
		// if the link matches any of the excluded patterns, skip it
		foreach($exclude as $e)
			if(preg_match($e, $link['url']))
				continue 2;

		$bestLinks[] = $link;
	}
	return $bestLinks;
}


function drawing_not_found($type, $drawing_id=0, $version_id=0, $drawing_code='', $version=0)
{
	global $DB;
	
	$data = array(
		'date' => date('Y-m-d H:i:s'),
		'type' => $type,
		'request_uri' => $_SERVER['REQUEST_URI'],
		'query_string' => $_SERVER['QUERY_STRING'],
		'external_url' => array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : '',
		'drawing_id' => $drawing_id,
		'version_id' => $version_id,
		'version' => $version,
		'drawing_code' => $drawing_code,
		'counter' => 1
	);

	// Check if we already have a record of this request URI
	$check = $DB->SingleQuery('SELECT COUNT(1) AS num FROM external_link_errors WHERE request_uri = "' . $data['request_uri'] . '"');
	if($check['num'] > 0)
	{
		$DB->Query('UPDATE external_link_errors 
			SET counter = counter + 1, date = NOW()
			WHERE request_uri = "' . $data['request_uri'] . '"');
	}
	else
	{
		$DB->Insert('external_link_errors', $data);
	}
	
	header('HTTP/1.0 404 Not Found');
	echo 'Not found';
	die();
}


function drawing_sort_by_version($a,$b) {
	return $a['version_num'] < $b['version_num'];
}


function getbrightness($hex) {
	$hex = strtolower($hex);
	$r = hexdec(substr($hex, 0, 2));
	$g = hexdec(substr($hex, 2, 2));
	$b = hexdec(substr($hex, 4, 2));
	$brightness = $r + $g + $b;
	return $brightness;
}

function getdominantcolor($hex) {
	$hex = strtolower($hex);
	$r = hexdec(substr($hex, 0, 2));
	$g = hexdec(substr($hex, 2, 2));
	$b = hexdec(substr($hex, 4, 2));

	$max = max($r, $g, $b);
	if( ($r == $g) && ($g == $b) ) {
		return 'grey';
	}
	if( $r == $max ) {
		return 'r';
	}
	if( $g == $max ) {
		return 'g';
	}
	if( $b == $max ) {
		return 'b';
	}
}

/**
 * If $value is not null, returns $value. Otherwise returns $default
 */
function dv($value, $default='')
{
	return ( $value ? $value : $default );	
}

/*
 * Return a handle to the $DB object, easier than using `global $DB` everywhere
 */
function db()
{
	return $GLOBALS['DB'];
}

?>