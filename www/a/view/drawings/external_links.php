<tr>
	<th>Embed Views</th>
	<td>
		<?php 
		$external_links = getExternalDrawingLinks($id, ($MODE == 'pathways' ? 'pathways' : 'post'), 'last_seen DESC');
		if(count($external_links) > 0)
		{
			?>
			<table class="border external_links" cellpadding="3" width="100%">
				<tr>
					<th width="16"></th>
					<td>This drawing is embedded on the following web pages</td>
					<th width="110">Last Viewed</th>
					<th>Primary</th>
				</tr>
			<?php 
			foreach($external_links as $link)
			{
				echo '<tr class="external_link' . ($link['primary'] ? ' primary' : '') . '" id="externalLink_' . $link['id'] . '">';
					echo '<td><a href="' . $link['url'] . '" target="_blank">' . SilkIcon('link.png') . '</a></td>';
					echo '<td><div class="trim">' . $link['counter'] . ' views on <a href="' . $link['url'] . '" target="_blank" class="url">' . $link['url'] . '</a></div></td>';
					echo '<td>' . date('n/d/y g:ia', strtotime($link['last_seen'])) . '</td>';
					echo '<td><div class="make_primary' . ($link['primary'] ? ' primary' : '') . '"></div></td>';
				echo '</tr>';
			}
			?>
			</table>
			<script type="text/javascript">
				jQuery(function(){
					bindMakePrimary();
					jQuery("#external_link_save").click(function(){
						jQuery.post("/a/external_links_action.php", {
							action: "set",
							mode: "<?=$MODE?>",
							drawing_id: <?=$id?>,
							url: jQuery("#external_link_url").val()
						}, function(data){
						  	jQuery("#external_link_url").css({backgroundColor: '#99FF99'});
						  	setTimeout(function(){
							  	jQuery("#external_link_url").css({backgroundColor: '#FFFFFF'});
						  	}, 300);
						});
					});
				});
				function bindMakePrimary(){
					jQuery(".external_link").unbind("mouseover mouseout");
					jQuery(".make_primary").unbind("click");
					
					jQuery(".external_link:not(.primary)").bind("mouseover", function(){
						jQuery(this).find(".make_primary").css({"background-position": "0 0"});
					}).bind("mouseout", function(){
						jQuery(this).find(".make_primary").css({"background-position": "-16px 0"});
					});
					
					jQuery(".make_primary:not(.primary)").bind("click", function(){
						jQuery.post("/a/external_links_action.php", {
							action: "primary",
							mode: "<?=$MODE?>",
							drawing_id: <?=$id?>,
							url_id: jQuery(this).parents(".external_link").attr("id").split("_")[1]
						}, function(data){
							var id = data.split("|")[0];
							var url = data.split("|")[1];
							jQuery("#external_link_url").val(url);
							jQuery(".make_primary, .external_link").removeClass("primary");
							jQuery("#externalLink_"+id).addClass("primary");
							jQuery("#externalLink_"+id).find(".make_primary").addClass("primary").css({"background-position": "0 0"});
							bindMakePrimary();
						});
					});
				}
			</script>
			<?php
		}
		else
			echo 'We did not find any external links embedding this drawing.<br /><br />';
		?>
	</td>
</tr>
