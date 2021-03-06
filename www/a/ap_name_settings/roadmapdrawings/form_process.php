<?php
// Handles file upload logic, etc
require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'form_process_common.php');

$data = array(); //after parsing the csv, this array holds the structured data

$report = array(
    'new_programs' => array(),
    'skipped' => array(),
);

//expects a one-row csv with a very particular format.
//parse the csv, build clean data
$row = null;
do {
    if ($row) {
        //will be used as approved program name.
        $title = APN_Tools::build_roadmap_APN($row[1], $apn_exceptions, array()); //not supporting excluded terms right now
        $career_area = $row[8]; //will be used for skillset.
        $skillset_id = get_skillset_id($career_area);

        //only add rows to the database if we have a valid skillset ID
        if ($skillset_id) {
            push_data($title, $skillset_id);
        }
    }
} while ($row = fgetcsv($handle));

if (count($data) < 1) {
    die('Apologies, we were unable to process that file.');
}

//Find current approved program names so we can determine duplicates.
$existing_programs = $DB->MultiQuery('SELECT * FROM programs WHERE `use_for_roadmap_drawing` = 1');

//Insert into database if new approved program name.
foreach ($data as $key => $value) {
    $program_exists = apn_exsists($value['approved_program_name']);

    if ($program_exists) {
        array_push($report['skipped'], $value);
    } else {
        if ($value['approved_program_name']) {
            array_push($report['new_programs'], $value);
            if (!$isDryrun) {
                $result = $DB->Insert('programs',
                    array(
                        'skillset_id' => $value['skillset_id'],
                        'title' => $value['approved_program_name'],
                        'use_for_roadmap_drawing' => 1,
                        'imported_uid' => intval($_SESSION['user_id']),
                    )
                );
            }
        }
    }
}

function apn_exsists($apn)
{
    global $existing_programs;
    foreach ($existing_programs as $ep) {
        if (strtolower($ep['title']) == strtolower($apn)) {
            return true;
        }
    }

    return false;
}

/**
 * Get the skillset ID for a career area string.
 *
 * @param string $career_area
 *
 * @return int skillset id. False if not found
 */
function get_skillset_id($career_area)
{
    switch ($career_area) {
        case 'NR - Ag, Food and Natural Resources':
            return 1; //Database ID for Agriculture, Food and Natural Resources
        case 'AC - Arts, Information and Communications':
            return 2; //Database ID for Arts, Information and Communications
        case 'BU - Business and Management':
            return 3; //Database ID for Business and Management
        case 'HS - Health Services':
            return 4; //Database ID for Health Services
        case 'HR - Human Resources':
            return 5; //Database ID for Human Resources
        case 'IE - Industrial and Engineering Systems':
            return 6; //Database ID for Industrial and Engineering Systems
        default:
            return false; //Could not find a skillset
    }
}

function push_data($title, $skillset_id)
{
    global $data;

    $data[$title] = array(
        'approved_program_name' => $title,
        'skillset_id' => $skillset_id,
    );
}
?>

<?php require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'form_process_proceed.php'); ?>
