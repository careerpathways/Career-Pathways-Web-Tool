<tr>
	<th valign="top">Versions</th>
	<td>
	<table>
	<?php
		$versions = $DB->MultiQuery("
			SELECT *
			FROM drawings
			WHERE drawings.parent_id=".$drawing['id']."
				AND deleted=0
			ORDER BY version_num DESC");
		foreach( $versions as $v ) {
			$created = ($v['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name, id FROM users WHERE id=".$v['created_by']));
			$modified = ($v['last_modified_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name, id FROM users WHERE id=".$v['last_modified_by']));

			echo '<tr'.($v['published']?' class="version_list_published"':'').'>';

			echo '<td class="border" width="400" valign="top"><table height="80">';
				echo '<tr>';
					echo '<td width="60"><b>Version</b></td>';
					echo '<td><span class="version_title">'.$v['version_num'].' '.($v['published']?' (Published)':'').'</span> <a href="/a/drawings.php?action=version_info&version_id='.$v['id'].'" title="Version Settings">'.SilkIcon('wrench.png').'</a></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Note</b></td>';
					echo '<td>'.($v['published']?'<img src="/common/silk/report.png" title="Published Version" alt="" />':'').($v['frozen']?'<img src="/common/silk/lock.png" title="Version Locked" alt="" />':'').' '.$v['note'].'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Created</b></td>';
					echo '<td>'.($v['date_created']==''?'':$DB->Date("n/d/Y g:ia",$v['date_created'])).' by <a href="/a/users.php?id='.$created['id'].'">'.$created['name'].'</a></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Modified</b></td>';
					echo '<td>'.($v['last_modified']==''?'':$DB->Date("n/d/Y g:ia",$v['last_modified'])).' by <a href="/a/users.php?id='.$modified['id'].'">'.$modified['name'].'</a></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Actions</b></td>';
					echo '<td>';
						$drawViewText = 'Draw/Edit Version';
						if( CanEditVersion($v['id'], 'pathways') ) {
							if( $v['published'] == 1 || $v['frozen'] == 1 ) {
								$link = SilkIcon('picture.png');
								$action = 'view';
								$drawViewText = 'View Version';
							} else {
								$link = SilkIcon('pencil.png');
								$action = 'draw';
							}
						} else {
							$link = SilkIcon('picture.png');
							$action = 'view';
							$drawViewText = 'View Version';
						}

						echo '<a href="'.$_SERVER['PHP_SELF'].'?action=' . $action . '&version_id='.$v['id'].'" title="' . $drawViewText . '">'.($v['published']?SilkIcon('picture.png'):$link).'</a>';
						echo ' &nbsp;&nbsp;&nbsp;';
						echo '<a href="javascript:preview_drawing('.$v['parent_id'].','.$v['id'].',\'pathways\')" title="Preview Version">'.SilkIcon('magnifier.png').'</a>';
						if( IsStaff() ) {
							echo ' &nbsp;&nbsp;&nbsp;';
							echo '<a href="javascript:copyPopup(\'pathways\', ' . $v['id'] . ')" class="toolbarButton" title="Copy Version">'.SilkIcon('page_copy.png').'</a>';
						}
					echo '</td>';
				echo '</tr>';
			echo '</table></td>';
			echo '</tr>';
		}

	?>
	</table>
	</td>
</tr>