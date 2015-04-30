<?php
global $SITE;

$php_page = 'drawings.php';
$main_table = 'drawing_main';
$drawings_table = 'drawings';
$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/$$/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/$$/%%.xml';
$pdf_link = 'http://'.$_SERVER['SERVER_NAME'].'/pdf/$$/%%.pdf';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/text/$$/text.html';

$embed_code = '<div id="pathwaysContainer" style="width:100%; height:600px"></div>
<script type="text/javascript" src="http://'.$_SERVER['SERVER_NAME'].'/c/published/$$/embed.js"></script>';


$drawing = $DB->LoadRecord($main_table,$id);


$program = $DB->SingleQuery('SELECT * FROM programs WHERE id = '.$drawing['program_id']);

if( count($program) > 0 )
{
	$drawing['full_name'] = $drawing['name'] == '' ? $program['title'] : $drawing['name'];
}

$schools = $DB->VerticalQuery("SELECT * FROM schools WHERE organization_type IN ('CC', 'Other') ORDER BY school_name",'school_name','id');
$schls = $DB->VerticalQuery("SELECT * FROM schools WHERE organization_type IN ('CC', 'Other') ORDER BY school_name",'school_abbr','id');

if( IsAdmin() ) {
	if( $id != "" ) {
		$school_id = $drawing['school_id'];
	} else {
		$school_id = $_SESSION['school_id'];
	}
} else {
	$school_id = $_SESSION['school_id'];
}

$school = $DB->SingleQuery('SELECT * FROM schools WHERE id = '.$school_id);

if( $id != "" ) {
	$published = $DB->SingleQuery("SELECT * FROM $drawings_table WHERE published=1 AND parent_id=".$drawing['id']);
}

?>

<script type="text/javascript" src="/common/jquery-1.3.min.js"></script>
<script type="text/javascript" src="/files/jquery.selectboxes.min.js"></script>
<script type="text/javascript" src="/common/APN.js"></script>
<script type="text/javascript">
	var $j = jQuery.noConflict();
</script>
<script type="text/javascript" src="/files/greybox.js"></script>
<script type="text/javascript" src="/files/drawing_list.js"></script>
<?php /* <script type="text/javascript" src="/c/drawings.js"></script> */ ?>

<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a><br /><br />



<?php if( $id == "" ): ?>

    <?php /** begin new drawing form **/ ?>

    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="drawing_form" class="new_drawing">
        <div id="existingDrawings" style="float:right; width:330px;"></div>
        <table>

            <tr>
                <th width="115"></th>
                <td>
                    <?php /** support current method of form submission. hidden so user uses apn <select>, rather than hand-typing. */ ?>
                    <input type="hidden" id="drawing_title" name="name" size="80" value="">

                    <div id="checkNameResponse" class="error"></div>
                </td>
            </tr>

            <?php if($SITE->hasFeature('oregon_skillset')): ?>
                <tr>
                    <th width="115"><?=l('skillset name')?></th>
                    <td>
                        <div id="skillset">
                            <?php echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', '', array('0'=>'')); ?>
                        </div>
                        <div id="skillsetConf" style="color:#393; font-weight: bold"></div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php if( $school['organization_type'] != 'Other'): ?>
                <tr>
                    <th width="115"><?=l('program name label')?></th>
                    <td>
                        <div id="program">
                            <?php echo GenerateSelectBoxDB('programs', 'program_id', 'id', 'title', 'title', '', array('0'=>'Not Listed')); ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            <tr>
                <th width="115">Organization</th>
                <td>
                    <?php
                    if( IsAdmin() ) {
                        echo GenerateSelectBox($schools,'school_id',$school_id);
                    } else {
                        echo '<b>'.$schools[$school_id].'</b><input type="hidden" name="school_id" id="school_id" value="'.$school_id.'" />';
                    }
                    ?>
                </td>
            </tr>

            <tr>
                <th width="115"></th>
                <td>
                    <div style="float:right">
                        <input type="button" class="submit" value="Reset" id="submitButtonReset">
                    </div>
                    <input type="button" class="submit" value="Create" id="submitButtonCreate">
                </td>
            </tr>

        </table>
        <input type="hidden" name="id" value="">
    </form>

    <?php /** end new drawing form **/ ?>

