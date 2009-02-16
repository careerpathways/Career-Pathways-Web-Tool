<tr>
	<th valign="top">Versions</th>
	<td>
	<table>
	<?php
		$versions = $DB->MultiQuery("
			SELECT *
			FROM ".$drawings_table."
			WHERE ".$drawings_table.".parent_id=".$drawing['id']."
				AND deleted=0
			ORDER BY version_num");
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
					echo '<td><b>Created</b></td>';
					echo '<td>'.($v['date_created']==''?'':$DB->Date("n/d/Y g:ia",$v['date_created'])).' by <a href="/a/users.php?id='.$created['id'].'">'.$created['name'].'</a></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Modified</b></td>';
					echo '<td>'.($v['last_modified']==''?'':$DB->Date("n/d/Y g:ia",$v['last_modified'])).' by <a href="/a/users.php?id='.$modified['id'].'">'.$modified['name'].'</a></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Note</b></td>';
					echo '<td>'.$v['note'].'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Actions</b></td>';
					echo '<td>';
					$action = (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id']) ? 'draw' : 'view';
					$link = (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id']) ? SilkIcon('pencil.png') : SilkIcon('picture.png');
						echo '<a href="'.$_SERVER['PHP_SELF'].'?action=' . $action . '&version_id='.$v['id'].'">'.($v['published']?SilkIcon('picture.png'):$link).'</a>';
						echo ' &nbsp;&nbsp;&nbsp;';
						echo '<a href="javascript:preview_drawing(drawing_code,'.$v['version_num'].')">'.SilkIcon('magnifier.png').'</a>';
						echo ' &nbsp;&nbsp;&nbsp;';
						echo '<a href="javascript:copyPopup(\'' . $MODE . '\', ' . $v['id'] . ')" class="toolbarButton">copy this version</a>';
					echo '</td>';
				echo '</tr>';
			echo '</table></td>';
			echo '</tr>';
		}

	?>
	</table>
	</td>
</tr>