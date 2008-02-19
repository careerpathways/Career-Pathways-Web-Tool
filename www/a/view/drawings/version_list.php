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
			ORDER BY version_num");
		foreach( $versions as $v ) {
			$created = ($v['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$v['created_by']));
			$modified = ($v['last_modified_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$v['last_modified_by']));

			echo '<tr'.($v['published']?' class="version_list_published"':'').'>';


			echo '<td class="border" width="400" valign="top"><table height="80">';
				echo '<tr>';
					echo '<td width="60"><b>Version</b></td>';
					echo '<td><a href="/a/drawings.php?action=version_info&version_id='.$v['id'].'">'.$v['version_num'].'</a>'.($v['published']?' (Published)':'').'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Created</b></td>';
					echo '<td>'.($v['date_created']==''?'':$DB->Date("m/d/Y g:ia",$v['date_created'])).' by '.$created['name'].'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Modified</b></td>';
					echo '<td>'.($v['last_modified']==''?'':$DB->Date("m/d/Y g:ia",$v['last_modified'])).' by '.$modified['name'].'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Note</b></td>';
					echo '<td>'.$v['note'].'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td><b>Actions</b></td>';
					echo '<td>';
					$action = (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id']) ? 'draw' : 'view';
						echo '<a href="'.$_SERVER['PHP_SELF'].'?action=' . $action . '&amp;version_id='.$v['id'].'">'.($v['published']?'view':$action).'</a>';
						echo ' &nbsp;&nbsp;&nbsp;';
						echo '<a href="javascript:preview_drawing('.$v['id'].')">preview</a>';
						echo ' &nbsp;&nbsp;&nbsp;';
						echo '<a href="copy_popup.php?version_id=' . $v['id'] . '" class="publish" onclick="return showCopy(this);">copy this version</a>';
					echo '</td>';
				echo '</tr>';
			echo '</table></td>';
			echo '</tr>';
		}

	?>
	</table>
	</td>
</tr>