<?php
// Handles file upload logic, etc
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'form_process_common.php';

$data = array(); //after parsing the csv, this array holds the structured data

$report = array(
    'new_programs' => array(),
    'skipped' => array(),
);

//expects a two-row csv with a very particular format.
//parse the csv, build clean data
$row = null;
do {
    if ($row && is_top($row)) {
        $row2 = fgetcsv($handle); //our data exists across two rows
        $program_title = $row[5];
        $secondary_course_name = $row[7];
        $cluster_title = $row2[5]; //will be used for skillset. Need to map down to the 6 official skillsets.

        $approved_program_name = APN_Tools::build_post_APN(
            $program_title,
            $secondary_course_name,
            $apn_exceptions,
            array() //not supporting excluded terms right now
        );

        $skillset_id = get_skillset_id($cluster_title);
        if (strlen($approved_program_name) > 5 && $skillset_id > 0) {
            push_data($approved_program_name, $skillset_id);
        }
    }
} while ($row = fgetcsv($handle));

if (count($data) < 1) {
    die('Apologies, we were unable to process that file.');
}

$existing_programs = $DB->MultiQuery('SELECT * FROM programs WHERE `use_for_post_drawing` = 1');

//insert into database
foreach ($data as $key => $value) {
    $exists = compare($value);

    if ($exists) {
        array_push($report['skipped'], $value);
    } else {
        if ($value['approved_program_name']) {
            array_push($report['new_programs'], $value);
            if (!$isDryrun) {
                $result = $DB->Insert('programs',
                    array(
                        'skillset_id' => $value['skillset_id'],
                        'title' => $value['approved_program_name'],
                        'use_for_post_drawing' => 1,
                        'imported_uid' => intval($_SESSION['user_id']),
                    )
                );
            }
        }
    }
}

function get_skillset_id($cluster_title)
{
    $cluster_title = strtolower($cluster_title);
    switch ($cluster_title) {
        case 'agriculture':
            $skillset_id = 1; //Database ID for Agriculture, Food and Natural Resources
            break;
        case 'automotive and heavy equipment technology':
            $skillset_id = 6; //Database ID for Industrial and Engineering Systems
            break;
        case 'automotive & heavy equipment technology':
            $skillset_id = 6; //Database ID for Industrial and Engineering Systems
            break;
        case 'business management and administration':
            $skillset_id = 3; //Database ID for Business and Management
            break;
        case 'business management & administration':
            $skillset_id = 3; //Database ID for Business and Management
            break;
        case 'construction':
            $skillset_id = 6; //Database ID for Industrial and Engineering Systems
            break;
        case 'education and related fields':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'education & related fields':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'engineering':
            $skillset_id = 6; //Database ID for Industrial and Engineering Systems
            break;
        case 'environmental services':
            $skillset_id = 1; //Database ID for Agriculture, Food and Natural Resources
            break;
        case 'finance':
            $skillset_id = 3; //Database ID for Business and Management
            break;
        case 'health sciences':
            $skillset_id = 4; //Database ID for Health Services
            break;
        case 'hospitality and tourism':
            $skillset_id = 5; //Database ID for Human Resources
            break;
        case 'hospitality & tourism':
            $skillset_id = 5; //Database ID for Human Resources
            break;
        case 'human services':
            $skillset_id = 4; //Database ID for Health Services
            break;
        case 'information and communications technology (ict)':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'information & communications technology (ict)':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'information and communications technology - i&e':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'information & communications technology - i&e':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'information and communications technology - aic':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'information & communications technology - aic':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'manufacturing':
            $skillset_id = 6; //Database ID for Industrial and Engineering Systems
            break;
        case 'marketing':
            $skillset_id = 3; //Database ID for Business and Management
            break;
        case 'natural resources management':
            $skillset_id = 1; //Database ID for Agriculture, Food and Natural Resources
            break;
        case 'performing arts':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'public services':
            $skillset_id = 4; //Database ID for Health Services
            break;
        case 'publishing and broadcasting':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'publishing & broadcasting':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'visual and media arts':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        case 'visual & media arts':
            $skillset_id = 2; //Database ID for Arts, Information and Communications
            break;
        default:
            $skillset_id = 0;
            break;
    }

    return $skillset_id;
}

function compare($value)
{
    global $existing_programs;
    foreach ($existing_programs as $ep) {
        if (strtolower($ep['title']) == strtolower($value['approved_program_name'])) {
            return $ep['id'];
        }
    }

    return false;
}

function is_top($row)
{
    return intval($row[1]) > 0;
}

function push_data($approved_program_name, $skillset_id)
{
    global $data;
    $data[$approved_program_name] = array(
        'approved_program_name' => $approved_program_name,
        'skillset_id' => $skillset_id,
    );
}

?>

<?php require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'form_process_proceed.php'; ?>
