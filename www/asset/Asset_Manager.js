var assetReplaceMode = false;

function getBuckets(){
	$.get('/asset/bucket_list.php', function(buckets){
		buildBucketSelector(buckets);
	});
}

function buildBucketSelector(buckets) {
	$('.bucket-select-container').html(buildBucketSelectorHTML(buckets));
	$('.bucket-select-container select').on('change',function(){
		updatePermissions($(this).val(), buckets);
		updateLabels($(this).find(':selected').text());
		clearAssets();
		clearWorkPad();
		getAssets($(this).val());
	});
	//initial page load:
	$('.bucket-select-container select').trigger('change');
}

function clearWorkPad() {
	$(".work-pad").html('');
}

function buildBucketSelectorHTML(buckets) {
	var s = '<select name="bucket">';
	if(typeof buckets === 'object' || typeof buckets === 'array'){
		for(var i = 0; i < buckets.length; i++){
			s += '<option value="'+buckets[i].school_id+'">'+buckets[i].school_name+'</option>';
		}
	}
	s += '</select>';
	return s;
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
				$('#uploaded-images').addClass('userCanDelete'); //used to delete images, as well as move them to another bucket.
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

function msg(msgHTML) {
	$('.messages').html(msgHTML);
}

function updateLabels(label){
	$("[data-replace='school_name']").html(label);
	msg('');
}

function getAssets(school_id){
	$.get('/asset/list.php?school_id='+school_id, function(assets){
		if(typeof assets === 'object' || typeof assets === 'array'){
			if(assets.length < 1){
				msg('<p>There are no images for this bucket yet.</p>');
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
	    	+ '<div class="controls">';
		    	h += getButtons();
	    	h += '</div>'
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

/**
 * Check an assets use and get details about where it's used.
 */
function checkAssetUse(assetId, callback){
	$.get("/asset/check_use.php?asset_id="+assetId, function(response){
		if(response && 'function' === typeof callback){
			callback(response);
		}
	});
}

/**
 * Delete an asset.
 */
function deleteAsset(assetId){
	$.get('/asset/delete.php?asset_id='+assetId, function(response){
		if(response && response.status == 'success'){
			$('[data-asset-id="'+assetId+'"]').remove();
			msg(response.message);
		}
	});	
}

/**
 * Begin replacing an asset.
 * Replacing an asset is a 3 step process (choose replacee, choose replacement, confirm)
 * @param  {int} assetId
 */
function replaceAssetStart(assetId){
	msg('');
	clearWorkPad()
	var $asset = $('[data-asset-id="'+assetId+'"]');
	assetReplaceMode = true;
	$('.section.upload').hide();
	checkAssetUse(assetId, function(response){
		$(".work-pad").append('<div class="heading">Image Replacement</div>'); //clear each time
		$(".work-pad").append('<div class="btn cancel" data-asset="replacecancel" data-asset-id="'+assetId+'">cancel</div>');
		$(".work-pad").append('<div class="subheading">This image will be replaced:</div>');
		$asset.clone().appendTo(".work-pad");
		$asset.hide(); //don't offer it as a choice to replace itself!
		$(".section.existing").addClass('asset-replace-mode');
		$(".work-pad").append('<div class="drawings-using">There are '+response.number_of_drawings_using+' drawings using this image &uarr;.</div>');
		$(".work-pad").append('<div class="subheading step-two-instructions">Please choose a replacement above.</div>');
		$(".work-pad").append('<div><em>*Replace image does not replace image in library, it only replaces use of image throughout Roadmaps and POST drawings.</em></div>');
		$(".work-pad").append('<div class="btn proceed" data-asset="replaceproceed" data-asset-original-id="'+assetId+'">proceed</div>');
		$('[data-asset="replaceproceed"]').hide();
	});
}

/**
 * Perform necessary steps after a replacement image has been selected.
 * @param  {int} replacementAssetId
 */
function replaceAssetReplacementChosen(replacementAssetId){
	var $replacementAsset = $('[data-asset-id="'+replacementAssetId+'"]');
	$('.section:not(.work-pad)').hide();
	$('.step-two-instructions').hide();
	$('[data-asset="replaceproceed"]').attr('data-asset-replacement-id', replacementAssetId).show();
	$replacementAsset.clone().insertBefore('[data-asset="replaceproceed"]');
	$(".work-pad").append('<div class="step-two-instructions-two">This image will replace it everywhere it\'s used.</div>');
	
}

function replaceAssetCancel(assetId){
	$('.section').show();
	clearWorkPad();
	$('[data-asset-id="'+assetId+'"]').show();
	$(".section.existing").removeClass('asset-replace-mode');
	assetReplaceMode = false;
}

function moveAssetStart(assetId){
	msg('');
	clearWorkPad();
	var $asset = $('[data-asset-id="'+assetId+'"]');
	var h = '<div class="move-asset">'
	+'<div class="heading">Move Image</div>'
	+'<div class="bucket-select-container"></div>'
	+'<div class="btn cancel" data-asset="movecancel" data-asset-id="'+assetId+'">cancel</div>'
	+'<div class="btn proceed" data-asset="moveproceed" data-asset-id="'+assetId+'">proceed</div>'
	+'</div>';
	
	$.get('/asset/bucket_list.php', function(buckets){
		var permittedBuckets = [];
		for(var i = 0; i < buckets.length; i++){
			//var isCurrent = buckets[i];
			var isCurrent = $('.bucket-select-container select').find(':selected').val() == buckets[i].school_id
			// Don't show current bucket (moving FROM), and don't show
			// buckets for which the user is not allowed to write to.
			if(buckets[i].userCanWrite && !isCurrent){
				permittedBuckets.push(buckets[i]);
			}
		}
		if(permittedBuckets.length > 0){
			$('.work-pad').html(h);
			$asset.clone().insertBefore('.heading');
			$('.move-asset .bucket-select-container').html(buildBucketSelectorHTML(permittedBuckets));
		} else {
			msg('It appears there are no other buckets that you have permission to move images to.');
		}
	});
}

function moveAssetProceed(assetId, bucketId){
	var _assetId = assetId;
	$.get('/asset/move.php?asset_id='+assetId+'&bucket_id='+bucketId, function(response){
		if(response && response.status == 'success'){
			clearWorkPad();
			msg(response.message);
			$('[data-asset-id="'+_assetId+'"]').remove();
		} else {
			msg(response.message);
		}
	});
}

function moveAssetCancel(assetId){
	clearWorkPad();
	msg('Cancelled image move.');
}

function getButtons(){
	var btns = '';
	btns += '<div class="delete btn" data-asset="delete">Delete</div>'
    	+ '<div class="replace btn" data-asset="replace">Replace</div>'
    	+ '<div class="move btn" data-asset="move">Move</div>'
    	+ '<div class="replace-with-this btn" data-asset="replace-with-this">Replace with this</div>'
    	+ '<div class="insert btn" data-asset="insert">Insert</div>';
	return btns;
}

