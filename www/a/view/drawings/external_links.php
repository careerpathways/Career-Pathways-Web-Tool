<tr>
	<th>Embed Views</th>
	<td>
		<?php 
		$external_links = getExternalDrawingLinks($id, ($MODE == 'pathways' ? 'pathways' : 'post'));
		if(count($external_links) > 0)
		{
			?>
			<table class="border external_links" cellpadding="3" width="100%">
				<tr>
					<th width="16"></th>
					<td>This drawing is embedded on the following web pages</td>
					<th width="110">Last Viewed</th>
				</tr>
			<?php 
			$trClass = new Cycler('even', 'odd');
			foreach($external_links as $link)
			{
				echo '<tr class="' . $trClass . '">';
					echo '<td><a href="' . $link['url'] . '" target="_blank">' . SilkIcon('link.png') . '</a></td>';
					echo '<td><div class="trim">' . $link['counter'] . ' views on <a href="' . $link['url'] . '" target="_blank">' . $link['url'] . '</a></div></td>';
					echo '<td>' . date('n/d/y g:ia', strtotime($link['last_seen'])) . '</td>';
				echo '</tr>';
			}
			?>
			</table>
			<?php
		}
		else
			echo 'We did not find any external links embedding this drawing.<br /><br />';
		?>
	</td>
</tr>
