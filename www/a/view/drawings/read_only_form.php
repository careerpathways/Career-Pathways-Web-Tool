<?php
global $DB;

$drawing = $DB->LoadRecord('drawing_main',$id);
$published = $DB->SingleQuery("SELECT * FROM drawings WHERE published=1 AND parent_id=".$drawing['id']);

?>

<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

<p>
<table>
<tr>
	<th>Title</th>
	<td><h2><?= $drawing['name'] ?></h2></td>
</tr>
<tr>
	<th width="80">School</th>
	<td><?= $DB->GetValue('school_name','schools',$drawing['school_id']) ?></td>
</tr>
<tr>
	<th>Preview</th>
	<td>
	<?php
		if( is_array($published) ) {
			echo '<img src="/files/charts/gif/'.$published['id'].'.gif" height="100" width="140" class="border">';
		} else {
			echo 'No versions have been published yet.';
		}
	?>
	</td>
</tr>
<tr>
	<th valign="top">Versions</th>
	<td>
	<?php

		$T = new WrappingTable();
		$T->table_width = (145*4);
		$T->table_align = '';
		$T->cols = 4;

		$versions = $DB->MultiQuery("
			SELECT *
			FROM drawings
			WHERE drawings.parent_id=".$drawing['id']."
				AND deleted=0
			ORDER BY version_num");
		foreach( $versions as $v ) {
			$str = 'Version '.$v['version_num'].'<br>';
			$str .= '<a href="'.$_SERVER['PHP_SELF'].'?action=draw&amp;version_id='.$v['id'].'">';
			$str .= '<img src="/files/charts/gif/'.$v['id'].'.gif" height="100" width="140" class="border">';
			$str .= '</a>';
			$str .= '<br><a href="'.$_SERVER['PHP_SELF'].'?action=copy_version&amp;version_id='.$v['id'].'" class="tiny">create new version</a>';
			$T->AddItem($str);
		}

		$T->Output();
	?>
	</td>
</tr>

</table>
</p>