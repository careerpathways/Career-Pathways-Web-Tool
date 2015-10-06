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

		$request_color = strtolower($_REQUEST['color']);

		$num_roadmaps = count(getCountRoadmapsUsingColor($request_color, $school_id));

		echo '<div style="padding:20px;">';
			echo '<div style="background-color:#' . $request_color . '; width:40px; height:40px; margin:0 auto;"></div>';
		echo '</div>';

		echo '<div style="padding-bottom:5px;">This color is currently being used by <b>' . $num_roadmaps . '</b> roadmaps. You must choose a new color for the objects currently using this color.</div>'; 
		
		$colors = $DB->MultiQuery('SELECT *
			FROM color_schemes
			WHERE school_id='.$school_id, 'hex');
		usort($colors, 'hslsort');

		$colors[] = array('hex'=>'333333', 'num_roadmaps'=>'');
		
		foreach($colors as $c)
		{
			if($c['hex'] != $request_color)
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
				deleteColor(school_id, "<?= $request_color ?>", $(".reassign_color:checked").val());
			});
		</script>
		<?php 
		die();
	} elseif( KeyInRequest('delete') && Request('color') ) {

		if(Request('replaceWith') && Request('replaceWith') != "undefined")
		{
			$drawing_ids = array();

			$objects = getObjectsUsingColor(Request('color'), $school_id);
			foreach($objects as $o)
			{
				$content = unserialize($o['content']);
				if(isset($content['config']['color']) && $content['config']['color'] == Request('color')){
					$content['config']['color'] = Request('replaceWith');
				}
				if(isset($content['config']['color_background']) && $content['config']['color_background'] == Request('color')){
					$content['config']['color_background'] = Request('replaceWith');
				}
				
				$data = array();
				$data['content'] = serialize($content);
				$data['color'] = 'null'; //No longer used because drawings have multiple color properties now.
				$DB->Update('objects', $data, $o['id']);
				
				if(!in_array($o['drawing_id'], $drawing_ids))
					$drawing_ids[] = $o['drawing_id'];
			}

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

	$data[] = array('hex'=> 'ffffff', 'num_roadmaps'=>'&nbsp;');
	$data[] = array('hex'=> '333333', 'num_roadmaps'=>'&nbsp;');

	$colors = array();
	$usage = array();
	foreach( $data as $c ) {
		$colors[] = $c['hex'];
		$usage[] = (int) count(getCountRoadmapsUsingColor($c['hex'],$school_id));
	}
	echo '({"request_mode":"request","colors":["'.implode('","',$colors).'"],"usage":["'.implode('","',$usage).'"]})';
}

/**
 * Get the Roadmap drawings using a given color.
 * Includes objects using the color as a border color or background color.
 * @param  string $hex       6-digit color, without the '#'.
 * @param  int $school_id 	 The school id to narrow the query on.
 * @return array of drawing ids
 */
function getCountRoadmapsUsingColor($hex, $school_id) {
	global $DB;
	$query = 'SELECT drawing_main.id
			FROM objects
				LEFT JOIN drawings on objects.drawing_id = drawings.id
				LEFT JOIN drawing_main on drawings.parent_id = drawing_main.id
			WHERE
				(objects.content LIKE \'%s:16:"color_background";s:6:"'.$hex.'";%\'
			OR
				objects.content LIKE \'%s:5:"color";s:6:"'.$hex.'";%\')
			AND
				drawing_main.school_id = "'.$school_id.'"
			GROUP BY
				drawings.parent_id';
	return $DB->MultiQuery($query);
}

/**
 * Get objects using a given color.
 * Includes objects using the color as a border color or background color.
 * @param  string $hex       6-digit color, without the '#'.
 * @param  int $school_id 	 The school id to narrow the query on.
 * @return array of "objects" and drawing ids.
 */
function getObjectsUsingColor($hex, $school_id) {
	global $DB;
	$query = 'SELECT o.*, d.id AS drawing_id
			FROM objects o
				LEFT JOIN drawings d ON o.drawing_id=d.id
				LEFT JOIN drawing_main m ON d.parent_id = m.id
			WHERE
				(o.content LIKE \'%s:16:"color_background";s:6:"'.$hex.'";%\'
			OR
				o.content LIKE \'%s:5:"color";s:6:"'.$hex.'";%\')
			AND
				school_id = ' . $school_id;
	return $DB->MultiQuery($query);
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