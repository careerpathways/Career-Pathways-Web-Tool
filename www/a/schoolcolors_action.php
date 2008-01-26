<?php
chdir("..");
include("inc.php");

ModuleInit('schoolcolors');


if( Request('sid') ) {

	if( IsAdmin() ) {
		if( Request('sid') ) {
			$school_id = $_REQUEST['sid'];
		} else {
			$school_id = $_SESSION['school_id'];
		}
	} else {
		$school_id = $_SESSION['school_id'];
	}


	if( KeyInRequest('num') ) {

		$num = $DB->SingleQuery("SELECT COUNT(*) AS num FROM color_schemes WHERE school_id=".$school_id);
		echo $num['num'];
		die();

	} elseif( KeyInRequest('delete') && Request('color') ) {

		$DB->Query("DELETE FROM color_schemes WHERE school_id=".$school_id." AND hex='".substr(strtolower($_REQUEST['color']),0,6)."'");

	} else {

		if( Request('color') ) {
			$_REQUEST['color'] = str_replace('#','',$_REQUEST['color']);

			$check = $DB->SingleQuery("SELECT * FROM color_schemes WHERE school_id=".$school_id." AND hex='".substr($_REQUEST['color'],0,6)."'");
			if( !is_array($check) ) {
				$content = Array( 'school_id' => $school_id,
								  'hex' => substr(strtolower($_REQUEST['color']),0,6),
								);
				$DB->always_alpha[] = 'hex';
				$DB->Insert('color_schemes',$content);
			}
		}
	}

	$colors = $DB->MultiQuery("SELECT * FROM color_schemes WHERE school_id=".$school_id);

		$group = array('grey'=>array(), 'r'=>array(), 'g'=>array(), 'b'=>array());
		foreach( $colors as $c ) {
			$group[getdominantcolor($c['hex'])][] = array('obj'=>$c, 'sort'=>getbrightness($c['hex']));
		}

		usort($group['grey'], 'TheSort');
		usort($group['r'], 'TheSort');
		usort($group['g'], 'TheSort');
		usort($group['b'], 'TheSort');

		$colors_ = array();
		foreach( $group['grey'] as $c ) {
			$colors_[] = $c['obj'];
		}
		foreach( $group['r'] as $c ) {
			$colors_[] = $c['obj'];
		}
		foreach( $group['g'] as $c ) {
			$colors_[] = $c['obj'];
		}
		foreach( $group['b'] as $c ) {
			$colors_[] = $c['obj'];
		}

	$colors = array();
	foreach( $colors_ as $c ) {
		$colors[] = $c['hex'];
	}
	$colors[] = '333333';
	echo '({"request_mode":"request","colors":["'.implode('","',$colors).'"]})';
}

function TheSort($a, $b) {
	return $a['sort'] > $b['sort'];
}


?>
