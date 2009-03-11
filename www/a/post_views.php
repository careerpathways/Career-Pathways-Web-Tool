<?php
chdir("..");
include("inc.php");

$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/view_post/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/view_post/%%.xml';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/view_post/text/%%.html';
$embed_code = '<iframe width="800" height="600" src="'.$published_link.'" frameborder="0" scrolling="no"></iframe>';



$MODE = 'pathways';
ModuleInit('post_views');

PrintHeader();


$id = Request('id')?intval(Request('id')):'';

$schools = $DB->VerticalQuery("SELECT * FROM schools ORDER BY school_name",'school_name','id');
if( IsAdmin() ) {
	if( $id != "" ) {
		$view = $DB->SingleQuery('SELECT * FROM vpost_views WHERE id='.$id);
		$school_id = $view['school_id'];
	} else {
		$school_id = $_SESSION['school_id'];
	}
} else {
	$school_id = $_SESSION['school_id'];
}




if( $id )
{
	$view = $DB->SingleQuery('SELECT * FROM vpost_views WHERE id='.$id);

	?>	
	<script type="text/javascript" src="/files/greybox.js"></script>
	<table width="100%">
	<tr>
		<th>Title</th>
		<td>
			<div id="title_fixed"><span id="title_value"><?= $view['name'] ?></span> <a href="javascript:showTitleChange()" class="tiny">edit</a></div>
			<div id="title_edit" style="display:none">
				<input type="text" id="drawing_title" name="name" size="80" value="<?= $view['name'] ?>" onblur="checkName(this)">
				<input type="button" class="submit tiny" value="Save" id="submitButton" onclick="savetitle()">
				<span id="checkNameResponse" class="error"></span>
			</div>
		</td>
	</tr>
	<tr>
		<th width="80">Organization</th>
		<td><b><?= $schools[$school_id] ?></b></td>
	</tr>	
	<tr>
		<th>Link</th>
		<td>
			<div id="drawing_link"><?php
			$url = str_replace('%%',$view['code'],$published_link);
			echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
			?></div>
		</td>
	</tr>
	<tr>
		<th>Embed Code</th>
		<td>
			<textarea style="width:560px;height:40px;" class="code" id="embed_code" onclick="this.select()"><?= htmlspecialchars(str_replace('%%',$view['code'],$embed_code)) ?></textarea>
		</td>
	</tr>
	<tr>
		<th>Contents</th>
		<td>
			<div style="display:inline"><a href="javascript:addDrawingToView('hs', <?= $id ?>)"><?= SilkIcon('add.png') ?></a></div>
			<h3 style="display:inline">High School Templates</h3>
			<div id="connected_drawing_list_hs">
			<?php
				ShowSmallDrawingConnectionList($id, 'views', 'HS', array(
					'delete'=>'javascript:deleteDrawingFromView(%%)',
				));
			?>
			</div>
			<br />
			
			<div style="display:inline"><a href="javascript:addDrawingToView('cc', <?= $id ?>)"><?= SilkIcon('add.png') ?></a></div>
			<h3 style="display:inline">Community College Pathways</h3>
			<div id="connected_drawing_list_cc">
			<?php
				ShowSmallDrawingConnectionList($id, 'views', 'CC', array(
					'delete'=>'javascript:deleteDrawingFromView(%%)',
				));
			?>
			</div>
			
		</td>
	</tr>

	</table>
	<script type="text/javascript">

	function preview_drawing(code) {
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/post/'+code+'.html"></iframe></div>',800,600, null, 'Preview');
	}
	
	</script>
	<?php
}
else
{
	$views = $DB->MultiQuery('SELECT * FROM vpost_views WHERE school_id='.$school_id.' ORDER BY name');
	echo '<table width="100%">';
		echo '<tr>';
			echo '<th width="20">&nbsp;</th>';
			echo '<th>Title</th>';
			echo '<th width="240">Last Modified</th>';
			echo '<th width="240">Created</th>';
		echo '</tr>';
	foreach( $views as $v )
	{
		echo '<tr>';
			echo '<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$v['id'].'" class="edit"><img src="/common/silk/cog.png" width="16" height="16"/></a></td>';
	
			echo '<td>' . $v['name'] . '</td>';
	
			$created = ($v['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$v['created_by']));
			$modified = ($v['last_modified_by']==array('name'=>'')?"":$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$v['last_modified_by']));
			echo '<td><span class="fwfont">'.($v['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$v['last_modified'])).'</span> <a href="/a/users.php?id='.$v['last_modified_by'].'">'.$modified['name'].'</a></td>';
			echo '<td><span class="fwfont">'.($v['date_created']==''?'':$DB->Date('Y-m-d f:i a',$v['date_created'])).'</span> <a href="/a/users.php?id='.$v['created_by'].'">'.$created['name'].'</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}



PrintFooter();

?>