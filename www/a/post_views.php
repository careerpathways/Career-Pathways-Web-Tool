<?php
chdir("..");
include("inc.php");

$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/study/$$/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/study/$$/%%.xml';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/study/text/$$/%%.html';
$embed_code = '<iframe width="800" height="600" src="'.$published_link.'" frameborder="0" scrolling="no"></iframe>';

$embed_code = '<div id="postContainer" style="width:100%; height:600px"></div>
<script type="text/javascript" src="http://'.$_SERVER['SERVER_NAME'].'/c/study/$$/embed.js"></script>';



$MODE = 'post_views';
ModuleInit('post_views');



if (KeyInRequest('action')) {
	$action = $_REQUEST['action'];
	switch ($action) {
		case 'drawing_list':
			// used to select a drawing in the connections chooser
			processDrawingListRequest();
			die();
		case 'change_name':
			processChangeNameRequest();
			die();
		case 'create':
			processCreateRequest();
			die();
		case 'save_tab_name':
			processTabNameRequest();
			die();
		case 'delete':
			processDeleteRequest();
			die();
		default:
			die('unknown action');
	}
}

$TEMPLATE->addl_scripts[] = '/common/jquery-1.3.min.js';
$TEMPLATE->addl_scripts[] = '/common/URLfunctions1.js';
$TEMPLATE->toolbar_function = 'ShowSymbolLegend';

function ShowSymbolLegend() {
	$helpFile = 'drawing_list';
	$onlyLegend = TRUE;
	require('view/drawings/helpbar.php');
}

PrintHeader();


$id = Request('id')?intval(Request('id')):'';

$schools = $DB->VerticalQuery("SELECT * FROM schools ORDER BY school_name",'school_name','id');
if( IsAdmin() ) {
	if( Request('school_id') != "" ) {
		$school_id = Request('school_id');
	} else {
		$school_id = $_SESSION['school_id'];
	}
} else {
	$school_id = $_SESSION['school_id'];
}
$school = $DB->SingleQuery("SELECT * FROM schools WHERE id=$school_id");




