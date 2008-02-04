<?php
global $DB;

$drawing = $DB->LoadRecord('drawing_main',$id);
$published = $DB->SingleQuery("SELECT * FROM drawings WHERE published=1 AND parent_id=".$drawing['id']);

?>
<script type="text/javascript" src="/files/greybox.js"></script>
<script type="text/javascript" src="/files/drawing_list.js"></script>
<script type="text/javascript" src="/c/drawings.js"></script>
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
			echo '<a href="javascript:preview_drawing('.$published['id'].')">Preview Published Drawing</a>';
		} else {
			echo 'No versions have been published yet.';
		}
	?>
	</td>
</tr>
<?php require('version_list.php'); ?>
</table>
</p>