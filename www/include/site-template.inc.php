<?php

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
				$func = $this->toolbar_function;
				$func();
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
		</ul>

		<?php
		if( $_SERVER['SERVER_NAME'] == 'oregon.ctepathways.org' ) {
		?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-8726801-2");
pageTracker._trackPageview();
} catch(err) {}</script>
		<?php
		}

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
			echo '<li' . ( $p == '/p/licensing' ? ' class="active"' : '' ) . '><a href="/p/licensing">Licensing</a></li>';
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
			'licensing' => "Licensing",
			'license_agreement' => "License Agreement",
			'help' => "Internal Help Text"
		);
	}

}

?>