<?php else: ?>

    <?php /** begin drawing edit form **/ ?>

    <table width="960">

        <tr class="editable">
            <td colspan="2">
                <div id="drawing_header" class="title_img">
                    <?= ShowRoadmapHeader($drawing['id']) ?>
                </div>
            </td>
        </tr>

        <?php include('apn.php'); ?>

        <?php if( $SITE->hasFeature('olmis') && $school['organization_type'] != 'Other' && is_array($published) ): ?>

            <tr class="editable">
                <th width="115">OLMIS</th>
                <td>
                    <div id="olmis_links">
                        <?=ShowOlmisCheckboxes($drawing['id'], false, "This published roadmap is publicly accessible from the following OLMIS occupational reports:")?>
                    </div>
                    <div id="olmis_search"></div>
                    <a href="javascript:void(0);" id="olmis_expand" class="edit"><?=SilkIcon('link_go.png')?> Add Link</a>
                    <div style="width:400px; display:none;" id="olmis_add">
                        <input type="button" id="search_olmis" value="Find OLMIS Links" class="submit tiny" /> Search your drawing for links to OLMIS pages
                        <br />
                        &nbsp;&nbsp;&nbsp;&nbsp;or<br />
                        Enter OLMIS URLs into this box then click "Add"<br />
                        <textarea id="olmis_textarea" style="width:400px;height:40px"></textarea>
                        <input type="button" id="enter_olmis_links" value="Add" class="submit tiny" style="float: right"/>
                        Note: Only <b>full</b> occupational report URLs will be added.
                    </div>
                </td>
            </tr>

            <?php if($drawing['last_olmis_link']): ?>
                <tr class="editable">
                    <th width="115">Last OLMIS Link</th>
                    <td>
                        <a href="<?=$drawing['last_olmis_link']?>"><?=$drawing['last_olmis_link']?></a>
                        last updated on <?php echo(date("m/d/Y",strtotime($drawing['last_olmis_update']))." at ".date("h:ia",strtotime($drawing['last_olmis_update']))); ?>
                    </td>
                </tr>
            <?php endif; ?>

        <?php endif; ?>

        <?php if( is_array($published) ): ?>
            <tr>
                <th width="115">Embed Code</th>
                <td>
                    <textarea style="width:560px;height:40px;" class="code" id="embed_code" onclick="this.select()"><?= htmlspecialchars(str_replace(array('$$','%%'),array($id,CleanDrawingCode($drawing['name'])),$embed_code)) ?></textarea>
                </td>
            </tr>
            <tr>
                <th valign="top" width="115">External Link</th>
                <td>
                    <?php if($external = getExternalDrawingLink($id, 'pathways')): ?>
                        <div style="width:16px; float:left;"><a href="<?=$external?>" target="_blank"><?=SilkIcon('link.png')?></a></div>
                        <input type="text" style="width:496px;" value="<?=$external?>" onclick="this.select()" id="external_link_url" />
                        <input type="button" id="external_link_save" value="save" class="submit small" /><br />
                        <div style="width:560px;">The primary URL is linked on external web pages such as OLMIS and MyPathCareers.org. To change, edit the URL above or select a URL from the list below.</div>
                    <?php else: ?>
                        We did not find any external links embedding this drawing.<br />
                    <?php endif; ?>
                    <br />
                </td>
            </tr>
            <?php require('external_links.php'); ?>
            <tr>
                <th width="115">HTML Link</th>
                <td>
                    <div style="width:16px; float:left; margin-right: 2px;"><a href="javascript:preview_drawing(<?=$published['parent_id'].','.$published['id']?>)"><?=SilkIcon('magnifier.png')?></a></div>
                    <div id="drawing_link"><?php
                    $url = str_replace(array('$$','%%'),array($id,CleanDrawingCode($schls[$drawing['school_id']].'-'.$drawing['full_name'])),$published_link);
                    echo '<input type="text" style="width:542px" value="'.$url.'" onclick="this.select()" />';
                    ?></div>
                </td>
            </tr>
            <tr>
                <th valign="top">PDF Link</th>
                <td><?php
                    $url = str_replace(array('$$','%%'),array($id,CleanDrawingCode($drawing['full_name'])),$pdf_link);
                    ?>
                    <div style="width:16px; float:left; margin-right: 2px;"><a href="<?=$url?>"><?=SilkIcon('page_white_acrobat.png')?></a></div>
                    <div id="drawing_link_pdf">
                        <input type="text" style="width:542px" value="<?=$url?>" onclick="this.select()" />
                    </div>
                </td>
            </tr>
            <tr>
                <th valign="top" width="115">XML Link</th>
                <td>
                    <div id="drawing_link_xml"><?php
                    $url = str_replace(array('$$','%%'),array($id,CleanDrawingCode($schls[$drawing['school_id']].'-'.$drawing['full_name'])),$xml_link);
                    echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
                    ?></div>
                </td>
            </tr>
            <tr>
                <th valign="top" width="115">Accessible Link</th>
                <td>
                    <div id="drawing_link_ada"><?php
                    $url = str_replace('$$',$id,$accessible_link);
                    echo '<input type="text" style="width:560px" value="'.$url.'" onclick="this.select()" />';
                    ?></div>
                    These links, as well as the embed code above, will always link to the <b>published</b> version of this drawing.<br>
                    <br>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <th valign="top" width="115">Links</th>
                <td>Publish a version to get the published links for this drawing.</td>
            </tr>
        <?php endif; ?>

        <?php require('version_list.php'); ?>

        <tr>
            <th width="115">Delete</th>
            <td>
                <?php if( CanDeleteDrawing($drawing['id'], 'pathways') ): ?>
                    <p><a href="javascript:deleteConfirm()" class="noline"><?=SilkIcon('cross.png')?> Delete this drawing and remove <b>all</b> versions</a></p>
                    <div id="deleteConfirm" style="display: none">
                        <p>Please be careful. Deleting this drawing will break any links from external web pages to this drawing.</p>
                        <p><b>There is no way to recover deleted drawings!</b></p>
                        <p>Are you sure? <a href="javascript:doDelete()">Yes</a></p>
                    </div>
                    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="delete_form">
                        <input type="hidden" name="id" value="<?= $drawing['id'] ?>">
                        <input type="hidden" name="delete" value="delete">
                    </form>
                <?php else: ?>
                    You can't delete this drawing because it was created by <a href="/a/users.php?id=<?= $drawing['created_by'] ?>"><?= $DB->GetValue('CONCAT(first_name," ",last_name)','users',$drawing['created_by']) ?></a>. Contact the creator of the drawing or any <a href="/a/users.php">Admin</a> user within your organization to delete this drawing.<br><br>
                    Note: If you're trying to delete a version, there is no need to delete versions, as the Web Tool is designed to maintain archival records of your roadmap designs.
                <?php endif; ?>
            </td>
        </tr>

    </table>

    <?php /** end drawing edit form **/ ?>

