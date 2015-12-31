<?php
if( !defined('NOSESSION') ) {
	session_start();
	// Make sure everyone has a user level for security
	if( !(array_key_exists("user_level", $_SESSION)) || $_SESSION['user_level'] == "" ) {
		$_SESSION['user_level']="-1";
		$_SESSION['session_created'] = date("Y-m-d H:i:s");
	}
	$_SESSION['session_last_seen'] = date("Y-m-d H:i:s");
	$_SESSION['session_remote_addr'] = (array_key_exists('REMOTE_ADDR',$_SERVER)?$_SERVER['REMOTE_ADDR']:'127.0.0.1');
}

include("grammar.inc.php");
include("wordgen.inc.php");
//include("class.phpmailer.php");
include("time.inc.php");
include("form.inc.php");
include("formatting.inc.php");
include("request.inc.php");
include("table.inc.php");
include("util.inc.php");
include("email.inc.php");
include("shopping.inc.php");
include("states.inc.php");


// define user level constants
define("USER_ANONYMOUS", -1);
define("USER_MEMBER", 16);
define("USER_STAFF", 64);
define("USER_ADMIN", 127);

$SITE = new ThisSite();
$DB = $SITE->GetDBH();

$TEMPLATE = new ThisSiteTemplate();


if($DB == FALSE) {
	if(substr($_SERVER['REQUEST_URI'], -3) == '.js' || substr($_SERVER['REQUEST_URI'], -4) == '.css') {
?>
/**
 * Error: Could not connect to the database.
 * Verify the database username and password are set properly in the configuration file.
 */
<?php
	} else {
		PrintHeader();
		echo '<div style="font-size: 15pt; margin-bottom: 9px; color: #900;">Error: Could not connect to the database.</div>';
		echo '<div style="font-size: 8pt;">Verify the database username and password are set properly in the configuration file.</div>';
		PrintFooter();
	}
	die();
}


function PrintHeader() {
	global $TEMPLATE;
	$TEMPLATE->PrintHeader();
}
function PrintFooter() {
	global $TEMPLATE;
	$TEMPLATE->PrintFooter();
}


include("dborder.inc.php");
$DBO = new DB_order($DB);

require_once('Amazon-SES-Mailer-PHP/AmazonSESMailer.php');
require_once(dirname(dirname(__FILE__)).'/vendor/swiftmailer/swiftmailer/lib/swift_required.php');
include("siteemail.inc.php");



