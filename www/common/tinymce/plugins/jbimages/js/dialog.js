/**
 * Justboil.me - a TinyMCE image upload plugin
 * jbimages/js/dialog.js
 *
 * Released under Creative Commons Attribution 3.0 Unported License
 *
 * License: http://creativecommons.org/licenses/by/3.0/
 * Plugin info: http://justboil.me/
 * Author: Viktor Kuzhelnyi
 *
 * Version: 2.3 released 23/06/2013
 */

 tinyMCEPopup.requireLangPack();

var jbImagesDialog = {
	
	resized : false,
	iframeOpened : false,
	timeoutStore : false,
	
	init : function() {
		document.getElementById("upload_target").src += '/' + tinyMCEPopup.getLang('jbimages_dlg.lang_id', 'english');
		if (navigator.userAgent.indexOf('Opera') > -1)
		{
			document.getElementById("close_link").style.display = 'block';
		}
	},
	
	inProgress : function() {
		document.getElementById("upload_infobar").style.display = 'none';
		document.getElementById("upload_additional_info").innerHTML = '';
		document.getElementById("upload_form_container").style.display = 'none';
		document.getElementById("upload_in_progress").style.display = 'block';
		this.timeoutStore = window.setTimeout(function(){
			document.getElementById("upload_additional_info").innerHTML = tinyMCEPopup.getLang('jbimages_dlg.longer_than_usual', 0) + '<br />' + tinyMCEPopup.getLang('jbimages_dlg.maybe_an_error', 0) + '<br /><a href="#" onClick="jbImagesDialog.showIframe()">' + tinyMCEPopup.getLang('jbimages_dlg.view_output', 0) + '</a>';
			//tinyMCEPopup.editor.windowManager.resizeBy(0, 30, tinyMCEPopup.id);
		}, 20000);
	},
	
	showIframe : function() {
		if (this.iframeOpened == false)
		{
			document.getElementById("upload_target").className = 'upload_target_visible';
			//tinyMCEPopup.editor.windowManager.resizeBy(0, 190, tinyMCEPopup.id);
			this.iframeOpened = true;
		}
	},
	
	uploadFinish : function(result) {
		if (result.resultCode == 'failed')
		{
			window.clearTimeout(this.timeoutStore);
			document.getElementById("upload_in_progress").style.display = 'none';
			document.getElementById("upload_infobar").style.display = 'block';
			document.getElementById("upload_infobar").innerHTML = result.result;
			document.getElementById("upload_form_container").style.display = 'block';
			
			if (this.resized == false)
			{
				//tinyMCEPopup.editor.windowManager.resizeBy(0, 30, tinyMCEPopup.id);
				this.resized = true;
			}
		}
		else
		{
			document.getElementById("upload_in_progress").style.display = 'none';
			document.getElementById("upload_infobar").style.display = 'block';
			document.getElementById("upload_infobar").innerHTML = tinyMCEPopup.getLang('jbimages_dlg.upload_complete', 0);			
			$('#uploaded-images').prepend(buildAssetHTML(result.asset));
			$("#upload_form_container").fadeIn();
		}
	}

};

tinyMCEPopup.onInit.add(jbImagesDialog.init, jbImagesDialog);

$('body').on('click', '[data-asset="delete"]', function(){
	deleteAsset($(this).parents('.asset').data('asset-id'));
});
$('body').on('click', '#uploaded-images img', function(){
	insertImage($(this).attr('src'));
});
$('body').on('click', '[data-asset="insert"]', function(){
	insertImage($(this).parents('.asset').find('.img-container img').attr('src'));
});

$.get('/asset/bucket_list.php', function(buckets){
	buildBucketSelector(buckets);
});

function buildBucketSelector(buckets) {
	var s = '<select name="bucket">';
	if(typeof buckets === 'object' || typeof buckets === 'array'){
		for(var i = 0; i < buckets.length; i++){
			s += '<option value="'+buckets[i].school_id+'">'+buckets[i].school_name+'</option>';
		}
	}
	s += '</select>';
	$('.bucket-select-container').html(s);
	$('.bucket-select-container select').on('change',function(){
		updatePermissions($(this).val(), buckets);
		updateLabels($(this).find(':selected').text());
		clearAssets();
		getAssets($(this).val());
	});
	//initial page load:
	$('.bucket-select-container select').trigger('change');
}

/**
 * Update permissions for the bucket.
 * This is only to show/hide the appropriate controls. Actual enforcement occurs on server-side.
 * @param  {int} bucketId the bucket id to enforce permissions for.
 * @param  {array} buckets  array of bucket objects
 */
function updatePermissions(bucketId, buckets){
	for(var i = 0; i < buckets.length; i++){
		if(buckets[i].school_id == bucketId){
			if(buckets[i].userCanDelete){
				$('#uploaded-images').addClass('userCanDelete');
			} else {
				$('#uploaded-images').removeClass('userCanDelete');
			}
			if(buckets[i].userCanReplace){
				$('#uploaded-images').addClass('userCanReplace');
			} else {
				$('#uploaded-images').removeClass('userCanReplace');
			}
		}
	}
}

function updateLabels(label){
	$("[data-replace='school_name']").html(label);
	$('.messages').html('');
}

function getAssets(school_id){
	$.get('/asset/list.php?school_id='+school_id, function(assets){
		if(typeof assets === 'object' || typeof assets === 'array'){
			if(assets.length < 1){
				$('.section.existing .messages').html('<p>There are no assets for this bucket yet.</p>');
			}
			for(var i = 0; i < assets.length; i++){
				setTimeout(appendAsset(assets[i]), 250*i);
			}
		}
	});
}
function buildAssetHTML(asset){
	var h = ''
    	+ '<div class="asset" data-asset-id="'+asset.id+'">'
	    	+ '<div class="img-container">'
	    		+ '<img src="'+asset.imgSrc+'" />'
	    	+ '</div>'
	    	+ '<div class="controls">'
		    	+ '<div class="insert btn" data-asset="insert">Insert</div>'
		    	+ '<div class="delete btn" data-asset="delete">Delete</div>'
	    	//+ '<div class="replace btn" data-asset="replace">Replace</div>' //Not implemented yet
	    	+ '</div>'
    	+ '</div>';
    return h;
}

function appendAsset(asset){
    return function(){
    	$('#uploaded-images').append(buildAssetHTML(asset));
    }
}

function clearAssets(){
	$('#uploaded-images').html('');
}

function deleteAsset(assetId){
	$.get('/asset/delete.php?asset_id='+assetId, function(response){
		console.log(response);
		if(response && response.status == 'success'){
			$('[data-asset-id="'+assetId+'"]').remove();
			$('.section.existing .messages').html(response.message);
		}
	});
}

function insertImage(imgSrc){
	tinyMCEPopup.editor.execCommand('mceInsertContent', false, '<img src="' + imgSrc +'" />');
	tinyMCEPopup.close();
}
