<?php
chdir("..");
include("inc.php");
ModuleInit('asset_manager');
PrintHeader(); ?>


<?php /* ====== Use the jbimages plugin outside the context of tinyMCE ====== */ ?>
<iframe src="/common/tinymce/plugins/jbimages/dialog.php?using_tiny_mce=false" width="828" height="1000"></iframe>

<?php PrintFooter();
