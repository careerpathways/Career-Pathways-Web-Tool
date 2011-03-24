<?php
chdir("..");
require_once "Text/Wiki.php";
include("inc.php");


ModuleInit('drawings');


// permissions checks
// SuperAdmins and people of the same school can edit drawings

$drawing = $DB->SingleQuery("SELECT *
	FROM drawings, drawing_main
	WHERE drawings.parent_id=drawing_main.id
	AND drawings.id=".Request('version_id'));
if( !is_array($drawing) || (!IsAdmin() && $_SESSION['school_id'] != $drawing['school_id']) ) {
	chlog("permissions error");
	die();
}





$debug = false;

switch( $_REQUEST['a'] ) {
	case 'new':

		$obj = array();
		$obj['drawing_id'] = Request('version_id');

		// insert the new object, and get the auto_increment value
		$obj['content'] = serialize($_REQUEST['content']);
		$obj['color'] = (array_key_exists('color', $_REQUEST['content']['config']) ? $_REQUEST['content']['config']['color'] : '333333');
		$id = $DB->Insert('objects', $obj);

		// quickly update the object's content, inserting its id into the variable structure.
		//    this is so that in case the created object is not moved in the future, the id is still stored in the database
		$newcontent = $_REQUEST['content'];
		$newcontent['id'] = $id;
		$updobj['content'] = serialize($newcontent);
		$DB->Update('objects', $updobj, $id);

		chlog("created new object: id=$id:".$updobj['content']);

		echo $id;

	break;
	case 'update':
		if($debug) ob_start();

		if($debug) echo "Full Request:\n";
		if($debug) PA($_REQUEST);

		$type = Request('type');

		if ($type === 'connection') {
			$properties = array();
			if (isset($_REQUEST['num_segments'])) {
				$properties['num_segments'] = intval($_REQUEST['num_segments']);
			}
			if (isset($_REQUEST['source_side'])) {
				$properties['source_side'] = $_REQUEST['source_side'];
			}
			if (isset($_REQUEST['source_position'])) {
				$properties['source_position'] = $_REQUEST['source_position'];
			}
			if (isset($_REQUEST['destination_side'])) {
				$properties['destination_side'] = $_REQUEST['destination_side'];
			}
			if (isset($_REQUEST['destination_position'])) {
				$properties['destination_position'] = $_REQUEST['destination_position'];
			}
			if (isset($_REQUEST['color'])) {
				$properties['color'] = $_REQUEST['color'];
			}
			if (isset($_REQUEST['source_axis'])) {
				if ($_REQUEST['source_axis'] === 'x') {
					$sourceAxis = 'x';
				}
				else {
					$sourceAxis = 'y';
				}
				$properties['source_axis'] = $sourceAxis;
			}

			if (count($properties) > 0) {
				$DB->Update('connections', $properties, intval($_REQUEST['id']));
			}
		}
		else {
			$json_obj = $DB->GetValue('content','objects',intval($_REQUEST['id']));

			if($debug) echo "Existing object:\n";
			$obj = unserialize($json_obj);
			if($debug) PA($obj);

			if($debug) echo "New values to be merged:\n";
			if($debug) PA($_REQUEST['content']);

			foreach( $_REQUEST['content'] as $key=>$val ) {
				if( is_array($val) ) {
					foreach( $val as $key2=>$val2 ) {
						$obj[$key][$key2] = $val2;
					}
				} else {
					$obj[$key] = $val;
				}
			}

			$obj['id'] = intval($_REQUEST['id']);

			if($debug) echo "Resulting object:\n";
			if($debug) PA($obj);

			$objstr = serialize($obj);

			$color = $obj['config']['color'];
			
			chlog("updated object: ".$obj['id']);

			$DB->Update('objects', array('content'=>$objstr, 'color'=>$color, 'date'=>$DB->SQLDate()), intval($_REQUEST['id']));

		}
		if($debug) {
			$v = ob_get_contents();
			ob_end_clean();
			chlog($v);
		}
	break;
	case 'remove':
		$objectId = intval($_REQUEST['id']);
		chlog("deleted object: $objectId");
		$DB->Query("DELETE FROM objects WHERE id=$objectId");
		chlog("deleted connections for object:  $objectId");
		$DB->Query("DELETE FROM connections WHERE source_object_id=$objectId OR destination_object_id=$objectId");
	break;
	case 'connect': //Connect two boxes. IE, Link them.
		$sourceId = intval($_REQUEST['source_id']);
		$destinationId = intval($_REQUEST['destination_id']);

		//check if this connection already exists.
		$count = $DB->SingleQuery("SELECT count(*) as count FROM connections WHERE source_object_id = $sourceId AND destination_object_id = $destinationId");

		if	($count['count'] < 1) {
			$parameters = array(
				'source_object_id' => $sourceId,
				'destination_object_id' => $destinationId,
				'created' =>  $DB->SQLDate(),
				'num_segments' => $_REQUEST['num_segments'],
				'color' => $_REQUEST['color'],
				'source_axis' => $_REQUEST['source_axis'],
				'source_side' => $_REQUEST['source_side'],
				'source_position' => $_REQUEST['source_position'],
				'destination_side' => $_REQUEST['destination_side'],
				'destination_position' => $_REQUEST['destination_position']
			);
			$id = $DB->Insert('connections', $parameters);
			chlog("created new connection: id=$id:");
			echo $id;
		} else {
			chlog("new connection not created for source: $sourceId, destination: $destinationId. It already exists.");
			echo "0";
		}

	break;
	case 'disconnect': //Disconnect two boxes. IE, unlink them.
		$sourceId = intval($_REQUEST['source_id']);
		$destinationId = intval($_REQUEST['destination_id']);
		chlog("disconnected connection for source: $sourceId, destination: $destinationId.");
		$DB->Query("DELETE FROM connections WHERE source_object_id = $sourceId AND destination_object_id = $destinationId");
		break;
	break;
	case 'setProgram':
	    $objectId = intval($_REQUEST['object_id']);
	    $programId = intval($_REQUEST['program_id']);
	    $oldProgram = $DB->SingleQuery("SELECT object_type.id FROM object_type JOIN types ON (object_type.type_id = types.id) WHERE object_type.object_id='$objectId' AND types.family ='program'");
	    if($oldProgram == ""){
	        $DB->Insert('object_type', array('object_id' => $objectId, 'type_id' => $programId));
	    }
	    else{
	        $DB->Update('object_type', array('type_id' => $programId), $oldProgram['id']);
	    }

	break;

}


$update = array();
$update['last_modified'] = $DB->SQLDate();
$update['last_modified_by'] = $_SESSION['user_id'];
$DB->Update('drawings',$update,Request('version_id'));
$drawing=$DB->SingleQuery("SELECT * FROM drawings WHERE id=".Request('version_id'));
$DB->Update('drawing_main',$update,$drawing['parent_id']);

function chlog($msg) {
	$fn = '/tmp/pathways-log.txt';
	if(is_writable($fn)) {
		$fp = fopen($fn,'a');
		fwrite($fp,date("Y-m-d H:i:s").' u:'.$_SESSION['user_id'].':'.$_SESSION['full_name'].' d:'.Request('version_id').' '.$msg."\n");
		fclose($fp);
	}
}

