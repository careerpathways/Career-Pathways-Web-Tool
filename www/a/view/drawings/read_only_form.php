<?php
global $DB, $SITE;

$drawing = $DB->LoadRecord('drawing_main',$id);
$published = $DB->SingleQuery("SELECT * FROM drawings WHERE published=1 AND parent_id=".$drawing['id']);
$skillset = $DB->SingleQuery('SELECT * FROM oregon_skillsets WHERE id = '. intval($drawing['skillset_id']));

$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/$$/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/$$/%%.xml';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/text/$$/text.html';
$pdf_link = 'http://'.$_SERVER['SERVER_NAME'].'/pdf/$$/%%.pdf';

$embed_code = '<div id="pathwaysContainer" style="width:100%; height:600px"></div>
<script type="text/javascript" src="http://'.$_SERVER['SERVER_NAME'].'/c/published/$$/embed.js"></script>';

$schls = $DB->VerticalQuery("SELECT * FROM schools ORDER BY school_name",'school_abbr','id');

?>
<script type="text/javascript" src="/files/greybox.js"></script>
<script type="text/javascript" src="/files/drawing_list.js"></script>
<script type="text/javascript" src="/c/drawings.js"></script>
<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

<p>
<table>
<tr class="editable">
	<td colspan="2">
		<div id="drawing_header" class="title_img" style="height:19px;font-size:0px;overflow:hidden;background-color:#295a76"><?= ShowRoadmapHeader($drawing['id']) ?></div>
	</td>
</tr>
<tr class="editable">
	<th width="120"><?=l('program name label')?></th>
	<td><div id="program"><?php
		$program = $DB->SingleQuery('SELECT * FROM programs WHERE id = ' . $drawing['program_id']);
		echo ($drawing['program_id'] ? $program['title'] : 'Not Listed');
	?></div></td>
</tr>
<tr class="editable">
	<th><div id="drawing_title_label"><?= ($drawing['program_id'] == 0 ? 'Program Name' : 'Alternate Title') ?></div></th>
	<td><?= $drawing['name'] ?></td>
</tr>
<?php
	if($SITE->oregon_skillset_enabled){
?>
<tr class="editable">
	<th>Oregon Skill Set</th>
	<td><div id="skillset"><?= $skillset['title'] ?></div></td>
</tr>
<?php 
	}
?>
<tr class="editable">
	<th width="110">Organization</th>
	<td><b><?= $DB->GetValue('school_name','schools',$drawing['school_id']) ?></b></td>
</tr>
<?php
$school = $DB->SingleQuery('SELECT * FROM schools WHERE id = ' . $drawing['school_id']);
if( $SITE->olmis_enabled && $school['organization_type'] != 'Other' && is_array($published) ) {
?>
<tr class="editable">
	<th>OLMIS</th>
	<td>
		<div id="olmis_links"><?=ShowOlmisCheckboxes($drawing['id'], false, "This published roadmap is publicly accessible from the following OLMIS occupational reports:", true)?></div>
	</td>
</tr>
<?php
}
?>
<tr>
	<th>Embed Code</th>
	<td>
		<textarea style="width:560px;height:40px;" class="code" id="embed_code" onclick="this.select()"><?= htmlspecialchars(str_replace(array('$$','%%'),array($id,CleanDrawingCode($drawing['name'])),$embed_code)) ?></textarea>
	</td>
</tr>
<tr>
	<th valign="top">External Link</th>
	<td>
		<?php 
		if($external = getExternalDrawingLink($id, 'pathways'))
		{
			?>
			<div style="width:16px; float:left;"><a href="<?=$external?>" target="_blank"><?=SilkIcon('link.png')?></a></div>
			<input type="text" style="width:496px;" value="<?=$external?>" onclick="this.select()" id="external_link_url" />
				<input type="button" id="external_link_save" value="save" class="submit small" /><br />
				<div style="width:560px;">The primary URL is linked on external web pages such as OLMIS and MyPathCareers.org. To change, edit the URL above or select a URL from the list below.</div>
			<?php 
		}
		else
			echo 'We did not find any external links embedding this drawing.<br />';
		?>
		<br />
	</td>
</tr>
<tr>
	<th>Link</th>
	<td><?php
		$url = str_replace(array('$$','%%'),array($id,CleanDrawingCode($schls[$drawing['school_id']].'_'.$drawing['name'])),$published_link);
		?>
		<div style="width:16px; float:left; margin-right: 2px;"><a href="javascript:preview_drawing(<?=$published['parent_id'].','.$published['id']?>)"><?=SilkIcon('magnifier.png')?></a></div>
		<div id="drawing_link">
			<input type="text" style="width:542px" value="<?=$url?>" onclick="this.select()" />
		</div>
	</td>
</tr>
<tr>
	<th valign="top">PDF Link</th>
	<td><?php 
		$url = str_replace(array('$$','%%'),array($id,CleanDrawingCode($drawing['name'])),$pdf_link);
		?>
		<div style="width:16px; float:left; margin-right: 2px;"><a href="<?=$url?>"><?=SilkIcon('page_white_acrobat.png')?></a></div>
		<div id="drawing_link_pdf">
			<input type="text" style="width:542px" value="<?=$url?>" onclick="this.select()" />
		</div>
	</td>
</tr>
<tr>
	<th valign="top">XML Link</th>
	<td>
		<div id="drawing_link_xml"><?php
		$url = str_replace(array('$$','%%'),array($id,CleanDrawingCode($schls[$drawing['school_id']].'_'.$drawing['name'])),$xml_link);
		echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
		?></div>
	</td>
</tr>
<tr>
	<th valign="top">Accessible Link</th>
	<td>
		<div id="drawing_link_ada"><?php
		$url = str_replace('$$',$id,$accessible_link);
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