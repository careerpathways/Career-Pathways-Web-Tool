<?php

$published_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/$$/%%.html';
$xml_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/%%.xml';
$pdf_link = 'http://'.$_SERVER['SERVER_NAME'].'/pdf/post/$$/%%.pdf';
$accessible_link = 'http://'.$_SERVER['SERVER_NAME'].'/c/post/text/%%.html';


$drawing = $DB->LoadRecord('post_drawing_main',$id);

// force non-admins to the school of this drawing to prevent hacks
$schools = $DB->VerticalQuery("SELECT * FROM schools ORDER BY school_name",'school_name','id');
$schls = $DB->VerticalQuery("SELECT * FROM schools ORDER BY school_name",'school_abbr','id');
if( IsAdmin() ) {
	if( $id != "" ) {
		$school_id = $drawing['school_id'];
	} else {
		$school_id = $_SESSION['school_id'];
	}
} else {
	$school_id = $_SESSION['school_id'];
}

if( $id != "" ) {
	$published = $DB->SingleQuery("SELECT * FROM post_drawings WHERE published=1 AND parent_id=".$drawing['id']);
}

?>

<script type="text/javascript" src="/common/jquery-1.3.min.js"></script>
<?php if($SITE->hasFeature('approved_program_name')): ?>
<script type="text/javascript" src="/common/APN.js"></script>
<?php endif;?>

<script type="text/javascript">
var $j = jQuery.noConflict();
</script>
<script type="text/javascript" src="/files/greybox.js"></script>
<!--<script type="text/javascript" src="/files/post_drawing_list.js"></script>-->
<!-- <script type="text/javascript" src="/c/drawings.js"></script> -->
<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

<p>
<?php if( $id == "" ): ?>
	<?php /** begin new drawing form **/ ?>
	<?php $drawing = array('id'=>'', 'code'=>'', 'name'=>''); ?>
	<?php if($SITE->hasFeature('approved_program_name')): ?>
		<?php include('post_drawing_info_new_form_with_APN.php') ?>
	<?php else: ?>
		<?php include('post_drawing_info_new_form.php') ?>
	<?php endif; ?>
	<?php /** end new drawing form **/ ?>
<?php else: ?>
	<?php /** begin drawing edit form **/ ?>
	<?php if($SITE->hasFeature('approved_program_name')): ?>
		<?php include('post_drawing_edit_form_with_APN.php') ?>
	<?php else: ?>
		<?php include('post_drawing_edit_form_without_APN.php') ?>
	<?php endif; ?>
	<?php /** end drawing edit form **/ ?>
<?php endif; ?>
</p>

<script type="text/javascript" src="/common/URLfunctions1.js"></script>
<script type="text/javascript">

var MODE = '<?= $MODE ?>';

var drawing_code = '<?= $drawing['code'] ?>';

var published_link = "<?= $published_link ?>";
var xml_link = "<?= $xml_link ?>";
var accessible_link = "<?= $accessible_link ?>";

Array.prototype.remove = function(s) {
	var i = this.indexOf(s);
	if(i != -1) this.splice(i, 1);
}


function checkName(title) {
	$j.get('/a/drawings_checkname.php',
		  {mode: 'post',
		   id: '<?= $drawing['id'] ?>',
		   title: title.value<?php if(IsAdmin()) { ?>,
		   school_id: $j("#school_id").val()
		   <?php } ?>
		  },
		  verifyName);
}

function verifyName(result) {
	if( result == 0 ) {
		getLayer('checkNameResponse').innerHTML = 'There is already a drawing by that name. Choose a different name.';
		$j('#submitButton').css('color', '#666666');
	} else {
		getLayer('checkNameResponse').innerHTML = '';
		$j('#submitButton').css('color', '');
	}
}
<?php if($SITE->hasFeature('approved_program_name')): ?>
function saveTitle() {
	if( $j("#program_id").val() == 0 && $j("#drawing_title").val() == "" )
	{
		alert("You must enter either an approved program name or a custom program name");
	}
	else
	{
		$j.get('/a/drawings_post.php',
			  {mode: 'post',
			   id: '<?= $drawing['id'] ?>',
			   changeTitle: "true",
			   title: $j("#drawing_title").val()<?php if(IsAdmin()) { ?>,
			   school_id: $j("#school_id").val()
			   <?php } ?>
			  }, function(data){
			  	data = eval(data);
			  	$j("#drawing_title").val(data.title);
			  	//$j("#drawing_title").css({backgroundColor: '#99FF99'});
			  	setTimeout(function(){
				  //	$j("#drawing_title").css({backgroundColor: '#FFFFFF'});
			  	}, 300);
			  	
				$j("#drawing_header").html(data.header);
				updateDrawingLinks(data.code);
				$j("body").trigger("drawingheaderchanged");
			  });
	}
}

