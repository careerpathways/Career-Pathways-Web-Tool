var assetReplaceMode = false;

function getBuckets(){
	$.get('/asset/bucket_list.php', function(buckets){
		buildBucketSelector(buckets);
	});
}

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
				$('.section.existing .messages').html('<p>There are no images for this bucket yet.</p>');
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
			$('.section.existing .messages').html(response.message);
		}
	});	
}

/**
 * Begin replacing an asset.
 * Replacing an asset is a 3 step process (choose replacee, choose replacement, confirm)
 * @param  {int} assetId
 */
function replaceAssetStart(assetId){
	var $asset = $('[data-asset-id="'+assetId+'"]');
	assetReplaceMode = true;
	$('.section.upload').hide();
	checkAssetUse(assetId, function(response){
		$(".replacement-pad").append('<div class="heading">Image Replacement</div>'); //clear each time
		$(".replacement-pad").append('<div class="btn cancel" data-asset="replacecancel" data-asset-id="'+assetId+'">cancel</div>');
		$(".replacement-pad").append('<div class="subheading">This image will be replaced:</div>');
		$asset.clone().appendTo(".replacement-pad");
		$asset.hide(); //don't offer it as a choice to replace itself!
		$(".section.existing").addClass('asset-replace-mode');
		$(".replacement-pad").append('<div class="drawings-using">There are '+response.number_of_drawings_using+' drawings using this image &uarr;.</div>');
		$(".replacement-pad").append('<div class="subheading step-two-instructions">Please choose a replacement above.</div>');
		$(".replacement-pad").append('<div><em>*Replace image does not replace image in library, it only replaces use of image throughout Roadmaps and POST drawings.</em></div>');
		$(".replacement-pad").append('<div class="btn proceed" data-asset="replaceproceed" data-asset-original-id="'+assetId+'">proceed</div>');
		$('[data-asset="replaceproceed"]').hide();
	});
}

/**
 * Perform necessary steps after a replacement image has been selected.
 * @param  {int} assetId
 */
function replaceAssetReplacementChosen(replacementAssetId){
	var $replacementAsset = $('[data-asset-id="'+replacementAssetId+'"]');
	$('.section:not(.replacement-pad)').hide();
	$('.step-two-instructions').hide();
	$('[data-asset="replaceproceed"]').attr('data-asset-replacement-id', replacementAssetId).show();
	$replacementAsset.clone().insertBefore('[data-asset="replaceproceed"]');
	$(".replacement-pad").append('<div class="step-two-instructions-two">This image will replace it everywhere it\'s used.</div>');
	
}

function replaceAssetCancel(assetId){
	$('.section').show();
	$(".replacement-pad").html('');
	$('[data-asset-id="'+assetId+'"]').show();
	$(".section.existing").removeClass('asset-replace-mode');
	assetReplaceMode = false;
}

function getButtons(){
	var btns = '';
	btns += '<div class="delete btn" data-asset="delete">Delete</div>'
    	+ '<div class="replace btn" data-asset="replace">Replace</div>'
    	+ '<div class="replace-with-this btn" data-asset="replace-with-this">Replace with this</div>'
    	+ '<div class="insert btn" data-asset="insert">Insert</div>';
	return btns;
}

$('body').on('click', '[data-asset="replacecancel"]', function(){
	replaceAssetCancel($(this).attr('data-asset-id'));
});

$('body').on('click', '[data-asset="replaceproceed"]', function(){
	if($(this).attr('data-asset-original-id') && $(this).attr('data-asset-replacement-id')){
		var originalId = $(this).attr('data-asset-original-id'),
			replacementId = $(this).attr('data-asset-replacement-id');
		$.get('/asset/replace.php?asset_id_original='+originalId+'&asset_id_new='+replacementId, function(response){
			$('.replacement-pad').html('<div class="message">'+response.message+'</div>');
			$('.replacement-pad').append('<div class="btn cancel" data-asset="replacecancel" data-asset-id="'+originalId+'">Okay</div>');
		});	
	}	
});

$('body').on('click', '[data-asset="replace-with-this"]', function(){
	var assetId = $(this).parents('.asset').data('asset-id');
	replaceAssetReplacementChosen(assetId);
});