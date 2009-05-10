<?php
include("firstinclude.inc.php");

define('CPUSER_HIGHSCHOOL', 8);
define("CPUSER_STAFF", 16);
define("CPUSER_SCHOOLADMIN", 64);
define("CPUSER_WEBMASTER", 32);
define("CPUSER_STATEADMIN", 127);


class ThisSite extends SiteSettings {

	var $debug = true;

	function name() { return "Career Pathways Web Tool"; }
	function email_name() { return "Oregon CTE Pathways"; }
	function email() { return "helpdesk@ctepathways.org"; }

	function recipient_email() { return "aaron@ctepathways.org"; }

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
			if( !is_dir($base_dir . $folder) ) mkdir($base_dir . $folder, 0777);
		}
	
		return '/web/oregon.ctepathways.org/cache/' . $folder . '/';
	}

	function https_port() { return ""; }
	function https_server() { return $_SERVER['SERVER_NAME']; }
	function force_https_login() { return false; }

	function recaptcha_publickey() { return '6Ldg9wEAAAAAADD5_LekXYwr2W6xeSDvPSrn2ULE'; }
	function recaptcha_privatekey() { return '6Ldg9wEAAAAAAHq3SbV8Ko0VEpcUEzg-QFq1DIx6'; }
}



class ThisSiteTemplate extends SiteTemplate {

	public $active_section = '';
	public $toolbar_function = '';

	public $is_chart_page = false;

	public $addl_scripts = array();
	public $addl_styles = array();

	function __construct() {
		if( !defined('NOSESSION') ) {
			if( $_SESSION['user_level'] == -1 ) {
				$_SESSION['user_id'] = -1;
			}
		}
	}

	function AddNavigation() {

	}


	function Header() {
		?>
			<div id="header">
				<img src="/images/title.gif" width="828" height="61" alt="Career Pathways Web Tool" />
			</div>

			<div id="topbar"><div id="topbar_inside">
				<?php if( IsLoggedIn() ) echo "Welcome ".$_SESSION['first_name']." ".$_SESSION['last_name'].' &nbsp;&nbsp;&bull;&nbsp;&nbsp;'; ?>

				<?php if( IsLoggedIn() ) { ?>
					<?php
					if( $_SERVER['PHP_SELF'] == "/index.php" ) {
						$b = "<b>";
						$b_end = "</b>";
					} else {
						$b = "";
						$b_end = "";
					}
					?>
					<a href="/a/users.php?id=<?= $_SESSION['user_id'] ?>">My Account</a> &nbsp;&nbsp;&bull;&nbsp;&nbsp;
					<a href="/a/password.php?change">Change Password</a> &nbsp;&nbsp;&bull;&nbsp;&nbsp;
				<?php } else { ?>
					<a href="/a/password.php">Reset Password</a> &nbsp;&nbsp;&bull;&nbsp;&nbsp;
				<?php } ?>
				<?php if( array_key_exists('original_user_id', $_SESSION) ) { ?>
					<a href="/a/login.php?reset">Return to <?=$_SESSION['original_user_name']?></a>
				<?php } else { ?>
					<a href="/a/login.php<?= (IsLoggedIn()?'?logout':'') ?>">Log <?= (IsLoggedIn()?'Out':'In') ?></a>
				<?php } ?>
			</div></div>

			<div id="sideboxes">
			<?php if( IsLoggedIn() ) { ?>
			<div id="navbox">
				<div class="links">
				<ul>
				<?php
				$mods = GetCategoriesForUser($_SESSION['user_id']);
				if( strpos($_SERVER['REQUEST_URI'], '?') )
					$p = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
				else
					$p = $_SERVER['REQUEST_URI'];
				$p = str_replace('.php', '', basename($p));
				foreach( $mods as $mod ) {
					
					// TODO: this is a total hack until permissions work better
					if( $mod['internal_name'] == 'hs_settings' && $_SESSION['user_level'] >= 16 && $_SESSION['user_level'] < 127 )
						continue;
					
					$active = '';
					if( $mod['internal_name'] == $p )  $active = 'active';

					if( $mod['name'] != "--" ) {
						echo '<li class="'.$active.'"><a href="/a/'.$mod['internal_name'].'.php">'.$mod['name'].'</a></li>';
					} else {
						echo "<li>&nbsp;</li>";
					}
				}
				?>
				</ul>
				</div>
				<br />
			</div>
			<?php } ?>

			<?php
			if( $this->toolbar_function != '' ) {
				eval($this->toolbar_function.'();');
			}

			$this->PublicToolbar();
			?>
			</div> <!-- sideboxes -->

			<?php
			if( !$this->is_chart_page ) {
			?>
			<div id="main">
				<div id="main-c">
					<div id="module_name"><? $this->PrintPageTitle(); ?></div>
					<div id="main-c-in">
					<?php
			}
	}