function updateDrawingLinks(newCode)
{
  	drawingCode = newCode;

    var published_link = "<?= $published_link ?>";
    var pdf_link = "<?= $pdf_link ?>";
    var xml_link = "<?= $xml_link ?>";
    var ada_link = "<?= $accessible_link ?>";

  	$j("#drawing_link input").val(published_link.replace("$$", <?=($drawing['id']?$drawing['id']:0)?>).replace("%%", drawingCode));
    $j("#drawing_link_pdf input").val(pdf_link.replace("$$", <?=($drawing['id']?$drawing['id']:0)?>).replace("%%", drawingCode));
  	$j("#drawing_link_xml input").val(xml_link.replace("$$", <?=($drawing['id']?$drawing['id']:0)?>).replace("%%", drawingCode));
    $j("#drawing_link_ada input").val(ada_link.replace("$$", <?=($drawing['id']?$drawing['id']:0)?>).replace("%%", drawingCode));
}

<?php else: ?>
function savetitle() {
	var title = getLayer('drawing_title');
	$j.get('/a/drawings_checkname.php',
		  {mode: 'post',
		   id: '<?= $drawing['id'] ?>',
		   title: title.value<?php if(IsAdmin()) { ?>,
		   school_id: $j("#school_id").val()
		   <?php } ?>
		  },
		  verifyNameSubmit);
}
<?php endif;?>

function submitform() {
	var title = getLayer('drawing_title');
	$j.get('/a/drawings_checkname.php',
		  {mode: 'post',
		   id: '<?= $drawing['id'] ?>',
		   title: title.value<?php if(IsAdmin()) { ?>,
		   school_id: $j("#school_id").val()
		   <?php } ?>
		  },
		  verifyNameSubmitNew);
}

function verifyNameSubmitNew(result) {
	if( result == 0 ) {
		verifyName(0);
	} else {
		getLayer('drawing_form').submit();
	}
}

function verifyNameSubmit(result) {
	if( result == 0 ) {
		verifyName(0);
	} else {
		var title = getLayer('drawing_title');
		ajaxCallback(cbNameChanged, '/a/drawings_post.php?mode=<?= $MODE ?>&id=<?= $drawing['id'] ?>&title='+URLEncode(title.value));
	}
}

function cbNameChanged(drawingCode) {
	drawing_code = drawingCode;
	getLayer('title_value').innerHTML = getLayer('drawing_title').value;
	getLayer('title_edit').style.display = 'none';
	getLayer('title_fixed').style.display = 'block';
}

function showTitleChange() {
	getLayer('title_edit').style.display = 'block';
	getLayer('title_fixed').style.display = 'none';
}

function preview_drawing(code,version) {
	if( version == null )
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/post/'+code+'.html"></iframe></div>',800,600, null, 'Preview');
	else
		chGreybox.create('<div id="dpcontainer"><iframe src="/c/post/'+code+'/'+version+'.html"></iframe></div>',800,600, null, 'Preview');
}

<?php if( $drawing['id'] && CanDeleteDrawing($drawing['id']) ) { ?>
function deleteConfirm() {
	getLayer('deleteConfirm').style.display = "block";
}
function doDelete() {
	getLayer('delete_form').submit();
}
<?php } ?>

<?php if($SITE->hasFeature('approved_program_name')): ?>
	(function($){
		$(document).ready(function(){
	        //Create a new APN object with page-specific parameters.
	        //APN provides skill set/approved program name sort features.
	        var apn = new APN({
	            drawingId : '<?= $drawing["id"] ?>',
	            drawingType : '<?= $MODE ?>',
	            programId: '<?= $drawing["program_id"] ?>'
	        });
		});
	}($j));
<?php else: ?>
<?php if( $drawing['id'] ) { ?>

$j(document).ready(function(){
	$j('#skillset select').bind('change', function() {
		$j('#skillsetConf').html('Saved!');
		$j('#skillset select').css({backgroundColor: '#99FF99'});
		setTimeout(function() {
			$j('#skillset select').css({backgroundColor: '#FFFFFF'});
			$j('#skillsetConf').html('');
		}, 500);
		$j.post('drawings_post.php',
			{action: 'skillset',
			 mode: 'post',
			 id: <?= intval($drawing['id']) ?>,
			 skillset_id: $j('#skillset select').val()
			},
			function() {
			}
		);
	});
});
					


<?php } ?>
<?php endif; ?>
</script>
