

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

