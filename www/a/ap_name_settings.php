<?php
/**
 * Primary page for AP Name Settings
 * Approved Program Names admin settings.
 *
 * Help guide admin user to proper form page.
 */
chdir("..");
include("inc.php");

ModuleInit('ap_name_settings');

PrintHeader();

//just in case someone makes it here directly (not via the sidebar link)
if(!$SITE->hasFeature('oregon_skillset')){
	die('This page is only available when the skillset feature is enabled. Please contact the system administrator if you are receiving this error unexpectedly. We apologize for any inconvenience.');
}
?>

<h3>Select which Approved Program Name list you wish to update/synchronize:</h3>
<ul>
	<li>
		<a href="ap_name_settings/roadmapdrawings/form.php">Roadmap Drawings &gt;&gt;</a>
	</li>
	<li>
		<a href="ap_name_settings/postdrawings/form.php">POST Drawings &gt;&gt;</a>
	</li>
</ul>