	function Footer() {

			if( !$this->is_chart_page ) {
				?>
					</div>
				</div>
				<div id="helplink"><a href="/a/help.php">Questions/Problems?</a></div>
			</div>
			<?php
			}
			?>

		<ul id="contextMenu" class="contextMenu" style="display:none">
			<li class="cut"><a href="#cut">Cut</a></li>
			<li class="copy"><a href="#copy">Copy</a></li>
			<li class="paste"><a href="#paste">Paste</a></li>
			<li class="delete"><a href="#clear">Clear</a></li>
		</div>

		<?php
	}

	function HeaderScripts() {
		?>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<style type="text/css">@import "/styles.css";</style>
		<script src="/files/ajax.js" type="text/javascript"></script>
		<script src="/common/common.js" type="text/javascript"></script>
		<script src="/common/actb.js" type="text/javascript"></script>

		<!-- Core + Skin CSS -->
		<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.4.0/build/menu/assets/skins/sam/menu.css" />

		<!-- Dependencies -->
		<script type="text/javascript" src="http://yui.yahooapis.com/2.4.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
		<script type="text/javascript" src="http://yui.yahooapis.com/2.4.0/build/container/container_core-min.js"></script>

		<!-- Source File -->
		<script type="text/javascript" src="http://yui.yahooapis.com/2.4.0/build/menu/menu-min.js"></script>

		<?php
		if( $this->is_chart_page ) {
			//echo '<script type="text/javascript" src="/common/FCKeditor/fckeditor.js"></script>'."\n";
			echo '<script type="text/javascript" src="/common/tinymce/tiny_mce.js"></script>'."\n";
		}
		
		foreach( $this->addl_styles as $css )
		{
			echo '<style type="text/css">@import "' . $css . '";</style>' . "\n";
		}
		foreach( $this->addl_scripts as $js )
		{
			echo '<script type="text/javascript" src="' . $js. '"></script>'."\n";
		}

	}

	function tag_doctype() {
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
	"http://www.w3.org/TR/2001/REC-xhtml11-20010531/DTD/xhtml11-flat.dtd">';
	}

	function tag_htmlprops() {
		return ' xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"';
	}

	function PublicToolbar() {
		if( strpos($_SERVER['REQUEST_URI'], '?') )
			$p = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
		else
			$p = $_SERVER['REQUEST_URI'];

		echo '<div id="resourcebar">';
		if( IsLoggedIn() ) {
			echo '<div id="resourcebar_header"></div>';
		}
		echo '<div id="resourcebar_content" class="links">';
		echo '<ul>';
			echo '<li' . ( $p == '/p/tutorial' ? ' class="active"' : '' ) . '><a href="/p/tutorial">Tutorial</a></li>';
			echo '<li' . ( $p == '/p/release_info' ? ' class="active"' : '' ) . '><a href="/p/release_info">Release Info</a></li>';
			echo '<li' . ( $p == '/p/ada' ? ' class="active"' : '' ) . '><a href="/p/ada">ADA Compliance</a></li>';
			echo '<li' . ( $p == '/a/help' ? ' class="active"' : '' ) . '><a href="/a/help">Help Desk</a></li>';
		echo '</ul>';
		echo '</div><br /></div>';
	}

	function resource_categories() {
		return array(
			'dashboard' => "Dashboard",
			'welcome' => "Welcome",
			'tutorial' => "Tutorial",
			'release_info' => "Release Info",
			'ada' => "ADA Compliance",
			'help' => "Internal Help Text"
		);
	}

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
	$dirty_code = preg_replace('/\s+/','_',strtolower($title));