//************************
// IsLoggedIn()
//
// returns true or false depending on whether
// the user is logged in
//************************
if( !function_exists('IsLoggedIn') ) {
	function IsLoggedIn() {
		if( $_SESSION['user_level'] > USER_ANONYMOUS ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}



function PA($a, $color='', $title='') {
	if( $color == '' )
		echo '<pre>';
	else
		echo '<pre style="background-color:#' . $color . '">';

	if( $title != '' ) echo '<b>' . $title . '</b><br />';
	print_r($a);
	echo "</pre>";
}


function PT($a, $color='FFFFFF', $title='') {
	echo '<pre style="background-color:#' . $color . '">';
	if( $title != '' ) echo '<b>' . $title . '</b><br />';
	echo '<table border="1">';
	foreach( $a as $r )
	{
		echo '<tr>';
		foreach( $r as $c )
		{
			if( is_array($c) )
			{
				echo '<td>';
				pa($c);
				echo '</td>';
			}
			else
				echo '<td>'.(trim($c)==''?'&nbsp;':$c).'</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
	echo "</pre>";
}



function PT2($a) {
	echo '<table border="1" cellpadding="2" style="border-collapse:collapse">';
	foreach( $a as $num=>$row ) {
		if( $num == 0 ) {
			echo '<tr>';
			foreach( $row as $field=>$val ) {
				echo '<td>'.$field.'</td>';
			}
			echo '</tr>';
		}
		echo '<tr>';
		foreach( $row as $field=>$val ) {
			echo '<td>';
			if( 0 && is_array($val) ) {
				pa($val);
			} else {
				if( $val == '' ) {
					echo '&nbsp;';
				} else {
					echo $val;
				}
			}
			echo '</td>';
		}
		echo '</tr>';
	}
	echo '</table>';
}


function IsIE() {
	if( strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') > 0 ) {
		return true;
	} else {
		return false;
	}
}


function FileName($str) {
	return substr($str,strrpos($str,"/")+1);
}

function FileExt($str) {
	return strtolower(substr($str,-3));
}



function PageIsIndex() {
	return $_SERVER['PHP_SELF'] == "/index.php";
}

function IsAdminPage() {
	$page = str_replace('/',"",$_SERVER['PHP_SELF']);

	$a = Array("modules", "admin", "login.php", "password.php");

	$admin = false;

	foreach( $a as $b ) {
		if( substr($page,0,strlen($b)) == $b ) {
			$admin = true;
		}
	}

	return $admin;
}

function ArraySum($arr, $col) {
	$sum = 0;
	foreach($arr as $el) {
		$sum += $el[$col];
	}
	return $sum;
}

function VerifyEmail($email) {
// just do a basic email check...doesn't have to be fancy,
//    if they enter the wrong email too bad for them

	if( strstr($email,"@") && strstr($email,".") ) {
		return true;
	} else {
		return false;
	}
}

function RedirectSecure() {
global $SITE;
	if( !$SITE->is_aaronsdev() && $SITE->force_https_login() ) {
		if( !array_key_exists('HTTPS',$_SERVER) ) {
			if( $SITE->https_port() != "" ) {
				$colon = ":";
			} else {
				$colon = "";
			}
			$secure = "https://".$SITE->https_server().$colon.$SITE->https_port().$_SERVER['REQUEST_URI'];
	#		header("Location: $secure");
	#		die();
		}
	}
}







function PrintCalendarItemForm($id, $show_sku=false) {
global $DB;

	$info = $DB->SingleQuery("SELECT * FROM calendar_item WHERE id=$id");

	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<table>
	<?php
		if( $show_sku ) {
		?>
		<tr>
			<td>Item ID</td>
			<td colspan="2"><input type="textbox" name="sku" style="width:100px" value="<?php echo $info['sku']; ?>"></td>
		</tr>
		<?php
		}
	?>
		<tr>
			<td>Date Description<br><div class="small">This is what you see<br>when looking at the class<br>or event description</div></td>
			<td colspan="2"><textarea name="date_desc" rows="4" cols="40"><?= ($info['date_desc']) ?></textarea></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" value="Submit" class="submit"></td>
			<td align="right">
				<?php if( $id != "" ) { ?>
					Delete: <select name="delete"><option value="">-------</option><option value="delete">Delete</option></select>
				<?php } else { ?>
					&nbsp;
				<?php } ?>
			</td>
		</tr>
	</table>
	<imput type="hidden" name="id" value="<?= $info['table_id'] ?>">
	<input type="hidden" name="d" value="<?= $id ?>">
	</form>
	<?php
}


function PrintItemDateForm($id="") {
global $DB;

	if( $id == '' ) {
		$data['item_id'] = '';
		$data['date'] = '';
		$data['start_time'] = '';
		$data['end_time'] = '';
	} else {
		$data = $DB->SingleQuery("SELECT * FROM calendar_item_date WHERE id=$id");
	}
	?>

	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="idateform">

	<td valign="top">
		<input type="submit" class="submit" value="<?= $id==''?'Add':'Save' ?>" style="display:none">
		<?php if( $id=="" ) { ?>
		<a href="javascript:getLayer('idateform').submit()" class="edit" title="Save">S</a>
		<?php } else { ?>
		<a href="javascript:getLayer('idateform').submit()" class="edit" title="Save">S</a>
		<a href="<?= $_SERVER['PHP_SELF'].'?id='.$_REQUEST['id'].'&d='.$_REQUEST['d'].'&di='.$id.'&delete' ?>" class="edit" title="Delete">D</a>
		<?php } ?>
	</td>

	<td valign="top"><input type="text" name="date" id="date" size="11" value="<?= $data['date'] ?>"></td>
	<td valign="top"><input type="text" name="start_time" id="start_time" size="11" value="<?= $data['start_time'] ?>"></td>
	<td valign="top"><input type="text" name="end_time" id="end_time" size="11" value="<?= $data['end_time'] ?>"> <span class="small">(24-hour time)</span></td>

	<input type="hidden" name="di" value="<?= $id ?>">
	<input type="hidden" name="id" value="<?= $_REQUEST['id'] ?>">
	<input type="hidden" name="d" value="<?= $_REQUEST['d'] ?>">
	</form>
	<?php
}


function SilkIcon($name) {
	return '<img src="/common/silk/'.$name.'" width="16" height="16" />';
}
