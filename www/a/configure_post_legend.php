<?php
	chdir('..');
	require_once('inc.php');

	ModuleInit('configure_post_legend');

	$TEMPLATE->AddCrumb('','POST Legend Configuration');
	$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';
	$TEMPLATE->addl_scripts[] = '/common/URLfunctions1.js';

	PrintHeader();


	$items = $DB->MultiQuery("SELECT `id`, `text` FROM `post_legend` ORDER BY `id` ASC");
?>

	<div style="color: #555555; font-size: 18px;">Configure the POST Legend Symbols using the form below:</div>

	<table border="0" cellpadding="0" cellspacing="0" style="margin: 20px auto 20px auto;">
		<tr>
			<td style="width: 16px;"></td>
			<td style="width: 20px;"></td>
			<td><div style="margin-left: 10px; font-weight: bold;">Current Description</div></td>
			<td style="width: 16px;"></td>
			<td><div style="margin-left: 10px; font-weight: bold;">New Description</div></td>
			<td></td>
			<td></td>
		</tr>
<?php
	foreach($items as $item)
	{
?>
		<tr>
			<td><img src="/common/silk/tick.png" id="legend_confirm_<?=$item['id']?>" alt="Confirmed" style="visibility: hidden; margin-right: 15px;" /></td>
			<td><img src="/c/images/legend/b<?=$item['id']?>.png" alt="<?=$item['text']?>" /></td>
			<td><div id="legend_source_<?=$item['id']?>" style="padding: 0 10px;"><?=$item['text']?></div></td>
			<td><img class="legend_transfer" id="legend_transfer_<?=$item['id']?>" src="/common/silk/arrow_right.png" alt="Transfer Description" style="cursor: pointer;" /></td>
			<td><input type="text" id="legend_text_<?=$item['id']?>" maxlength="255" style="margin-left: 10px; width: 220px;" /></td>
			<td><input type="button" class="legend_submit" id="legend_submit_<?=$item['id']?>" value="Save" style="margin: 2px 0 2px 10px; padding: 0 4px; font-size: 12pt; background: #E0E0E0; border: 1px #777777 solid;" /></td>
			<td><input type="button" class="legend_clear" id="legend_clear_<?=$item['id']?>" value="Clear" style="margin: 2px 0 2px 10px; padding: 0 4px; font-size: 12pt; background: #E0E0E0; border: 1px #777777 solid;" /></td>
		</tr>
<?php
	}
?>
	</table>

	<script type="text/javascript">
		$(".legend_transfer").click(function(){
			var id = parseID($(this).attr("id"));
			$("#legend_text_" + id).val($("#legend_source_" + id).text());
		});

		$(".legend_clear").click(function(){
			var id = parseID($(this).attr("id"));
			$.ajax({
				type : 'POST',
				url : '/a/postserv.php?mode=commit&type=legend&id=' + id,
				data : "text=",
				success : function(data){
					$("#legend_source_" + id).html(data);
					$("#legend_text_" + id).val("");
					$("#legend_confirm_" + id).attr("src", "/common/silk/tick.png").css({
						opacity : 1,
						visibility: "visible"
					}).animate({
						opacity: 0
					}, 1500, function(){
						$("#legend_confirm_" + id).css({
							visibility: "hidden"
						});
					});
				}
			});
		});

		$(".legend_submit").click(function(){
			var id = parseID($(this).attr("id"));

			if($("#legend_text_" + id).val() == '')
			{
				$("#legend_confirm_" + id).attr("src", "/common/silk/cancel.png").css({
					opacity : 1,
					visibility: "visible"
				}).animate({
					opacity: 0
				}, 1500, function(){
					$("#legend_confirm_" + id).css({
						visibility: "hidden"
					});
				});

				$("#legend_text_" + id).animate({
					border : "1px #FF0000 solid"
				}, 300, function(){
					$("#legend_text_" + id).animate({
						border : "1px #999999 solid"
					}, 300, function(){
						$("#legend_text_" + id).animate({
							border: "1px #FF0000 solid"
						}, 300, function(){
							$("#legend_text_" + id).animate({
								border: "1px #999999 solid"
							}, 300);
						});
					});
				});

				return false;
			}

			$.ajax({
				type : 'POST',
				url : '/a/postserv.php?mode=commit&type=legend&id=' + id,
				data : "text=" + URLEncode($("#legend_text_" + id).val()),
				success : function(data){
					$("#legend_source_" + id).html(data);
					$("#legend_text_" + id).val("");
					$("#legend_confirm_" + id).attr("src", "/common/silk/tick.png").css({
						opacity : 1,
						visibility: "visible"
					}).animate({
						opacity: 0
					}, 1500, function(){
						$("#legend_confirm_" + id).css({
							visibility: "hidden"
						});
					});
				}
			});
		});

		function parseID(id)
		{
			var chunks = id.split("_");
			return chunks[2];
		}
	</script>
<?php
	PrintFooter();
?>