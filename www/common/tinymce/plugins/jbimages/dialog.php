<?php
/* ====== Provide support if this .js file is used outside the context of tinyMCE ====== */
if(isset($_GET['using_tiny_mce']) && $_GET['using_tiny_mce'] == 'false'){
	$inTinyMce = false;
} else {
	$inTinyMce = true; //Default
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<title>{#jbimages_dlg.title}</title>
	<?php if($inTinyMce): ?>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<?php endif; ?>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
	<link href="/asset/asset_manager.css" rel="stylesheet" type="text/css">
	<link href="css/dialog.css" rel="stylesheet" type="text/css">
	<link href="/styles.css" rel="stylesheet" type="text/css">
	<style>
		<?php if($inTinyMce): ?>
		.replace.btn {
			display: none !important;
		}
		<?php else: ?>
		.insert.btn {
			display: none;
		}
		.img-container img {
			cursor: default;
		}
		<?php endif; ?>
	</style>

	
</head>
<body>
	<script type="text/javascript" src="/asset/Asset_Manager.js"></script>
	
	<?php if(!$inTinyMce): ?>
		<script>
			var tinyMCE = {
				addI18n: function(name, values){
					this[name] = values;
				}
			}
		</script>
		<script type="text/javascript" src="langs/en_dlg.js"></script>
	<?php endif; ?>
	<script type="text/javascript" src="js/dialog.js"></script>

	<form class="form-inline asset-manager" id="upl" name="upl" action="ci/index.php/upload/{#jbimages_dlg.lang_id}" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="jbImagesDialog.inProgress();">
		<div class="section bucket">
			<?php if($inTinyMce): ?>
				<h2>1. Choose Your Image Bucket</h2>
			<?php else: ?>
				<h2>Choose Your Image Bucket</h2>
			<?php endif; ?>
			<p><div class="bucket-select-container"></div></p>
		</div>
		
		<div class="section upload">
			<?php if($inTinyMce): ?>
				<h2>2. (optional) Upload a new image</h2>
			<?php else: ?>
				<h2>Upload a new image</h2>
			<?php endif;?>
			<p>The new image will be added to <span data-replace="school_name"></span>. Please limit your file size to 400x400 or 1MB</p>
			<div id="upload_in_progress" class="upload_infobar"><img src="img/spinner.gif" width="16" height="16" class="spinner"><!--{#jbimages_dlg.upload_in_progress}-->Upload in progress&hellip; <div id="upload_additional_info"></div></div>
			<div id="upload_infobar" class="upload_infobar"></div>	
			
			<p id="upload_form_container">
				<input id="uploader" name="userfile" type="file" class="jbFileBox" onChange="document.upl.submit(); jbImagesDialog.inProgress();" size="8">
				<!--<button type="submit" class="btn">{#jbimages_dlg.upload}</button>-->
				<!--<input type="submit" class="submit" value="Upload">-->
			</p>			
		</div>
		
		<div class="section existing">
			<?php if($inTinyMce): ?>
				<h2>3. Select an image from <span data-replace="school_name"></span></h2>
			<?php else: ?>
				<h2>Administer images for <span data-replace="school_name"></span></h2>
			<?php endif;?>
			
			<div class="messages"></div>
			<div id="uploaded-images"></div>
		</div>

		<div class="section replacement-pad">
			
		</div>

		<?php if($inTinyMce): ?>
		<input type="submit" class="submit" onclick="tinyMCEPopup.close(); return false;" value="Close">
		<?php endif; ?>
		<p id="the_plugin_name"><a href="http://justboil.me/" target="_blank" title="JustBoil.me Images - a TinyMCE Images Upload Plugin">JustBoil.me Images Plugin</a></p>
	</form>

	<iframe id="upload_target" name="upload_target" src="ci/index.php/blank"></iframe>
	

	<script>
		$('body').on('click', '[data-asset="delete"]', function(){
			var asset_id = $(this).parents('.asset').data('asset-id');
			checkAssetUse(asset_id, function(response){
				var response = confirm("There are " + response.number_of_drawings_using + " Roadmap or POST drawing versions using this image. Are you sure you want to delete it?");
				if(response == true){
					deleteAsset(asset_id);		
				}
			});
		});
		<?php if(!$inTinyMce): ?>
		$('body').on('click', '[data-asset="replace"]', function(){
			var asset_to_replace_id = $(this).parents('.asset').data('asset-id');
			replaceAssetStart(asset_to_replace_id);
		});
		<?php endif; ?>
		$('body').on('click', '[data-asset="insert"], #uploaded-images img', function(){
			var imgSrc = $(this).parents('.asset').find('.img-container img').attr('src'),
			assetId = $(this).parents('.asset').data('asset-id');
			insertImage(imgSrc, assetId); //tinymce dialog.js
		});

		//Asset_Manager.js main function
		getBuckets();
	</script>
</body>
</html>
