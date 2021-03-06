<?php
/**
* POST Drawings admin form.
*/
chdir('../../..');
include 'inc.php';

ModuleInit('ap_name_settings');

PrintHeader();

//just in case someone makes it here directly (not via the sidebar link)
if (!$SITE->hasFeature('oregon_skillset')) {
    die('This page is only available when the skillset feature is enabled. Please contact the system administrator if you are receiving this error unexpectedly. We apologize for any inconvenience.');
}
?>

<?php if (isset($_REQUEST['submitted'])):
    require 'form_process.php'; ?>
<?php else: ?>

    <h3>Upload a CSV of Approved Program Names for POST Drawings</h3>
    <p>New Approved Program Names will be added to the system for POST Drawings. Duplicates will be ignored.</p>
    <p><em>Important: Please upload a .csv file. If your file is an excel spreadsheet (.xls or .xlsx) please save it as a .csv with your spreadsheet application before uploading here.<br />For help with Microsoft Excel, <a href="http://office.microsoft.com/en-us/excel-help/import-or-export-text-txt-or-csv-files-HP010099725.aspx#BMexport">see this article</a>. Instructions for Libre Office can be <a href="https://help.libreoffice.org/Calc/Importing_and_Exporting_CSV_Files">found here</a>.</em></p>
    <?php /* The data encoding type, enctype, MUST be specified as below */ ?>
    <?php
    $record = $DB->SingleQuery('SELECT * FROM `apn_import` WHERE `field`="exceptions"');
    $exceptions = $record['value'];
    ?>
    <?php require_once(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'form_common.php'); ?>
    <br>
    <br>
    <br>
    <h3>Help</h3>
    Your XLS Document should be structured similarly to this example, before converting it to a .csv:
    <br>
    <br>
    <br>
    <img src="/images/ap_name_settings/post_xls_example.png" width="1000" />
<?php endif; ?>
