<?php

abstract class SiteTemplate {

	private $NAV = array();               // this array holds the structure of the website, along with names of pages
	private $breadcrumb = array();        // handle_navigation() populates this array with url,name pairs to create the breadcrumb trail

	protected $stacked_menu_expand = true;  // whether to allow expanding of the stacked menu (showing submenu choices)

	function __construct() {
	global $SITE;

		$this->NAV['home'] = array('name'=>"Home", 'pages'=>array());

		$this->AddNavigation();
		$this->handle_navigation();
	}


	final private function handle_navigation() {
	global $SITE;

		$url = substr($_SERVER['PHP_SELF'], strlen($SITE->root()) );
		$url = str_replace("index.php","",$url);


		// split up the $where string on slashes
		$path = explode('/',$url);
		if( $path[count($path)-1] == "" ) array_pop($path); // remove the last blank element if it's there

		if( count($path) == 0 ) {
			// the visitor is on the home page
			$this->NAV['home']['active'] = 1;
		} else {
			// populate the breadcrumb array based on the current page
			$part = $this->NAV;
			$tag = &$this->NAV;  // tag the NAV array with "active" labels to indicate the current page. used by the menu generator to choose the style
			$url = "/";
			foreach( $path as $pos=>$step ) {
				if( array_key_exists($step,$part) ) {  // some pages like mainstyles.css aren't going to be in the nav array
					$tag[$step]['active'] = (count($path)-$pos);
					$url .= $step.'/';
					$this->breadcrumb[] = array('url'=>$url,'name'=>$part[$step]['name']);

					$tag = &$tag[$step]['pages'];
					$part = $part[$step]['pages'];
				}
			}
		}

	}




	final public function PrintHeader() {
	// Main function to print all parts of the whole site header including nav bars
		global $SITE;

echo $this->tag_doctype()."\n";
?>
<html<?= $this->tag_htmlprops() ?>>
<head>
<title><?= $this->CreatePageTitle() ?></title>
<script src="<?= $SITE->root() ?>common/functions.js" type="text/javascript"></script>
<?php
		$this->HeaderScripts();
?>
</head>
<body>
		<?php
		$this->Header();
	}

	final public function PrintFooter() {
	// Main function to print the footer

		$this->Footer();
		?>
</body>
</html>
		<?php
	}


	protected function tag_doctype() {
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
	}
	protected function tag_htmlprops() {
		return "";
	}



	protected function Header() {
		// should provide the header template code
	}

	protected function Footer() {
		// should provide the footer template code
	}

	protected function HeaderScripts() {
		// override this method to add script or other tags into the header
	}

	protected function AddNavigation() {
		// override this method to add the site structure
		// e.g. $this->AddPage('classes',array('name'=>"Classes"));
	}




	final public function AddCrumb($url, $name) {
		// dynamic pages can call this to add pages to the breadcrumb trail

		$this->breadcrumb[] = array('url'=>$url, 'name'=>$name);
	}


	final public function AddPage($where, $array) {
		// dynamic pages can call this to add pages to the nav menu


		$array['pages'] = array();

		// split up the $where string on slashes
		$path = explode('/',$where);

		$part = &$this->NAV;

		foreach( $path as $pos=>$step ) {
			if( $pos == count($path)-1 ) {

				$part[$step] = $array;

			} else {
				$part = &$this->NAV[$path[$pos]]['pages'];
			}
		}

	}

	final public function SuppressHighlight($parent) {
		// If a (database-driven) page has added things to the nav menu, it may have flagged
		// one of the items as 'active'. If this is the case, we would not want its parent
		// to also be flagged. The page should call this function to clear that flag.
		$this->NAV[$parent]['suppress'] = true;
	}


