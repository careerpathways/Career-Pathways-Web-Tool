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
	function email_name() { return "Oregon CT Pathways"; }
	function email() { return "helpdesk@ctepathways.org"; }

	function recipient_email() { return "aaron@ctepathways.org"; }

	function __construct() {
		$this->DBname = 'pathways';
		$this->DBuser = 'pathways';
		$this->DBpass = 'pathways';

		$this->ConnectDB();

		$this->add_local_name('pathways','ccti.ctepathways.org');
	}

	function base_url() { return $_SERVER['SERVER_NAME']; }
	function cache_path() { return '/tmp/pathways/'; }

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
				<a href="/a/login.php<?= (IsLoggedIn()?'?logout':'') ?>">Log <?= (IsLoggedIn()?'Out':'In') ?></a>
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
			echo '<script type="text/javascript" src="/common/FCKeditor/fckeditor.js"></script>'."\n";
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
			'ada' => "ADA Compliance"
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
	return $_SESSION['email'] == 'guest';
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
	return preg_replace('/[^a-z0-9_]/i','',$code);
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
		$drawing = $DB->SingleQuery("SELECT drawing_main.*, drawings.*, drawings.id drawings_id, sk.title AS skillset
			FROM drawing_main
			JOIN drawings ON drawings.parent_id=drawing_main.id
			LEFT JOIN oregon_skillsets AS sk ON sk.id = drawing_main.skillset_id
			WHERE drawings.id=".$drawing_id);
	} elseif( $type == 'post' ) {
		$drawing = $DB->SingleQuery("SELECT post_drawing_main.*, post_drawings.*, post_drawings.id drawings_id
			FROM post_drawing_main, post_drawings
			WHERE post_drawings.parent_id=post_drawing_main.id
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
		case 'ccti':
			$draw_page = 'ccti_drawings.php';
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
			echo '<th colspan="4">Title</th>';
			echo '<th width="240">Last Modified</th>';
			echo '<th width="240">Created</th>';
			//echo '<th width="40">SVG</th>';

		foreach( $mains as $mparent ) {
			echo '<tr class="row0">';

			echo '<td><a href="'.$draw_page.'?action=drawing_info&id='.$mparent['id'].'" class="edit"><img src="/common/silk/cog.png" width="16" height="16"/></a></td>';
			echo '<td colspan="3" class="drawinglist_name">'.$mparent['name'].'</td>';
			$created = ($mparent['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$mparent['created_by']));
			$modified = ($mparent['last_modified_by']==array('name'=>'')?"":$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$mparent['last_modified_by']));
			echo '<td><span class="fwfont">'.($mparent['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$mparent['last_modified'])).'</span> <a href="/a/users.php?id='.$mparent['last_modified_by'].'">'.$modified['name'].'</a></td>';
			echo '<td><span class="fwfont">'.($mparent['date_created']==''?'':$DB->Date('Y-m-d f:i a',$mparent['date_created'])).'</span> <a href="/a/users.php?id='.$mparent['created_by'].'">'.$created['name'].'</a></td>';

			foreach( $mparent['drawings'] as $dr ) {
				echo '<tr class="'.($dr['published']==1?'published':'row1').'">';
					if( CanEditVersion($dr['id']) ) {
						if( $dr['published'] == 1 || $dr['frozen'] == 1 ) {
							$linktext = SilkIcon('picture.png');
						} else {
							$linktext = SilkIcon('pencil.png');
						}
					} else {
						$linktext = SilkIcon('picture.png');
					}

					echo '<td width="30">&nbsp;</td>';

					echo '<td width="160">';
						echo 'Version '.$dr['version_num'].' '.($dr['published']?'(published)':'');
						echo (!array_key_exists('note',$dr) || $dr['note']==''?"":' ('.$dr['note'].')');
					echo '</td>';

					echo '<td width="70">';
						echo '<a href="'.$draw_page.'?action=version_info&amp;version_id='.$dr['id'].'" class="edit" title="Version Settings">'.SilkIcon('wrench.png').'</a>';
					echo '</td>';

					echo '<td width="70">';
						echo '<a href="'.$draw_page.'?action=draw&amp;version_id='.$dr['id'].'" class="edit">'.$linktext.'</a>';
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
	
	$connections = $DB->MultiQuery('SELECT post_id, tab_name
		FROM vpost_links AS v
		JOIN post_drawing_main AS d ON v.post_id=d.id
		WHERE type="'.$type.'" AND vid='.$drawing_id);
	if( count($connections) == 0 )
	{
		echo '(none)';
		return;
	}
	echo '<table>';
	echo '<tr>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="280">Drawing Title</th>';
		echo '<th width="20">&nbsp;</th>';
		echo '<th width="130">Tab Name</th>';
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
			echo '<td><a href="javascript:preview_drawing(\''.$d['code'].'\')">' . SilkIcon('magnifier.png') . '</a></td>';
			echo '<td><input type="text" id="tabName_'.$c['post_id'].'" class="tabName" value="' . $c['tab_name'] . '" style="width:90px" /><input type="button" class="tabNameBtn" id="tabNameBtn_'.$c['post_id'].'" style="width:30px;font-size:9px;margin-left:2px;" value="Save" /></td>';
			echo '<td>' . $d['school_name'] . '</td>';
			echo '<td><span class="fwfont">'.($d['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$d['last_modified'])).'</span> ' . $d['modified_by'] . '</td>';
		echo '</tr>';
	}
	echo '</table>';
	
}


function drawing_sort_by_version($a,$b) {
	return $a['version_num'] > $b['version_num'];
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

	if( IsIE() && !strpos($_SERVER['HTTP_USER_AGENT'],'MSIE 7.0') ) {
		echo '<div style="font-size:19pt; font-weight: bold; color: #cf9d2b">Notice for Internet Explorer 6 Users</div>';
		echo '<p>IE 6 is not yet fully supported by this website. Most things will work, but you may experience slight glitches.</p>';
		echo '<p>We recommend using <a href="http://www.mozilla.com/en-US/firefox/">Firefox</a> or Internet Explorer 7 instead. Or you can continue logging in below.</p>';
	}

	?>

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


?>
