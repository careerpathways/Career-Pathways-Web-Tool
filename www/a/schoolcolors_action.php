<?php
chdir("..");
include("inc.php");
require_once('colors.inc.php');

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

	} elseif( KeyInRequest('promptDelete') ) {
		
		echo '<div style="background-color: white; width: 100%;"><div style="padding-top:5px; padding-left:5px;">';

		//$color = $DB->SingleQuery('SELECT * FROM color_schemes WHERE school_id = ' . $school_id . ' AND hex = "' . strtolower($_REQUEST['color']) . '"');

        $color = $DB->SingleQuery('
        SELECT count(distinct dcc.id) as num_roadmaps,dcc.hexcolor as hex FROM (
            SELECT m.id as id , o.color as hexcolor
            FROM drawing_main as m INNER JOIN drawings as d ON m.id = d.parent_id
                              INNER JOIN objects as o ON o.drawing_id= d.id
		    WHERE m.school_id='.$school_id . '
		     AND o.color = \''. strtolower($_REQUEST['color']) .'\'
		UNION
				SELECT m.id as id, c.color as hexcolor
				FROM connections c
				INNER JOIN objects o ON c.source_object_id = o.id
				INNER JOIN drawings d ON o.drawing_id=d.id
				INNER JOIN drawing_main m ON d.parent_id = m.id
  				WHERE school_id = ' . $school_id . '
  		          AND c.color = \''. strtolower($_REQUEST['color']) .'\'
				) as dcc
		GROUP BY dcc.hexcolor
		'
        );


		echo '<div style="padding:20px;">';
			echo '<div style="background-color:#' . $color['hex'] . '; width:40px; height:40px; margin:0 auto;"></div>';
		echo '</div>';
		
		echo '<div style="padding-bottom:5px;">This color is currently being used by ' . $color['num_roadmaps'] . ' roadmaps. You must choose a new color for the objects currently using this color.</div>'; 
		
		$colors = $DB->MultiQuery('SELECT *
			FROM color_schemes
			WHERE school_id='.$school_id, 'hex');
		usort($colors, 'hslsort');

		$colors[] = array('hex'=>'333333', 'num_roadmaps'=>'');
		
		foreach($colors as $c)
		{
			if($c['hex'] != $color['hex'])
				echo '<div style="background-color:#' . $c['hex'] . '; width:40px; height:40px; float:left; margin-right:5px; margin-bottom:5px;">
					<input type="radio" name="reassign_color" class="reassign_color" value="' . $c['hex'] . '"' . ($c['hex'] == '333333' ? 'checked="checked"' : '') . ' />
				</div>';
		}
		
		echo '<div style="clear:both;"></div>';
		
		echo '<div style="padding: 10px;">';
			echo '<input type="button" value="Reassign and Delete" id="reassignBtn" class="submit" />';
		echo '</div>';
		
		echo '</div></div>';
		?>
		<script type="text/javascript">
			jQuery("#reassignBtn").click(function(){
				$(this).val("Please wait...").unbind("click").css({float: "left"}).after('<div style="float:left; margin-left:10px;"><img src="/images/e6d09e_loader.gif" width="43" height="11" /></div><div style="clear:both;"></div>');
				deleteColor(school_id, "<?=$color['hex']?>", $(".reassign_color:checked").val());
			});
		</script>
		<?php 
		die();
	} elseif( KeyInRequest('delete') && Request('color') ) {

		if(Request('replaceWith') && Request('replaceWith') != "undefined")
		{
			$drawing_ids = array();

			$objects = $DB->MultiQuery('
				SELECT o.*, d.id AS drawing_id
				FROM objects o
				JOIN drawings d ON o.drawing_id=d.id
				JOIN drawing_main m ON d.parent_id = m.id
				WHERE school_id = ' . $school_id . '
					AND color = "' . Request('color') . '"');
			foreach($objects as $o)
			{
				$content = unserialize($o['content']);
				$content['config']['color'] = Request('replaceWith');
				
				$data = array();
				$data['content'] = serialize($content);
				$data['color'] = Request('replaceWith');
				$DB->Update('objects', $data, $o['id']);
				
				if(!in_array($o['drawing_id'], $drawing_ids))
					$drawing_ids[] = $o['drawing_id'];
			}

            /*
			$objects = $DB->MultiQuery('
				SELECT o.*, d.id AS drawing_id
				FROM objects o
				JOIN drawings d ON o.drawing_id=d.id
				JOIN drawing_main m ON d.parent_id = m.id
				WHERE school_id = ' . $school_id . '
					AND color = "' . Request('color') . '"');
			foreach($objects as $o)
			{
				$content = unserialize($o['content']);
				$content['config']['color'] = Request('replaceWith');
				
				$data = array();
				$data['content'] = serialize($content);
				$data['color'] = Request('replaceWith');
				$DB->Update('objects', $data, $o['id']);
				
				if(!in_array($o['drawing_id'], $drawing_ids))
					$drawing_ids[] = $o['drawing_id'];
			}
            */

			$connections = $DB->MultiQuery('
				SELECT c.*, d.id AS drawing_id
				FROM connections c
				JOIN objects o ON c.source_object_id = o.id
				JOIN drawings d ON o.drawing_id=d.id
				JOIN drawing_main m ON d.parent_id = m.id
				WHERE school_id = ' . $school_id . '
					AND c.color = "' . Request('color') . '"');
			foreach($connections as $c)
			{
				$data = array();
				$data['color'] = Request('replaceWith');
				$DB->Update('connections', $data, $c['id']);
				
				if(!in_array($o['drawing_id'], $drawing_ids))
					$drawing_ids[] = $c['drawing_id'];
			}
			
			// This is not exact, but it will at least make the number go up so the number of the target color doesn't stay the same
			// The number will be corrected when the nightly script runs
			$DB->Query('UPDATE color_schemes SET num_roadmaps = num_roadmaps + ' . count($drawing_ids) . ' WHERE school_id = ' . $school_id . ' AND hex = "' . Request('replaceWith') . '"');
		}
		$DB->Query("DELETE FROM color_schemes WHERE school_id=".$school_id." AND hex='".substr(strtolower($_REQUEST['color']),0,6)."'");

	} else {

		if( Request('color') && !in_array(strtolower(Request('color')), array('ffffff','333333')) ) {
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

	$data = $DB->MultiQuery('SELECT *
		FROM color_schemes
		WHERE school_id='.$school_id, 'hex');
	usort($data, 'hslsort');
    $object_color_usage = $DB->ArrayQuery('
        SELECT count(distinct dcc.id) as count,dcc.hexcolor FROM (
            SELECT m.id as id , o.color as hexcolor
            FROM drawing_main as m INNER JOIN drawings as d ON m.id = d.parent_id
                              INNER JOIN objects as o ON o.drawing_id= d.id
		    WHERE m.school_id='.$school_id . '
		UNION
				SELECT m.id as id, c.color as hexcolor
				FROM connections c
				INNER JOIN objects o ON c.source_object_id = o.id
				INNER JOIN drawings d ON o.drawing_id=d.id
				INNER JOIN drawing_main m ON d.parent_id = m.id
				WHERE school_id = ' . $school_id . '
				) as dcc
		GROUP BY dcc.hexcolor
				',
        'hexcolor');

	$data[] = array('hex'=> 'ffffff', 'num_roadmaps'=>'&nbsp;');
	$data[] = array('hex'=> '333333', 'num_roadmaps'=>'&nbsp;');

	$colors = array();
	$usage = array();
	foreach( $data as $c ) {
		$colors[] = $c['hex'];
		$usage[] = (int)$object_color_usage[$c['hex']]['count'];
	}
	echo '({"request_mode":"request","colors":["'.implode('","',$colors).'"],"usage":["'.implode('","',$usage).'"]})';
}

function TheSort($a, $b) {
	return $a['sort'] > $b['sort'];
}

function hslsort($a, $b) 
{
	// Force white and grey to the end of the list
	if(in_array(strtolower($a['hex']), array('ffffff', '333333')))
		return TRUE;
	if(in_array(strtolower($b['hex']), array('ffffff', '333333')))
		return FALSE;
		
	$A = RGBtoHSL($a['hex']);
	$B = RGBtoHSL($b['hex']);
	return $A['H'] < $B['H'];
}


?>