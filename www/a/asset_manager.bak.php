<?php
chdir("..");
include("inc.php");

ModuleInit('asset_manager');

//Web path to the jbimages plugin
$path_jbimage_plugin = '/common/tinymce/plugins/jbimages/';

if( IsAdmin() ) {
} else {
}
$TEMPLATE->addl_styles[] = '/asset/asset_manager.css';
$TEMPLATE->addl_scripts[] = 'https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js';
$TEMPLATE->addl_scripts[] = '/files/greybox.js';
$TEMPLATE->addl_scripts[] = '/asset/Asset_Manager.js';
$TEMPLATE->addl_scripts[] = $path_jbimage_plugin.'js/dialog.js';
PrintHeader(); ?>


<form class="form-inline asset-manager" id="upl" name="upl" action="<?= $path_jbimage_plugin?>ci/index.php/upload/english" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="jbImagesDialog.inProgress();">
		<div class="section">
			<h2>1. Choose Your Image Bucket</h2>
			<p><div class="bucket-select-container"></div></p>
		</div>
		
		<div class="section">
			<h2>Upload a new image to this bucket</h2>
			<p>Please limit your file size to 400x400 or 1MB</p>
			<div id="upload_in_progress" class="upload_infobar"><img src="img/spinner.gif" width="16" height="16" class="spinner">{#jbimages_dlg.upload_in_progress}&hellip; <div id="upload_additional_info"></div></div>
			<div id="upload_infobar" class="upload_infobar"></div>	
			
			<p id="upload_form_container">
				<input id="uploader" name="userfile" type="file" class="jbFileBox" onChange="document.upl.submit(); jbImagesDialog.inProgress();" size="8">
				<!--<button type="submit" class="btn">{#jbimages_dlg.upload}</button>-->
				<input type="submit" class="submit" value="Upload">
			</p>			
		</div>
		<h3>OR</h3>
		<div class="section existing">
			<h2>Select from existing images in "<span data-replace="school_name"></span>"</h2>
			<div class="messages"></div>
			<div id="uploaded-images"></div>
		</div>

		<input type="submit" class="submit" onclick="tinyMCEPopup.close(); return false;" value="Close">
		<p id="the_plugin_name"><a href="http://justboil.me/" target="_blank" title="JustBoil.me Images - a TinyMCE Images Upload Plugin">JustBoil.me Images Plugin</a></p>
	</form>

	<iframe id="upload_target" name="upload_target" src="ci/index.php/blank"></iframe>
	
	<script type="text/javascript" src="/asset/Asset_Manager.js"></script>
	<script type="text/javascript" src="js/dialog.js"></script>
	<script>
		$('body').on('click', '[data-asset="delete"]', function(){
			var asset_id = $(this).parents('.asset').data('asset-id');
			checkAssetUse(asset_id, 'roadmap_drawings', function(response){
				var response = confirm("There are " + response.number_of_drawings_using + " roadmap drawing versions using this image. Are you sure you want to delete it?");
				if(response == true){
					deleteAsset(asset_id);		
				}
			});
		});

		$('body').on('click', '[data-asset="insert"], #uploaded-images img', function(){
			var imgSrc = $(this).parents('.asset').find('.img-container img').attr('src'),
			assetId = $(this).parents('.asset').data('asset-id');
			insertImage(imgSrc, assetId); //tinymce dialog.js
		});

		//Asset_Manager.js main function
		getBuckets();
	</script>

<?php
PrintFooter();