	// remove any character that is not a letter or number
	$clean_code = CleanDrawingCode($dirty_code);

	// remove any duplicate underscores
	$clean_code = preg_replace('/_+/','_',$clean_code);

	// get the coded version of the school abbreviation (removes spaces)
	$school_abbr = CleanDrawingCode($DB->GetValue('school_abbr','schools',$school_id));

	// this is the ideal code for this drawing. but it may already exist in the database
	$proposed_code = strtolower($school_abbr.'_'.$clean_code);

	// look for conflicting codes
	$code = $proposed_code;
	while( DrawingCodeAlreadyExists($code, $drawing_id, $mode) ) {
		// keep trying new codes until we get one that is unique
		$code = $proposed_code.'_'.rand(100,999);
	}

	return $code;
}

function CleanDrawingCode($code) {
	return strtolower(preg_replace('/[^a-z0-9_]+/i','_',$code));
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
		$drawing = $DB->SingleQuery("SELECT drawing_main.*, drawings.*, drawings.id drawings_id, sk.title AS skillset, school_name
			FROM drawing_main
			JOIN drawings ON drawings.parent_id=drawing_main.id
			LEFT JOIN oregon_skillsets AS sk ON sk.id = drawing_main.skillset_id
			LEFT JOIN schools ON drawing_main.school_id=schools.id
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


function ShowDrawingList(&$mains, $type='pathways') {
	global $DB;

	switch( $type )
	{
		case 'pathways':
			$draw_page = 'drawings.php';
			break;
		case 'post':
			$draw_page = 'post_drawings.php';
			break;		
	}

	if( count($mains) == 0 ) {
		echo '<p>(none)</p>';
	} else {
		echo '<table width="100%">';
		echo '<tr>';
			echo '<th colspan="4">Occupation/Program</th>';
			echo '<th width="240">Last Modified</th>';
			echo '<th width="240">Created</th>';
			//echo '<th width="40">SVG</th>';

		foreach( $mains as $mparent ) {
			echo '<tr class="drawing_main">';

			echo '<td><a href="'.$draw_page.'?action=drawing_info&id='.$mparent['id'].'" class="edit"><img src="/common/silk/cog.png" width="16" height="16" title="Drawing Properties" /></a></td>';
			echo '<td colspan="3" class="drawinglist_name">'.$mparent['name'].'</td>';
			$created = ($mparent['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$mparent['created_by']));
			$modified = ($mparent['last_modified_by']==array('name'=>'')?"":$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$mparent['last_modified_by']));
			echo '<td><span class="fwfont">'.($mparent['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$mparent['last_modified'])).'</span> <a href="/a/users.php?id='.$mparent['last_modified_by'].'">'.$modified['name'].'</a></td>';
			echo '<td><span class="fwfont">'.($mparent['date_created']==''?'':$DB->Date('Y-m-d f:i a',$mparent['date_created'])).'</span> <a href="/a/users.php?id='.$mparent['created_by'].'">'.$created['name'].'</a></td>';

			foreach( $mparent['drawings'] as $dr ) {
				echo '<tr class="'.($dr['published']==1?'published':'').'">';
					$drawViewText = 'Draw/Edit Version';
					if( CanEditVersion($dr['id'], $type) ) {
						if( $dr['published'] == 1 || $dr['frozen'] == 1 ) {
							$linktext = SilkIcon('picture.png');
						} else {
							$linktext = SilkIcon('pencil.png');
						}
					} else {
						$linktext = SilkIcon('picture.png');
						$drawViewText = 'View Version';
					}

					echo '<td width="30">&nbsp;</td>';

					echo '<td width="160">';
						echo 'Version '.$dr['version_num'].' ';
						if( $dr['published'] == 1 )
							echo '<img src="/common/silk/report.png" width="16" height="16" title="Published Version" />';
						echo (!array_key_exists('note',$dr) || $dr['note']==''?"":' ('.$dr['note'].')');
					echo '</td>';

					echo '<td width="70">';
						echo '<a href="'.$draw_page.'?action=version_info&amp;version_id='.$dr['id'].'" class="edit" title="Version Settings">'.SilkIcon('wrench.png').'</a>';
					echo '</td>';

					echo '<td width="70">';
						echo '<a href="'.$draw_page.'?action=draw&amp;version_id='.$dr['id'].'" class="edit" title="'.$drawViewText.'">'.$linktext.'</a>';
					echo '</td>';


					$created = ($dr['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$dr['created_by']));
					$modified = ($dr['last_modified_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$dr['last_modified_by']));
					echo '<td><span class="fwfont">'.($dr['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$dr['last_modified'])).'</span> <a href="/a/users.php?id='.$dr['last_modified_by'].'">'.$modified['name'].'</a></div></td>';
					echo '<td><span class="fwfont">'.($dr['date_created']==''?'':$DB->Date('Y-m-d f:i a',$dr['date_created'])).'</span> <a href="/a/users.php?id='.$dr['created_by'].'">'.$created['name'].'</a></td>';

					//echo '<td>';
					//	echo '<a href="/files/charts/svg/'.$dr['id'].'.svg"><img src="/images/svg_icon.png" width="16" height="12"></a>';
					//echo '</td>';
				echo '</tr>';
			}

			echo '</tr>';
		}


		echo '</tr>';
		echo '</table>';
	}

}

function ShowSmallDrawingConnectionList($drawing_id, $type=null, $links=array())
{
	global $DB;
	
	$connections = $DB->MultiQuery('SELECT post_id, tab_name, sort
		FROM vpost_links AS v
		JOIN post_drawing_main AS d ON v.post_id=d.id
		WHERE type="'.$type.'" AND vid='.$drawing_id.' ORDER BY sort');
	if( count($connections) == 0 )
	{
		echo '(none)';
		return;
	}
	echo '<table>';
	echo '<tr>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="280">Occupation/Program</th>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th>Tab Name</th>';
		echo '<th>Sort</th>';
		echo '<th width="180">Organization</th>';
		echo '<th width="285">Last Modified</th>';
	echo '</tr>';
	foreach( $connections as $c )
	{
		$d = $DB->SingleQuery('SELECT M.*, CONCAT(U.first_name," ",U.last_name) AS modified_by, schools.school_name
			FROM post_drawing_main M
			JOIN post_drawings D ON D.parent_id=M.id
			LEFT JOIN users U ON M.last_modified_by=U.id
			LEFT JOIN schools ON M.school_id=schools.id
			WHERE M.id='.intval($c['post_id']).'
			ORDER BY name');

		echo '<tr>';
			echo '<td><a href="'.str_replace('%%', $d['id'], $links['delete']).'">' . SilkIcon('cross.png') . '</a></td>';
			echo '<td>' . $d['name'] . '</td>';
			echo '<td><a href="javascript:preview_drawing(\''.$d['code'].'\')" title="View Version">' . SilkIcon('magnifier.png') . '</a></td>';
			echo '<td width="90">';
				echo '<input type="text" id="tabName_'.$c['post_id'].'" class="tabName tabID_'.$c['post_id'].'" value="' . $c['tab_name'] . '" style="width:90px" />';
			echo '</td>';
			echo '<td width="60">';
				echo '<input type="text" id="tabSort_'.$c['post_id'].'" class="tabSort tabID_'.$c['post_id'].'" value="' . $c['sort'] . '" style="width:20px" />';
				echo '<input type="button" class="tabNameBtn" id="tabNameBtn_'.$c['post_id'].'" style="width:30px;font-size:9px;margin-left:2px;" value="Save" />';
			echo '</td>';
			echo '<td>' . $d['school_name'] . '</td>';
			echo '<td><span class="fwfont">'.($d['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$d['last_modified'])).'</span> ' . $d['modified_by'] . '</td>';
		echo '</tr>';
	}
	echo '</table>';
	
}


function drawing_sort_by_version($a,$b) {
	return $a['version_num'] > $b['version_num'];
}

function ShowBrowserNotice()
{
	$browser = $_SERVER['HTTP_USER_AGENT'];
	$notice = '';

	if( preg_match('~Firefox/(1|2)~', $browser) )
		$notice = "You appear to be using an old version of Firefox. We recommend you upgrade to the <a href=\"http://www.mozilla.com/firefox/\">latest version of Firefox</a> in order to have full access to the web tool.";

	if( preg_match('~Firefox~', $browser) == 0 )
		$notice = "We recommend using <a href=\"http://www.mozilla.com/firefox/\">Firefox</a> for the best experience with the web tool.";

	if( preg_match('~MSIE (6|5)~', $browser) )
		$notice = "Internet Explorer 6 is not supported by this website. Most features should work, but you may experience glitches. To avoid this, we recommend using the latest version of <a href=\"http://www.mozilla.com/firefox/\">Firefox</a> or <a href=\"http://www.microsoft.com/windows/Internet-explorer/default.aspx\">Internet Explorer</a>";

	if( preg_match('~MSIE 7~', $browser) )
		$notice = "You appear to be using Internet Explorer. We recommend you use <a href=\"http://www.mozilla.com/firefox/\">Firefox</a> for the best experience with the web tool.";

	if( $notice != '' )
	{
	?>
	<div id="browserNotice">
		<div style="float:left;"><a href="http://www.mozilla.com/firefox/"><img src="/images/firefox-logo.png" /></a></div>
		<div style="margin-left:100px;"><?= $notice ?></div>
	</div>
	<div style="clear:right"></div>
	<?php
	}
}



include("general.inc.php");
include("admin_inc.php");
include("json_encode.php");


function logmsg($message) {
	$fp = fopen('../log.txt','a');
	fwrite($fp, date("Y-m-d H:i:s").' '.$_SERVER['REMOTE_ADDR'].' ['.$_SERVER['PHP_SELF'].'] u:'.$_SESSION['user_id'].' '.$message."\n");
	fclose($fp);
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


function ShowLoginForm($email="") {
global $SITE;

	if( $SITE->force_https_login() && !$SITE->is_aaronsdev() ) {
		$form_action = "https://".$SITE->https_server().':'.$SITE->https_port()."/a/login.php";
	} else {
		$form_action = "/a/login.php";
	}

	?>
	<div style="margin-right:20px">
	<?php ShowBrowserNotice(); ?>
	</div>

	<br /><br />
	<form action="<?= $form_action; ?>" method="post">
	<table align="center">
	<tr>
		<td>Email:</td>
		<td><input type="text" size="20" name="email" id="email" value="<?= $email; ?>"></td>
	</tr>
	<tr>
		<td>Password:</td>
		<td><input type="password" size="20" name="password" id="password"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" value="Log In" class="submit"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><br/><br/><span class="button_link"><a href="/a/guestlogin.php">Guest Login</a></span></td>
	</tr>
	</table>

	<input type="hidden" name="next" value="<?= (Request('next')) ?>">
	</form>

	<?php
	echo str_repeat('<br/>',20);
}




function showPublishForm($mode)
{
	global $DB;
	$version_id = intval($_REQUEST['version_id']);
	if( $mode == 'post' )
		$version = $DB->SingleQuery('SELECT * FROM post_drawings WHERE id='.$version_id);
	else
		$version = $DB->SingleQuery('SELECT * FROM drawings WHERE id='.$version_id);
	
	?>
	<div style="border: 1px solid rgb(119, 119, 119); margin-left: 15px; margin-right: 15px; background-color: white; padding: 15px;">
	<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
		<p>Are you sure you want to publish this version? Any web pages that are embedding this drawing will automatically be updated.</p>
		<input type="submit" class="submit" value="Yes" />
		<input type="button" class="submit" value="No" onclick="chGreybox.close()" />

		<input type="hidden" name="action" value="publish" />
		<input type="hidden" name="drawing_id" value="<?=$version_id?>" />

		<?php
		if( $version['frozen'] == 0 ) {
			echo '<br /><br /><p>Note: You can lock this version instead. This will prevent anyone from making changes to this version, but will not update websites that have embedded this drawing.</p>';
		}
		?>

	</form>
	</div>
	<?php
}

?>