	public function CreateBreadcrumb() {
		global $SITE;

		// don't show just the "home" breadcrumb if we're on the home page.
		if( $_SERVER['PHP_SELF'] == '/index.php' ) {
			return "&nbsp;";
		} else {

			$bc = $this->breadcrumb;
			array_unshift($bc,array('url'=>"/",'name'=>"Home"));

			$url = "";
			$breadcrumb = array();
			foreach( $bc as $i=>$b ) {
				$b['name'] = TrimString($b['name'],45);
				$url = $bc[$i]['url'];
				if( $i == count($bc)-1 ) {
					$breadcrumb[] = $b['name'];
				} else {
					$breadcrumb[] = '<a href="'.$url.'">'.$b['name'].'</a>';
				}
			}

			if( $SITE->reverse_breadcrumb() ) {
				$breadcrumb = array_reverse($breadcrumb);
			}

			return csl($breadcrumb, $SITE->breadcrumb_separator());
		}
	}



	function CreatePageTitle() {
		global $SITE;

		$names_ = $this->breadcrumb;
		array_unshift($names_, array('url'=>"",'name'=>$SITE->name()));

		if( $SITE->reverse_pagetitle() ) {
			$names_ = array_reverse($names_);
		}

		$names = array();
		foreach( $names_ as $name ) {
			$names[] = $name['name'];
		}

		return csl($names, $SITE->title_separator());
	}


	function CreateStackedMenu() {
		global $SITE;

		// print out all links at the first level
		// if one of them is marked "active", go and print all of its links
		// highlight the one item marked active=1

		if( $SITE->hide_home_link() == true ) {
			$this->NAV['home']['hide'] = true;
		}

		echo '<ul class="menu">'."\n";
		foreach( $this->NAV as $url1=>$page1 ) {
			if( $this->stacked_menu_expand == true ) {
				// if we are expanding submenus, then only the deepest level should be
				// marked as active.
				$selected1 = (array_key_exists('active',$page1) && $page1['active']==1);
			} else {
				// if we are not expanding menus, the first level should be marked active
				// if any of its sub pages are active
				$selected1 = (array_key_exists('active',$page1));
			}
			if( $url1 == 'home' ) {
				$url1 = '/';
			} else {
				$url1 = $SITE->root().$url1;
			}
			if( array_key_exists('suppress',$page1) ) {
				$selected1 = false;
			}

			if( !array_key_exists('hide',$page1) ) {
				echo '	<li'.($selected1?' class="selected"':'').'><a href="'.$url1.'">'.$page1['name'].'</a></li>'."\n";
				if( $this->stacked_menu_expand && (array_key_exists('active',$page1) || array_key_exists('always_expand',$page1)) ) {
					if( count($page1['pages']) > 0 ) {
						echo '	<ul class="nested">'."\n";
						foreach( $page1['pages'] as $url2=>$page2 ) {
							$selected2 = (array_key_exists('active',$page2) && $page2['active']==1);
							if( array_key_exists('suppress',$page2) ) {
								$selected2 = false;
							}
							echo '		<li'.($selected2?' class="selected"':'').'><a href="'.$url1.'/'.$url2.'">'.$page2['name'].'</a></li>'."\n";
						}
						echo '	</ul>'."\n";
					}
				}
			}
		}
		echo '</ul>';

		/*
		 ** SAMPLE MENU CODE generated above **
				<li><a href="#">Shopping</a></li>
					<ul>
						<li><a href="#">Yarn</a></li>
						<li class="selected"><a href="#">Needles</a></li>
						<li><a href="#">Quilts</a></li>
					</ul>
				<li><a href="#">Your Quilts</a></li>
				<li><a href="#">Classes</a></li>
					<ul>
						<li><a href="#">classes 1</a></li>
						<li><a href="#">classes 2</a></li>
						<li><a href="#">classes 3</a></li>
					</ul>
				<li><a href="#">Newsletter</a></li>
				<li><a href="/links/">Links</a></li>
			</ul>
		*/
	}

	function PrintPageTitle() {
		if( count($this->breadcrumb) > 0 ) {
			echo '<h1>';
			echo $this->breadcrumb[count($this->breadcrumb)-1]['name'];
			echo '</h1>';
		}
	}


	function ShowContent($page) {
	global $DB;

		echo $DB->GetValue('content','content',$page);
	}

}



?>