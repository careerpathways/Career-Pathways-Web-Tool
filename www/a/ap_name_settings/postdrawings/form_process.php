<?php
ini_set("auto_detect_line_endings", true);
$file = $_FILES["userfile"]["tmp_name"];
if(!file_exists($file)){ die('Our apologies, there appears to be a problem uploading the file. Please try again or contact your system administrator.'); }
$handle = fopen($file,"r");

$data = array(); //after parsing the csv, this array holds the structured data

$report = array(
	'new_programs' => array(),
	'skipped' => array()
);

//expects a two-row csv with a very particular format.
//parse the csv, build clean data
$row = null;
do {
    if ($row) {
    	if(is_top($row)){
    		$row2 = fgetcsv($handle); //our data exists across two rows
    		$secondary_course_name = $row[7]; //will be used as approved program name.
    		$cluster_title = $row2[5]; //will be used for skillset. Need to map down to the 6 official skillsets.
			push_data($secondary_course_name, $cluster_title);
    	}
    }
} while ($row = fgetcsv($handle));

if(count($data) < 1){
	die('Apologies, we were unable to process that file.');
}

$existing_programs = $DB->MultiQuery('SELECT * FROM programs WHERE `use_for_post_drawing` = 1');

//insert into database
foreach ($data as $key => $value) {
	$exists = compare($value);

	if($exists){
		array_push($report['skipped'], $value);
	} else {
		if($value['approved_program_name']){
			array_push($report['new_programs'], $value);
			$result = $DB->Insert('programs',
			    array(
				    'skillset_id'          => $value['skillset_id'],
				    'title'                => $value['approved_program_name'],
				    'use_for_post_drawing' => 1,
				    'imported_uid'         => intval($_SESSION['user_id'])
			    )
			);
		}
	}
}

function compare($value){
	global $existing_programs;
	foreach($existing_programs as $ep){
		if(strtolower($ep["title"]) == strtolower($value["approved_program_name"])){
			return $ep['id'];
		}
	}
	return false;
}

function is_top($row) {
	return (intval($row[1]) > 0);
}

function push_data($secondary_course_name, $cluster_title) {
	global $data;
	switch ($cluster_title) {
		case "agriculture":
			$skillset_id = 1; //Database ID for Agriculture, Food and Natural Resources
			break;
		case "business management and administration":
			$skillset_id = 3; //Database ID for Business and Management
			break;
		case "construction":
			$skillset_id = 6; //Database ID for Industrial and Engineering Systems
			break;
		case "health sciences":
			$skillset_id = 4; //Database ID for Health Services
			break;
		case "hospitality and tourism":
			$skillset_id = 5; //Database ID for Human Resources
			break;
		case "natural resources management":
			$skillset_id = 1; //Database ID for Agriculture, Food and Natural Resources
			break;
		case "manufacturing":
			$skillset_id = 6; //Database ID for Industrial and Engineering Systems
			break;
		case "automotive & heavy equipment technology":
			$skillset_id = 6; //Database ID for Industrial and Engineering Systems
			break;
		case "visual and media arts":
			$skillset_id = 2; //Database ID for Arts, Information and Communications
			break;
		case "engineering":
			$skillset_id = 6; //Database ID for Industrial and Engineering Systems
			break;
		case "information and communications technology (ict)":
			$skillset_id = 2; //Database ID for Arts, Information and Communications
			break;
		case "publishing and broadcasting":
			$skillset_id = 2; //Database ID for Arts, Information and Communications
			break;
		case "marketing":
			$skillset_id = 3; //Database ID for Business and Management
			break;
		case "education and related fields":
			$skillset_id = 2; //Database ID for Arts, Information and Communications
			break;
		case "finance":
			$skillset_id = 3; //Database ID for Business and Management
			break;
		case "information and communications technology - i&e":
			$skillset_id = 2; //Database ID for Arts, Information and Communications
			break;
		case "information and communications technology - aic":
			$skillset_id = 2; //Database ID for Arts, Information and Communications
			break;
		case "public services":
			$skillset_id = 4; //Database ID for Health Services
			break;
		case "performing arts":
			$skillset_id = 2; //Database ID for Arts, Information and Communications
			break;
		case "human services":
			$skillset_id = 4; //Database ID for Health Services
			break;
		case "environmental services":
			$skillset_id = 1; //Database ID for Agriculture, Food and Natural Resources
			break;
		default:
			$skillset_id = 1;
			break;
	}

	$data[$secondary_course_name] = array(
		'approved_program_name' => $secondary_course_name,
		'skillset_id' => $skillset_id,
		'cluster_title' => $cluster_title
	);
}

?>

<h3>File Uploaded Successfully!</h3>
<p>Number of new Approved Program Names: <?php echo count($report['new_programs']); ?></p>
<p>Number skipped because they already exist: <?php echo count($report['skipped']); ?></p>
<p><a href="/">Return to home page &gt;&gt;</a></p>
<?php //print_r($report); ?>
