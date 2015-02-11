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


<?php
$most_recent = $DB->SingleQuery("SELECT programs.imported, users.first_name, users.last_name
	FROM programs
	LEFT JOIN users ON programs.imported_uid = users.id
	WHERE imported_uid > 0
	ORDER BY imported desc");
?>


<h3>Select which Approved Program Name list you wish to update/synchronize:</h3>

<?php if($most_recent): ?>
	<div class="previous-import">
		Previous upload (most recent) was by <?php echo $most_recent['first_name'] . ' ' . $most_recent['last_name']; ?>
		on <?php echo $most_recent['imported']; ?>
	</div>
<?php else: ?>
	<div class="previous-import">
		No previous uploads detected.
	</div>
<?php endif; ?>

<ul>
	<li>
		<a href="ap_name_settings/roadmapdrawings/form.php">Upload Roadmap APNs &gt;&gt;</a>
	</li>
	<li>
		<a href="ap_name_settings/postdrawings/form.php">Upload POST APNs &gt;&gt;</a>
	</li>
</ul>