<?php endif; ?>



<script type="text/javascript" src="/common/URLfunctions1.js"></script>
<script type="text/javascript">

//For legacy and olmis stuff
var MODE = '<?= $MODE ?>';

var drawing_code = '<?= $drawing['code'] ?>';
var schools = new Array(<?= count($schools) ?>);
<?php
$i=0;
foreach( $schools as $sid=>$school ) {
	echo 'schools['.$i.'] = '.$sid.";\n";
	$i++;
}
?>

var published_link = "<?= $published_link ?>";
var xml_link = "<?= $xml_link ?>";
var accessible_link = "<?= $accessible_link ?>";

var programList;
var program_id;
var drawingCode = '<?=array_key_exists('code',$drawing)?$drawing['code']:''?>';


function saveTitle() {
	if( $j("#program_id").val() == 0 && $j("#drawing_title").val() == "" )
	{
		alert("You must enter either an approved program name or a custom program name");
	}
	else
	{
		$j.get('/a/drawings_post.php',
			  {mode: 'pathways',
			   id: '<?= $drawing['id'] ?>',
			   changeTitle: "true",
			   title: $j("#drawing_title").val()<?php if(IsAdmin()) { ?>,
			   school_id: $j("#school_id").val()
			   <?php } ?>
			  }, function(data){
			  	data = eval(data);
			  	$j("#drawing_title").val(data.title);
			  	$j("#drawing_title").css({backgroundColor: '#99FF99'});
			  	setTimeout(function(){
				  	$j("#drawing_title").css({backgroundColor: '#FFFFFF'});
			  	}, 300);
			  	
				$j("#drawing_header").html(data.header);
				updateDrawingLinks(data.code);
			  });
	}
}


