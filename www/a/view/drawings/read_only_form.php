<?php
global $DB;

$drawing = $DB->LoadRecord('drawing_main',$id);
$published = $DB->SingleQuery("SELECT * FROM drawings WHERE published=1 AND parent_id=".$drawing['id']);
$skillset = $DB->SingleQuery('SELECT * FROM oregon_skillsets WHERE id = '. intval($drawing['skillset_id']));

$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/%%.xml';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/text/%%.html';
$embed_code = '<iframe width="800" height="600" src="'.$published_link.'" frameborder="0" scrolling="no"></iframe>';

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
	<th width="110">Organization</th>
	<td><?= $DB->GetValue('school_name','schools',$drawing['school_id']) ?></td>
</tr>
<tr>
	<th>Oregon Skill Set</th>
	<td><div id="skillset"><?= $skillset['title'] ?></div></td>
</tr>
<tr>
	<th>Preview</th>
	<td>
	<?php
		if( is_array($published) ) {
			echo '<a href="javascript:preview_drawing(\''.$drawing['code'].'\','.$published['version_num'].')">Preview Published Drawing</a>';
		} else {
			echo 'No versions have been published yet.';
		}
	?>
	</td>
</tr>
<tr>
	<th>Embed Code</th>
	<td>
		<textarea style="width:560px;height:40px;" class="code" id="embed_code" onclick="this.select()"><?= htmlspecialchars(str_replace('%%',$drawing['code'],$embed_code)) ?></textarea>
	</td>
</tr>
<tr>
	<th>Link</th>
	<td>
		<div id="drawing_link"><?php
		$url = str_replace('%%',$drawing['code'],$published_link);
		echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
		?></div>
	</td>
</tr>
<tr>
	<th valign="top">XML</th>
	<td>
		<div id="drawing_link_xml"><?php
		$url = str_replace('%%',$drawing['code'],$xml_link);
		echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
		?></div>
	</td>
</tr>
<tr>
	<th valign="top">Accessible</th>
	<td>
		<div id="drawing_link_ada"><?php
		$url = str_replace('%%',$drawing['code'],$accessible_link);
		echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
		?></div>
		These links, as well as the embed code above, will always link to the <b>published</b> version of this drawing.<br>
		<br>
	</td>
</tr>
<?php require('version_list.php'); ?>
</table>
</p>
<script type="text/javascript">

var MODE = 'pathways';

</script>