<?php
chdir("..");
include("inc.php");

$TEMPLATE->AddCrumb('/p/license_agreement', 'License Agreement');

PrintHeader();
?>

<style type="text/css">

.license h2 {
	color: black;
	text-align: center;
}

.license {
	width: 600px;
}
.license li {
	margin-top: 0.5em;
}
.license li li {
	list-style-type: lower-alpha;
}

</style>

<div class="license">
<?php 
	include('LICENSE.htm');
?>
</div>

<?php 
PrintFooter();
?>