function updateDrawingLinks(newCode)
{
  	drawingCode = newCode;
  	$j("#drawing_link input").val(published_link.replace("$$", <?=($drawing['id']?$drawing['id']:0)?>).replace("%%", drawingCode));
  	$j("#drawing_link_xml input").val(xml_link.replace("$$", <?=($drawing['id']?$drawing['id']:0)?>).replace("%%", drawingCode));
}

function loadProgramTitles() {
	$j("#program_id").removeOption(/./);
	for( var i=0; i<programList.length; i++ )
	{
		$j("#program_id").addOption(programList[i].id, programList[i].title).val(0);
	}
}

$j(document).ready(function(){
    //Create a new APN object with page-specific parameters.
    //APN provides skill set/approved program name sort features.
    var apn = new APN({
        drawingId : '<?= $drawing["id"] ?>', //string to avoid JS errors when PHP prints nothing
        drawingType : '<?= $MODE ?>',
        programId: '<?= $drawing["program_id"] ?>'
    });

	program_id = $j("#program_id").val();

	$j("#submitButtonCreate").click(function(){
		if( $j("#drawing_title").val() === "" )
		{
			alert("You must enter an Approved Program Name.");
		}
		else
		{
			$j.post("/a/drawings.php",
				{
                    id: "",
				    skillset_id: $j("#skillset_id").val(),
				    program_id: $j("#program_id").val(),
				    name: $j("#drawing_title").val(),
//                    name_approved: '1', //all names are approved names after the apn update
				    school_id: $j("#school_id").val()
				},
				function(data){
					data = eval(data);
					window.location = data["redirect"]
				});
		}
	});

	$j("#submitButtonReset").click(function(){
		$j("#skillset select").val(0).change();
		$j("#drawing_title").val("");
		$j("#school_id").val(<?=$_SESSION['school_id']?>);
	});

	// OLMIS stuff
	
	$j("#olmis_expand").click(function(){
		$j("#olmis_add").slideDown(300);
	});

	$j("#search_olmis").click(function(){
		$j("#olmis_search").html('Please wait while we search your drawing for OLMIS links. This may take a while depending on how many versions of your drawing exist.<br /><img src="/images/horiz-green.gif" />');
		$j.post("/a/drawings_post.php",
			{id: '<?=intval($drawing['id'])?>',
			 action: "olmis",
			 mode: "find"},
			function(data){
				json = eval(data);
				$j("#olmis_search").html(json.olmis);				
				$j("#olmis_add").slideUp(300);
				bindOlmisCheckboxes();
			});
	});
	
	$j("#enter_olmis_links").click(function(){
		$j.post("/a/drawings_post.php",
			{id: '<?=intval($drawing['id'])?>',
			 action: "olmis",
			 mode: "add",
			 content: $j("#olmis_textarea").val()},
			function(data){
				json = eval(data);
				$j("#olmis_links").html(json.olmis);
				$j("#olmis_textarea").val("");
				bindOlmisCheckboxes();
			});
	});

	bindOlmisCheckboxes();

});

function bindOlmisCheckboxes()
{
	$j("#olmis_links input").unbind("click").click(function(){
		var id = $j(this).attr("id").split("_")[1];
		mode = ($j(this).attr("checked") ? "enable" : "disable");
			
		$j.post("/a/drawings_post.php",
			{id: '<?=intval($drawing['id'])?>',
			 action: "olmis",
			 mode: mode,
			 code: id
			},
			function(data){
				if( mode == "disable" )
				{
					$j("#olmischk_"+id).remove();
				}
			});
	});

	$j("#olmis_search input").unbind("click").click(function(){
		var id = $j(this).attr("id").split("_")[1];

		$j.post("/a/drawings_post.php",
			{id: '<?=intval($drawing['id'])?>',
			 action: "olmis",
			 mode: "enable",
			 code: id
			},
			function(data){
				$j("#olmischk_"+id).appendTo("#olmis_links");
				bindOlmisCheckboxes();
			});
	});	
}

<?php if( $drawing['id'] && CanDeleteDrawing($drawing['id'], 'pathways') ): ?>
    function deleteConfirm() {
        getLayer('deleteConfirm').style.display = "block";
    }

    function doDelete() {
        getLayer('delete_form').submit();
    }
<?php endif; ?>

</script>
