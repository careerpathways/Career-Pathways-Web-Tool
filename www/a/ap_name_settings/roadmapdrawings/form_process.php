<?php
// Handles file upload logic
require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'common.php');

$data = array(); //after parsing the csv, this array holds the structured data

$report = array(
    'new_programs' => array(),
    'skipped' => array(),
);

// After user submits, save/update exception list that they've entered.
// Do not run this on the non-dryrun pass, since these values aren't POSTed by
// the form.
if($isDryrun){
    echo 'Saving exceptions';
    //update the exceptions and exclusions in the DB
    $exceptions = $DB->safe($_POST['exceptions']);
    $exclusions = $DB->safe($_POST['exclusions']);
    // TODO insert if new...
    $DB->SingleQuery("UPDATE `apn_import` SET `value`=\"$exceptions\" WHERE type = \"roadmap\" AND field = \"exceptions\"");
    $DB->SingleQuery("UPDATE `apn_import` SET `value`=\"$exclusions\" WHERE type = \"roadmap\" AND field = \"exclusions\"");
}

// Query for these, even if we've just saved them during dry-run.
$exceptions = $DB->SingleQuery('SELECT * FROM `apn_import` WHERE `type`="roadmap" and `field`="exceptions"');
$exclusions = $DB->SingleQuery('SELECT `value` FROM `apn_import` WHERE `type`="roadmap" and `field`="exclusions"');
var_dump($exceptions);
$apn_exceptions = explode("\n", $exceptions);
$apn_exclusions = explode("\n", $exclusions);

//expects a one-row csv with a very particular format.
//parse the csv, build clean data
$row = null;
do {
    if ($row) {
        //will be used as approved program name.
        $title = APN_Tools::build_roadmap_APN($row[1], $apn_exceptions, $apn_excluded_terms);
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

<?php if ($isDryrun): ?>
    <h3>Proceed?</h3>
    <a href="?dryrun=false&submitted=true&file=<?= urlencode($file) ?>">Yes</a>
    <br />
    <br />
    <div>
        <h4>Exceptions</h4>
        <?php implode($apn_exceptions, ', '); ?>
    </div>
    <div>
        <h4>Exclusions</h4>
        <?php implode($apn_exclusions, ', '); ?>
    </div>

    <h3>New Programs</h3>
    <div>
        <em><?= count($report['new_programs']) ?> to be imported.</em>
    </div>
    <br />
    <?php if(count($report['new_programs']) > 0): ?>
        <?php foreach($report['new_programs'] as $np): ?>
            <div>
                <?= $np['approved_program_name'] ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div>
            No new (unique) program names found.
        </div>
    <?php endif;?>

    <br />
    <br />

    <h3>Skipping</h3>
    <div>
        <em><?= count($report['skipped']) ?> skipped because they'd be duplicates.</em>
    </div>
    <br />
    <?php if(count($report['skipped']) > 0): ?>
        <?php foreach($report['skipped'] as $np): ?>
            <div>
                <?= $np['approved_program_name'] ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div>
            Not skipping any programs.
        </div>
    <?php endif;?>

    <?php //print_r($data);?>

<?php else: ?>

    <h3>File Uploaded Successfully!</h3>
    <p>Number of new Approved Program Names: <?php echo count($report['new_programs']); ?></p>
    <p>Number skipped because they already exist: <?php echo count($report['skipped']); ?></p>
    <p><a href="/">Return to home page &gt;&gt;</a></p>

<?php endif; ?>
