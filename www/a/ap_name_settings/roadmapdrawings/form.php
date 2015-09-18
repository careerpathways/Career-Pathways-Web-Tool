<?php
/**
 * Roadmap Drawings admin form.
 */
chdir("../../..");
include("inc.php");

ModuleInit('ap_name_settings');

PrintHeader();

//just in case someone makes it here directly (not via the sidebar link)
if(!$SITE->hasFeature('oregon_skillset')){
	die('This page is only available when the skillset feature is enabled. Please contact the system administrator if you are receiving this error unexpectedly. We apologize for any inconvenience.');
}
?>

<?php if(isset($_POST['submitted'])):
	require('form_process.php'); ?>
<?php else: ?>

	<h3>Upload a CSV of Approved Program Names for Roadmap Drawings</h3>
	<p>New Approved Program Names will be added to the system for Roadmap Drawings. Duplicates will be ignored.</p>
	<p><em>Important: Please upload a .csv file. If your file is an excel spreadsheet (.xls or .xlsx) please save it as a .csv with your spreadsheet application before uploading here.<br />For help with Microsoft Excel, <a href="http://office.microsoft.com/en-us/excel-help/import-or-export-text-txt-or-csv-files-HP010099725.aspx#BMexport">see this article</a>. Instructions for Libre Office can be <a href="https://help.libreoffice.org/Calc/Importing_and_Exporting_CSV_Files">found here</a>.</em></p>
	<?php /* The data encoding type, enctype, MUST be specified as below */ ?>
	<form enctype="multipart/form-data" action="" method="POST">
	    File: <input name="userfile" type="file" />
	    <br>
	    <br>
		<input type="hidden" name="submitted" />
	    <input type="submit" value="Upload File" />
	</form>
	<br>
	<br>
	<br>
	<h3>Help</h3>
	Your XLS Document should be structured similarly to this example, before converting it to a .csv:
	<br>
	<br>
	<br>
	<img src="/images/ap_name_settings/roadmap_xls_example_2.png" width="1000" />
<?php endif; ?>