if( $id )
{
	$view = $DB->SingleQuery('SELECT * FROM vpost_views WHERE id='.$id);
	$school_id = $view['school_id'];
	$school = $DB->SingleQuery("SELECT * FROM schools WHERE id=$school_id");

	?>
	<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a><br /><br />
	
	<script type="text/javascript" src="/files/greybox.js"></script>
	<table width="100%">
	<tr>
		<th>Occupation/Program</th>
		<td>
			<div id="title_fixed"><span id="title_value"><?= $view['name'] ?></span> <a href="javascript:showTitleChange()" class="tiny">edit</a></div>
			<div id="title_edit" style="display:none">
				<input type="text" id="drawing_title" name="name" size="80" value="<?= $view['name'] ?>">
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
		<th>Embed Code</th>
		<td>
			<textarea style="width:560px;height:40px;" class="code" id="embed_code" onclick="this.select()"><?= htmlspecialchars(str_replace(array('$$','%%'),array($view['id'],CleanDrawingCode($view['name'])),$embed_code)) ?></textarea>
		</td>
	</tr>
	<tr>
		<th valign="top">External Link</th>
		<td>
			<?php 
			if($external = getExternalDrawingLink($id, 'post'))
			{
				?>
				<div style="width:16px; float:left;"><a href="<?=$external?>" target="_blank"><?=SilkIcon('link.png')?></a></div>
				<input type="text" style="width:544px;" value="<?=$external?>" onclick="this.select()" /><br />
				<?php 
			}
			?>
			<br />
		</td>
	</tr>
	<tr>
		<th>HTML Link</th>
		<td>
			<div id="drawing_link"><?php
			echo '<div style="width:16px; float:left;"><a href="javascript:preview_postview(' . $view['id'] . ',0,\'post_view\')">' . SilkIcon('magnifier.png') . '</a></div>';
			$url = str_replace(array('$$','%%'),array($view['id'],CleanDrawingCode($view['name'])),$published_link);
			echo '<input type="text" style="width:544px" value="'.$url.'" onclick="this.select()" />';
			?></div>
		</td>
	</tr>
	<?php 
		require('view/drawings/external_links.php');
	?>
	<tr>
		<th>Delete</th>
		<td>
			<a href="javascript:void(0);" id="deleteLink" class="noline"><?=SilkIcon('cross.png')?> Delete this view</a> &nbsp;&nbsp;
			<div id="deleteConfirm" style="display:none">
				<p>Deleting this view will not delete the drawings associated with it. Click the link below to delete only this view.</p>
				<p>Are you sure? <a href="javascript:void(0);">yes</a></p>
			</div><br />
			<br />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div style="display:inline"><a href="javascript:addDrawingToView('hs')"><?= SilkIcon('add.png') ?></a></div>
			<h3 style="display:inline">High School Programs</h3>
			<div id="connected_drawing_list_HS">
			<?php
				ShowSmallDrawingConnectionList($id, 'HS', array(
					'delete'=>'javascript:deleteDrawingFromView(\'HS\', %%)',
				));
			?>
			</div>
			<br />
			
			<div style="display:inline"><a href="javascript:addDrawingToView('cc')"><?= SilkIcon('add.png') ?></a></div>
			<h3 style="display:inline">Community College Pathways</h3>
			<div id="connected_drawing_list_CC">
			<?php
				ShowSmallDrawingConnectionList($id, 'CC', array(
					'delete'=>'javascript:deleteDrawingFromView(\'CC\', %%)',
				));
			?>
			</div>
			
		</td>
	</tr>
	</table>
	<script type="text/javascript">

	var $j = jQuery.noConflict();

	var selected_drawings = Array();


	if(!Array.indexOf){
	    Array.prototype.indexOf = function(obj){
	        for(var i=0; i<this.length; i++){
	            if(this[i]==obj){
	                return i;
	            }
	        }
	        return -1;
	    }
	}

	Array.prototype.remove = function(s) {
		var i = this.indexOf(s);
		if(i != -1) this.splice(i, 1);
	}

	$j(document).ready(function(){
		bindTabNameBoxes();
		
		$j("#deleteLink").click(function(){
			$j("#deleteConfirm").css("display", "inline");
			$j("#deleteConfirm a").click(function(){
				$j.post("post_views.php",
						{id: <?= $id ?>,
						 action: "delete"
						},
						function(data) {
							window.location = "post_views.php";
						});
			});
		});
		
	});

	function showTitleChange() {
		getLayer('title_edit').style.display = 'block';
		getLayer('title_fixed').style.display = 'none';
	}

	function savetitle() {
		$j.get("post_views.php",
			{id: <?= $id ?>,
			 title: $j('#drawing_title').val(),
			 action: "change_name"
			},
			function(data) {
				getLayer('title_value').innerHTML = data;
				getLayer('title_edit').style.display = 'none';
				getLayer('title_fixed').style.display = 'block';
			}
		);
	}

	function preview_drawing(code) 
	{
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/post/'+code+'.html"></iframe></div>',800,600, null, 'Preview');
	}
	function preview_postview(id)
	{
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/study/'+id+'/view.html"></iframe></div>',800,600, null, 'Preview');
	} 
	
	function addDrawingToView(type)
	{
		$j.get("post_views.php",
			{type: type, drawing_id: <?= $id ?>, action: 'drawing_list', showForm: 1, school_id: <?=$school_id?>},
			function(data) {
				chGreybox.create(data, 700,500);
			}
		);
	}
	
	
	function createConnection(drawing_id)
	{
		$j.get("post_views.php",
			{drawing_id: <?= $id ?>, action: 'drawing_list', showForm: 1},
			function(data) {
				chGreybox.create(data, 700,500);
			}
		);
	}
	
	function loadDrawingPreview(code)
	{
		$j('#drawing_preview_box iframe').attr('src', '/c/post/'+code+'.html');
		$j('#drawing_preview_box').css('display', 'block');
	}

	function select_organization(val)
	{
		$j('#drawing_preview_box').css('display', 'none');
		$j('#drawing_preview_box iframe').attr('src', '');
		$j.get("post_views.php",
			{
			 school_id: $j("#list_schools option:selected").val(),
			 action: 'drawing_list'
			},
			function(data) {
				$j('#list_of_drawings').html(data);
				$j('#submit_btn').css({display:'none'});
				$j('.drawing_select').hover(
					function() {
						$j(this).css({'background-color': '#FFFFAA', cursor: 'pointer'});
					},
					function() {
						$j(this).css({'background-color': '#FFFFFF', cursor: 'normal'});
					}
				);
				$j('.drawing_select > td:not(.preview)').click(
					function(e) {
						var dr_id = $j(this).parent().attr('id').split('_')[1];

						if( selected_drawings.indexOf(dr_id) == -1 )
						{
							$j(this).parent().children('.icon').children().css({opacity: 0, display: "block"}).animate({opacity:1}, 150);
							selected_drawings.push(dr_id);
						}
						else
						{
							$j(this).parent().children('.icon').children().css({opacity: 1, display: "block"}).animate({opacity:0}, 150, function() {
									$j(this).css({display:'none'});
								});
							selected_drawings.remove(dr_id);
						}
						if( selected_drawings.length > 0 )
						{
							$j('#submit_btn').css({display:'block'});
						}
						else
						{
							$j('#submit_btn').css({display:'none'});
						}
					}
				);
			}
		);
	}

	function save_drawing_selection(type)
	{
		if( selected_drawings.length > 0 )
		{
			$j.post("post_views.php",
				{action: 'drawing_list',
				 drawing_id: <?= $id ?>,
				 save: 1,
				 type: type,
				 drawings: selected_drawings.join(",")
				},
				function(data) {
					$j('#connected_drawing_list_'+type).html(data);
					chGreybox.close();
					selected_drawings = Array();
					bindTabNameBoxes();
				});
		}
	}

	function deleteDrawingFromView(type, id)
	{
			$j.post("post_views.php",
				{'action': 'drawing_list',
				 'drawing_id': <?= $id ?>,
				 'delete': id,
				 'type': type
				},
				function(data) {
					$j('#connected_drawing_list_'+type).html(data);
					bindTabNameBoxes();
				});
	}

	function bindTabNameBoxes()
	{
		$j(".tabName").bind("change", function(){
			$j("#tabNameBtn_"+$j(this).attr("id").split("_")[1]).click();
		});
		$j(".tabNameBtn").click(function(){
			var postID = $j(this).attr("id").split("_")[1];
			if( $j("#tabName_"+postID).val() != "" ) {
				$j.post("<?=$_SERVER['PHP_SELF']?>",
						{action: "save_tab_name",
						 vid: <?=request('id')?>,
						 post_id: postID,
						 tab_name: $j("#tabName_"+postID).val(),
						 tab_sort: $j("#tabSort_"+postID).val()},
						function(data){
							$j("#tabName_"+postID).val(data.name);
							$j("#tabSort_"+postID).val(data.sort);
							$j(".tabID_"+postID).css('background-color', '#99FF99');
							setTimeout(function(){
								$j(".tabID_"+postID).css('background-color', '');
							}, 400);
						}, 'json');
			}
		});
	}

	</script>
	<?php
}
elseif( KeyInRequest('id') )
{
	?>
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="drawing_form">
	<table>
	<tr>
		<th valign="bottom">Occupation/Program</th>
		<td>
			<input type="text" id="title" name="name" size="60" value="">
		</td>
	</tr>
	<tr>
		<th width="80">Organization</th>
		<td>
		<?php
		if( IsAdmin() ) {
			$these_schools = $DB->VerticalQuery('SELECT * FROM schools ORDER BY school_name', 'school_name', 'id');
			echo GenerateSelectBox($these_schools, 'school_id', $school_id);
		} else {
			echo '<b>'.$schools[$school_id].'</b>';
		}
		?>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><input type="submit" class="submit" value="Create" id="submitButton"></td>
	</tr>
	</table>
	<input type="hidden" name="action" value="create" />
	</form>
	<?php
}
else
{
	echo '<a href="' . $_SERVER['PHP_SELF'] . '?id&school_id='.$school_id.'" class="edit">' . SilkIcon('add.png') . ' new view</a><br /><br />';

	if( IsAdmin() ) {
		echo '<h2>' . $school['school_name'] . '</h2>';
		echo '<div style="margin-top: 4px">';
		echo 'Switch Organization: ';
		$schools_ = $DB->VerticalQuery('SELECT id, school_name FROM schools ORDER BY school_name','school_name','id');
		$schools = array("-1"=>'') + $schools_;
		echo GenerateSelectBox($schools,'school_id',-1,'switch_school(this.value)');
		echo '</div>';
		echo '<hr>';
	}
	else
	{
		echo '<h2>'.$school['school_name'].'</h2>';
	}
	
	$views = $DB->MultiQuery('SELECT * FROM vpost_views WHERE school_id='.$school_id.' ORDER BY name');
	echo '<table width="100%">';
		echo '<tr>';
			echo '<th width="20">&nbsp;</th>';
			echo '<th>Occupation/Program</th>';
			echo '<th width="240">Last Modified</th>';
			echo '<th width="240">Created</th>';
		echo '</tr>';
	foreach( $views as $i=>$v )
	{
		echo '<tr class="row' . ($i%2) . '">';
			echo '<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$v['id'].'" class="edit"><img src="/common/silk/cog.png" width="16" height="16" title="Drawing Properties" /></a></td>';
	
			echo '<td>' . $v['name'] . '</td>';
	
			$created = ($v['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$v['created_by']));
			$modified = ($v['last_modified_by']==array('name'=>'')?"":$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$v['last_modified_by']));
			echo '<td><span class="fwfont">'.($v['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$v['last_modified'])).'</span> <a href="/a/users.php?id='.$v['last_modified_by'].'">'.$modified['name'].'</a></td>';
			echo '<td><span class="fwfont">'.($v['date_created']==''?'':$DB->Date('Y-m-d f:i a',$v['date_created'])).'</span> <a href="/a/users.php?id='.$v['created_by'].'">'.$created['name'].'</a></td>';
		echo '</tr>';
	}
	if( count($views) == 0 )
	{
		echo '<tr class="row0"><td colspan="4">No views exist yet for your school</td></tr>';
	}
	echo '</table>';


	$views = $DB->MultiQuery('SELECT v.*, school_name
		FROM vpost_views v
		JOIN vpost_links vl ON vl.vid=v.id
		JOIN post_drawing_main p ON vl.post_id=p.id
		JOIN schools s ON s.id=v.school_id
		WHERE p.school_id='.$school_id.'
			AND p.school_id!=v.school_id
		ORDER BY v.name');
	if( count($views) > 0 )
	{
		echo '<br /><br />';
		echo '<h2>Affiliated Organizations</h2>';
		echo '<table width="100%">';
			echo '<tr>';
				echo '<th width="20">&nbsp;</th>';
				echo '<th>Occupation/Program</th>';
				echo '<th>URL</th>';
				echo '<th width="240">Organization</th>';
			echo '</tr>';
		foreach( $views as $i=>$v )
		{
			echo '<tr class="row' . ($i%2) . '">';
				echo '<td><img src="/images/blank.gif" width="16" height="16" /></td>';
		
				echo '<td>' . $v['name'] . '</td>';
				$url = 'http://'.$_SERVER['SERVER_NAME'].'/c/study/'.$v['id'].'/'.CleanDrawingCode($v['name']).'.html';
				echo '<td><input type="text" style="width: 300px" value="'.$url.'" onclick="this.select()" /><a href="'.$url.'" target="_blank">'.SilkIcon('link.png').'</a></td>';
		
				echo '<td>' . $v['school_name'] . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}


	?>	
	<script type="text/javascript">
	function switch_school(id) {
		window.location = "post_views.php?school_id=" + id;
	}
	</script>
	<?php	
}



PrintFooter();




function processDrawingListRequest()
{
	global $DB;

	if( Request('showForm') == 1 )
	{
	?>
	<div id="connectionForm">
		<div style="width:700px;margin:0 auto;">
			<h3>Organizations</h3>
			<?php
			$k1 = (Request('type')=='hs'?'hs':'cc');
			$k2 = (Request('type')=='cc'?'hs':'cc');

			// "Other" orgs should be able to choose any CC or Other org, not just their own
			$mySchool = $DB->SingleQuery('SELECT * FROM schools WHERE id = ' . $_SESSION['school_id']);
			if($mySchool['organization_type'] == 'Other' && Request('type') == 'cc')
			{
				$schools = $DB->VerticalQuery('SELECT *
					FROM schools
					WHERE organization_type IN ("CC", "Other")
					ORDER BY id='.Request('school_id').' DESC, school_name', 'school_name', 'id');
			}
			else
			{
				$schools = $DB->VerticalQuery('SELECT *
					FROM schools
					WHERE organization_type IN (' . (Request('type')=='hs'?'"HS"':'"CC","Other"') . ')
						AND ( schools.id IN (SELECT '.$k1.'_id FROM hs_affiliations WHERE '.$k2.'_id='.Request('school_id').')
								OR schools.id = '.Request('school_id').' )
					ORDER BY school_name', 'school_name', 'id');
			}

			echo '<select size="6" id="list_schools" style="width:100%" onchange="select_organization(this.value)">';
			foreach( $schools as $sid=>$school )
			{
				echo '<option value="'.$sid.'">'.$school.'</option>';
			}
			echo '</select>';
			?>
			<br />
			
			<div id="submit_btn" style="display:none" onclick="save_drawing_selection('<?= strtoupper(request('type')) ?>')">Save</div>
			
			<div id="list_of_drawings"></div>

			<div id="drawing_preview_box" style="display:none;margin:0 auto;width:700px;height:400px;"><h3>Preview</h3><iframe style="width:700px;height:400px;background-color:#FFFFFF;"></iframe></div>
		
		</div>
	</div>
	<script type="text/javascript">
		$j(document).ready(function(){
			$j('#list_schools option[value=<?= $_SESSION['school_id'] ?>]').attr('selected','selected').trigger('change');
		});
	</script>
	<?php
	}
	elseif( Request('save') == 1 || Request('delete') )
	{
		$drawing_id = intval(Request('drawing_id'));
		$type = $DB->GetValue('type', 'post_drawing_main', $drawing_id);

		if( Request('save') == 1 )
		{
			foreach( explode(',', Request('drawings')) as $link_id )
			{
				$test = $DB->MultiQuery('SELECT * FROM vpost_links WHERE vid='.$drawing_id.' AND post_id='.$link_id);

				if( count($test) == 0 )
				{
					$info = $DB->SingleQuery('SELECT school_name
											 FROM post_drawing_main AS pdm
											 JOIN schools ON school_id=schools.id
											 WHERE pdm.id = '.$link_id);
					$tab_name = str_replace(array(' High School', ' Community College'), '', $info['school_name']);
					$DB->Insert('vpost_links', array('vid'=>$drawing_id, 'post_id'=>$link_id, 'tab_name'=>$tab_name));
				}
			}
		}
		elseif( Request('delete') )
		{
			$link_id = intval(Request('delete'));
			$DB->Query('DELETE FROM vpost_links WHERE vid='.$drawing_id.' AND post_id='.$link_id);
		}

		$view = array();
		$view['last_modified'] = $DB->SQLDate();
		$view['last_modified_by'] = $_SESSION['user_id'];
		$DB->Update('vpost_views', $view, $drawing_id);
		
		ShowSmallDrawingConnectionList($drawing_id, strtoupper(request('type')), array(
			'delete'=>'javascript:deleteDrawingFromView(\''.strtoupper(request('type')).'\', %%)'
		));
	}
	else
	{
		// return a formatted list of drawings
		$drawings = $DB->MultiQuery('SELECT M.*, CONCAT(U.first_name," ",U.last_name) AS modified_by
			FROM post_drawing_main M
			JOIN post_drawings D ON D.parent_id=M.id
			LEFT JOIN users U ON M.last_modified_by=U.id
			WHERE M.school_id='.intval(Request('school_id')).'
				AND D.published=1
			ORDER BY name');
		echo '<h3>Drawings</h3>';
		echo '<div style="background-color:#ffffff; border: 1px #999999 solid; padding:4px"><table width="100%">';
		foreach( $drawings as $d )
		{
			echo '<tr class="drawing_select" id="d_'.$d['id'].'">';
				echo '<td width="20" class="icon"><img src="/common/silk/tick.png" width="16" height="16" style="display:none" /></td>';
				echo '<td>' . $d['name'] . '</td>';
				echo '<td width="40" class="preview"><a href="javascript:loadDrawingPreview(\''.$d['code'].'\')"><img src="/common/silk/magnifier.png" width="16" height="16" /></a></td>';
				echo '<td width="155"><span class="fwfont">'.($d['last_modified']==''?'':$DB->Date('Y-m-d f:i a',$d['last_modified'])).'</span></td>';
				echo '<td width="130">' . $d['modified_by'] . '</td>';
			echo '</tr>';
		}
		echo '</table>';
		if( count($drawings) == 0 )
		{
			echo 'No published drawings were found for this school.';
		}
		echo '</div>';
	}	
	
}

function processChangeNameRequest()
{
	global $DB;
	
	$view = array();
	$view['name'] = Request('title');
	$view['last_modified'] = $DB->SQLDate();
	$view['last_modified_by'] = $_SESSION['user_id'];
	$DB->Update('vpost_views', $view, Request('id'));
	
	echo Request('title');
}

function processCreateRequest()
{
	global $DB;
	
	$last = $DB->SingleQuery('SELECT MAX(id) AS id FROM vpost_views');
	$code = base_convert($last['id']*16, 10, 26);

	$newCode = '';
	for( $i=0; $i<strlen($code); $i++ )
		$newCode .= chr(ord('a')+base_convert(substr($code, $i, 1), 26, 10));

	$view = array();
	$view['name'] = Request('name');
	$view['school_id'] = (IsAdmin() ? Request('school_id') : $_SESSION['school_id']);
	$view['date_created'] = $DB->SQLDate();
	$view['last_modified'] = $DB->SQLDate();
	$view['created_by'] = $_SESSION['user_id'];
	$view['last_modified_by'] = $_SESSION['user_id'];
	$view['`code`'] = $newCode;
	$view_id = $DB->Insert('vpost_views', $view);
	
	header('Location: post_views.php?id='.$view_id);
}

function processTabNameRequest()
{
	global $DB;
	
	$tab_name = preg_replace('/[^a-zA-Z0-9 \-]/', '', Request('tab_name'));
	$tab_sort = intval(Request('tab_sort'));
	$DB->Query('UPDATE vpost_links SET tab_name = "'.$tab_name.'", sort = "'.$tab_sort.'" WHERE vid = ' . intval(Request('vid')) . ' AND post_id = ' . intval(Request('post_id')));
	echo json_encode(array('name'=>$tab_name, 'sort'=>$tab_sort));
}


function processDeleteRequest()
{
	global $DB;
	
	$DB->Query('DELETE FROM vpost_views WHERE id = '.intval(Request('id')));	
}

?>
