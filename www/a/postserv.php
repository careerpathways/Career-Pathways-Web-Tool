<?php

/**
 * How this file works:
 * 
 * Each AJAX call is categorized as either a "prompt" or "commit" mode. Prompts are for displaying inside
 * of a greybox, while commits are for doing silent actions (like a swap).
 * 
 * Moste often, a prompt will immediately be followed by a commit.
 * 
 * The types are: cell, head, footer, swap.  These represent the different actions this AJAX handler is
 * capable of handing.
 * 
 * The file is split into 4 sections:
 *   Very Top: Decide how to act
 *   Middle Top: Write all our prompt (HTML/Javascript)
 *   Middle Bottom: Write all the commit code (SQL)
 *   Bottom: Write all the helper code (HTML/Javascript) for the prompts
 */

chdir("..");
require_once("inc.php");
require_once("POSTChart.inc.php");

	/**
	 * AJAX Handler for POST Drawings
	 */
	switch($_GET['mode'])
	{
		case "prompt":
?>
<div class="postGreyboxWrapper">
	<div class="postGreyboxContent">
<?php
			if($_GET['type'] == 'cell')
				printCellForm($_GET['id']);

			elseif($_GET['type'] == 'head')
				printHeadForm($_GET['id']);

			elseif($_GET['type'] == 'rowTitle')
				printRowTitleForm($_GET['id']);

			elseif($_GET['type'] == 'footer')
				printFooterForm($_GET['id']);
				
			elseif($_GET['type'] == 'header')
				printHeaderForm($_GET['id']);

			elseif($_GET['type'] == 'sidebar_right')
				printSidebarRightForm($_GET['id']);

			elseif($_GET['type'] == 'swap')
				die('<div class="greyboxError">Cannot Prompt to Swap, must Commit to Swap</div>');

			else
				die('<div class="greyboxError">Misunderstood Prompting</div>');
?>
	</div>
</div>
<?php
		break;
		case "commit":

			switch( $_GET['type'] )
			{
				case 'cell':
					$version_id = $DB->GetValue('drawing_id', 'post_cell', intval($_GET['id']));
					break;
				case 'head':
					$version_id = $DB->GetValue('drawing_id', 'post_col', intval($_GET['id']));
					break;
				case 'rowTitle':
					$version_id = $DB->GetValue('drawing_id', 'post_row', intval($_GET['id']));
					break;
				case 'footer':
				case 'header':
				case 'sidebar_right':
					if( array_key_exists('action', $_POST) )
					{
						commitConfigSidebar();
						die();
					}
					else
						$version_id = intval($_GET['id']);
					break;
				case 'swap':
					$version_id = $DB->GetValue('drawing_id', 'post_cell', intval($_POST['toID']));
					break;
				case 'legend':
					if(IsAdmin())
						commitLegend($_GET['id']);
					else
						$version_id = -1;
				default:
					die('<div class="greyboxError">Misunderstood Commit Type</div>');
			}

			if( !CanEditVersion($version_id) )
			{
				die('Cannot edit this drawing due to a permissions error');
			}
			$drawing = $DB->SingleQuery("SELECT * FROM post_drawings WHERE id=".$version_id);

			$update = array();
			$update['last_modified'] = $DB->SQLDate();
			$update['last_modified_by'] = $_SESSION['user_id'];
			$DB->Update('post_drawings', $update, $version_id);
			$DB->Update('post_drawing_main', $update, $drawing['parent_id']);
			
			switch( $_GET['type'] )
			{
				case 'cell':
					commitCell($_GET['id']);
					break;
				case 'head':
					commitHead($_GET['id']);
					break;
				case 'rowTitle':
					commitRowTitle($_GET['id']);
					break;
				case 'header':
					commitHeader($_GET['id']);
					break;
				case 'footer':
					commitFooter($_GET['id']);
					break;
				case 'sidebar_right':
					commitSidebarRight($_GET['id']);
					break;
				case 'swap':
					commitSwap($_POST['fromID'], $_POST['toID']);
					break;
			}

		break;
		case "fetch":
			switch( $_GET['type'] )
			{
				case 'cell':
					$version_id = $DB->GetValue('drawing_id', 'post_cell', intval($_GET['id']));
					break;
			}
			if( !CanEditVersion($version_id) )
			{
				die('Cannot edit this drawing due to a permissions error');
			}
			switch( $_GET['type'] )
			{
				case 'cell':
					fetchCell($_GET['id']);
					break;
			}
		break;
		default:
			echo '<div style="greyboxError">You can only Prompt or Commit - Nothing Else.</div>';
		break;
	}//switch


	/***************************/
	/******* PROMPT MODE *******/
	/***************************/
	function printCellForm($id)
	{
		global $DB;

		$cell = $DB->SingleQuery("SELECT `post_cell`.`id`, `post_cell`.`drawing_id`, `post_drawing_main`.`type`, `content`, `href`, `legend`, 
				`course_subject`, `course_number`, `course_title`, `course_credits`, 
				`row_id`, `post_col`.`num` AS `col_num`, `post_col`.`title` AS `col_name`
			FROM `post_cell`
			LEFT JOIN `post_col` ON `post_cell`.`col_id` = `post_col`.`id`
			LEFT JOIN `post_drawings` ON (`post_cell`.`drawing_id` = `post_drawings`.`id`)
			LEFT JOIN `post_drawing_main` ON (`post_drawings`.`parent_id` = `post_drawing_main`.`id`)
			WHERE `post_cell`.`id` = '" . intval($id) . "'");

		// Draw the High School form
		ob_start();
		if($cell['type'] == 'HS')
		{
			echo getHSFormHTML($cell);
?>
		<script language="JavaScript" type="text/javascript">
			<?= tinyMCEInitScript() ?>
			
			$("#postFormContent").focus();

			$(".postGreyboxContent input").keydown(function(e) {
				if( e.keyCode == 13 ) $("#postFormSave").click();
			});

			$("#postFormSave").click(function(){
				var legendData = '';
				$.each($(".post_legend_input"), function() {
					if( $(this).val() == 1 ) {
						var id = $(this).attr("id").split("_")[2];
						legendData += id + "-";
					}
				});
				$.ajax({
					type: "POST",
					url: "/a/postserv.php?mode=commit&type=cell&id=<?=$id?>",
					data: { content: (tinyMCE.activeEditor.getContent()),
							legend: legendData
					},
					success: function(data){
						$("#post_cell_<?=$id?>").html(data);
						var bgSwap = $("#post_cell_<?=$id?>").children().css("background");
						$("#post_cell_<?=$id?>").parent().css({"background" : bgSwap});
						$("#post_cell_<?=$id?>").children().css({"background" : "none"});
						chGreybox.close();
					}
				});
			});
		</script>
<?php
		}//if (high school)
		elseif($cell['type'] == 'CC')
		{
			echo getCCFormHTML($cell);
?>
		<script language="JavaScript" type="text/javascript">
			<?= tinyMCEInitScript() ?>

			$("#postFormSubject").focus();

			$("#postFormSubject").keyup(function(){
				$(this).val($(this).val().toUpperCase());
			});

			$(".postGreyboxContent input").keydown(function(e) {
				if( e.keyCode == 13 ) $("#postFormSave").click();
			});

			if($("#postTopRadio").attr("checked"))
			{
				$("#postFormContent, #postFormURL").attr("disabled", "disabled");
				$("#ccDetailRow").css({background: "#FFECBF"});
			}
			else
			{
				$("#postFormSubject, #postFormNumber, #postFormTitle, #postFormCredits").attr("disabled", "disabled");
				$("#ccFreeRow").css({background: "#FFECBF"});
			}

			$("#postTopRadio").click(function(){
				$("#postFormSubject, #postFormNumber, #postFormTitle, #postFormCredits").attr("disabled", false).css({"background" : "#FFFFFF"});
				$("#postFormContent, #postFormURL").attr("disabled", "disabled");
				$("#ccDetailRow").css({background: "#FFECBF"});
				$("#ccFreeRow").css({background: "#FFFFFF"});
			});
			$("#postBottomRadio").click(function(){
				$("#postFormContent, #postFormURL").attr("disabled", false).css({"background" : "#FFFFFF"});
				$("#postFormSubject, #postFormNumber, #postFormTitle, #postFormCredits").attr("disabled", "disabled");
				$("#ccDetailRow").css({background: "#FFFFFF"});
				$("#ccFreeRow").css({background: "#FFECBF"});
			});

			$("#postFormSave").click(function(){
				if($("#postTopRadio").attr("checked"))
					$("#postFormContent, #postFormURL").val("");
				else
					$("#postFormSubject, #postFormNumber, #postFormTitle").val("");

				var legendData = ''
				$.each($(".post_legend_input"), function() {
					if( $(this).val() == 1 ) {
						var id = $(this).attr("id").split("_")[2];
						legendData += id + "-";
					}
				});

				$.ajax({
					type: "POST",
					url: "/a/postserv.php?mode=commit&type=cell&id=<?=$id?>",
					data: {	subject: $("#postFormSubject").val(),
							number: $("#postFormNumber").val(),
							title: ($("#postFormTitle").val()),
							content: (tinyMCE.activeEditor.getContent()),
							// content: ($("#postFormContent").val()),
							credits: ($("#postFormCredits").val()),
							href: $("#postFormURL").val(),
							legend: legendData
					},
					success: function(data){
						$("#post_cell_<?=$id?>").html(data);
						var bgSwap = $("#post_cell_<?=$id?>").children().css("background");
						$("#post_cell_<?=$id?>").parent().css({"background" : bgSwap});
						$("#post_cell_<?=$id?>").children().css({"background" : "none"});
						chGreybox.close();
					}
				});
			});
		</script>
<?php
		}//if (community college)
		else
		{
			echo 'This cell does not exist in the database';
		}

		echo ob_get_clean();
	}//end function printCellForm

	function tinyMCEInitScript()
	{
		ob_start();
?>
			tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				plugins : "spellchecker,style",
				theme_advanced_buttons1 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,link,unlink,|,code,spellchecker",
				theme_advanced_buttons2 : "",
				theme_advanced_buttons3 : "",
				theme_advanced_buttons4 : "",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : false,
				theme_advanced_advanced_resizing : false,
				spellchecker_languages : "+English=en",
				spellchecker_rpc_url : "/common/tinymce/plugins/spellchecker/rpc.php",
			});
<?php
		return ob_get_clean();
	}

	function printHeadForm($id)
	{
		global $DB;
		$cell = $DB->SingleQuery("SELECT `title` FROM `post_col` WHERE `id` = '" . intval($id) . "'");

		ob_start();
?>
		<form action="javascript:void(0);">
			<div style="font-weight: bold;">Header Title:</div>
			<input type="text" id="postFormTitle" style="width: 400px; border: 1px #AAA solid;" value="<?=$cell['title']?>" />
			<br /><br />
			<div style="text-align: right;">
				<input type="button" id="postFormSave" value="Save" style="padding: 3px; background: #E0E0E0; border: 1px #AAA solid; font-weight: bold;" />
			</div>
		</form>
		<script language="JavaScript" type="text/javascript">
			$("#postFormTitle").focus().bind("keydown", function(e){
				if(e.keyCode == 13){
					$("#postFormSave").click();
				}
			});
			$("#postFormSave").click(function(){
				$.ajax({
					type: "POST",
					url: "/a/postserv.php?mode=commit&type=head&id=<?=$id?>",
					data: "title=" + $("#postFormTitle").val(),
					success: function(data){
						$("#post_header_<?=$id?>").html(data);
						chGreybox.close();
					}
				});
			});
		</script>
<?php
		echo ob_get_clean();
	}//end function printHeadForm

	function printRowTitleForm($id)
	{
		global $DB;
		$cell = $DB->SingleQuery("SELECT `title`, `row_type` FROM `post_row` WHERE `id` = '" . intval($id) . "'");

		if($cell['title'])
			$title = $cell['title'];
		else
			$title = '';
 
		ob_start();
?>
		<form action="javascript:void(0);">
			<div style="font-weight: bold;">Row Title:</div>
			<input type="text" id="postFormTitle" style="width: 400px; border: 1px #AAA solid;" value="<?=$title?>" />
			<br /><br />
			<div style="text-align: right;">
				<input type="button" id="postFormSave" value="Save" style="padding: 3px; background: #E0E0E0; border: 1px #AAA solid; font-weight: bold;" />
			</div>
		</form>
		<script language="JavaScript" type="text/javascript">
			$("#postFormTitle").focus().bind("keydown", function(e){
				if(e.keyCode == 13){
					$("#postFormSave").click();
				}
			});
			$("#postFormSave").click(function(){
				$.ajax({
					type: "POST",
					url: "/a/postserv.php?mode=commit&type=rowTitle&id=<?=$id?>",
					data: "title=" + $("#postFormTitle").val(),
					success: function(data){
						$("#post_row_<?=$id?>").html(data);
						chGreybox.close();
					}
				});
			});
		</script>
<?php
		echo ob_get_clean();
	}//end function printRowTitleForm

	function printFooterForm( $id)
	{
		global $DB;

		$cell = $DB->SingleQuery("SELECT `footer_text`, `footer_link`
			FROM `post_drawings`
			WHERE `id` = '" . intval($id) . "'");

		// Draw the Footer form
		ob_start();

		echo getFooterHTML($cell);
?>
			<script language="JavaScript" type="text/javascript">		
		$(".postGreyboxContent input").keydown(function(e) {
				if( e.keyCode == 13 ) $("#postFormSave").click();
			});

			$("#postFormSave").click(function(){
				$.ajax({
					type: "POST",
					url: "/a/postserv.php?mode=commit&type=footer&id=<?=$id?>",
					data: {
						text: $("#postFormContent").val(),
						link: $("#postFormURL").val()
					},
					success: function(data){
						$("#post_footer_<?=$id?>").html(data);
						chGreybox.close();
					}
				});
			});
		</script>
<?php
	}//end function printFooterForm
	
	function printHeaderForm( $id)
	{
		global $DB;

		$cell = $DB->SingleQuery("SELECT `header_text`, `header_link`
			FROM `post_drawings`
			WHERE `id` = '" . intval($id) . "'");

		// Draw the Header form
		ob_start();

		echo getHeaderHTML($cell);
?>
		<script language="JavaScript" type="text/javascript">
			$(".postGreyboxContent input").keydown(function(e) {
				if( e.keyCode == 13 ) $("#postFormSave").click();
			});

			$("#postFormSave").click(function(){
				$.ajax({
					type: "POST",
					url: "/a/postserv.php?mode=commit&type=header&id=<?=$id?>",
					data: {text: $("#postFormContent").val(), link: $("#postFormURL").val()},
					success: function(data){
						$("#post_headers_<?=$id?>").html(data);
						chGreybox.close();
					}
				});
			});
		</script>
<?php
	}//end function printHeaderForm

	function printSidebarRightForm($id)
	{
		global $DB;

		$cell = $DB->SingleQuery("SELECT d.`sidebar_text_right`, m.`type`
			FROM `post_drawings` d
			JOIN `post_drawing_main` m ON m.id = d.parent_id
			WHERE d.`id` = '" . intval($id) . "'");

		$options = $DB->VerticalQuery('SELECT text FROM post_sidebar_options WHERE type="'.$cell['type'].'" ORDER BY text', 'text', 'text');
		$options = array_merge(array(''=>''), $options);

		// Draw the Footer form
		ob_start();
?>
		<form action="javascript:void(0);">
			<div style="font-weight: bold;">Degree Option:</div>
			<?= GenerateSelectBox($options, 'sidebar_text', $cell['sidebar_text_right']) ?>
			<br /><br />
			<div style="text-align: right;">
				<input type="button" id="postFormSave" value="Save" style="padding: 3px; background: #E0E0E0; border: 1px #AAA solid; font-weight: bold;" />
			</div>
		</form>

		<script language="JavaScript" type="text/javascript">
			$("#postFormSave").click(function(){
				$.ajax({
					type: "POST",
					url: "/a/postserv.php?mode=commit&type=sidebar_right&id=<?=$id?>",
					data: {text: $("#sidebar_text").val()},
					success: function(data){
						$("#postsidebarright_<?=$id?>").html(data);
						chGreybox.close();
					}
				});
			});
		</script>
<?php
	}//end function printSidebarRightForm

	/**************************/
	/****** COMMIT CODE *******/
	/**************************/
	function commitCell($id)
	{
		global $DB;

		// Decide if we are drawing a link or not
		$href = array_key_exists('href', $_POST) ? $_POST['href'] : '';
		$link = FALSE;
		if(isset($_POST['href']) && $_POST['href'] != '' && $_POST['href'] != 'undefined')
		{
			$link = TRUE;
			if(substr($href, 0, 7) != 'http://' && substr($href, 0, 8) != 'https://')
				$href = 'http://' . $_POST['href'];
		}
		if(array_key_exists('href',$_POST) && $_POST['href'] == 'undefined')
			$href = '';

		$subject = (isset($_POST['subject']))?$_POST['subject']:'';
		$number = (isset($_POST['number']))?$_POST['number']:'';
		$credits = (isset($_POST['credits']))?$_POST['credits']:'0';
		$title = (isset($_POST['title']))?$_POST['title']:'';

		// Update the database
		$DB->Update('post_cell', array(
			'legend'=>$_POST['legend'],
			'course_subject'=>$subject,
			'course_number'=>$number,
			'course_title'=>$title,
			'course_credits'=>$credits,
			'content'=>$_POST['content'],
			'href' => $href), intval($id));

		if($_POST['legend'])
			$background = 'url(/c/images/legend/' . trim($_POST['legend'],'-') . '.png) top left no-repeat;';
		else
			$background = 'none;';

		// Decide what we should draw back to the page
		if($subject != '' && $number != '')
		{
			echo '<span style="background: ' . $background . '">' . $subject . ' ' . $number . ($credits > 0 ? ' (' . $credits . ')' : '') . '<br />' . $title . '</span>';
		}
		else
		{
			echo ($link?'<a href="javascript:void(0);" style="background: ' . $background . '">':'<span style="background: ' . $background . '">') . $_POST['content'] . ($link?'</a>':'</span>');
		}
	}//end function commitCell
	
	/**
	 * Print the data for the cell as a json object.
	 * Used by the copy/paste feature
	 */
	function fetchCell($id)
	{
		global $DB;
		$data = $DB->SingleQuery('SELECT * FROM post_cell WHERE id='.$id);
		$return = array();
		$return['content'] = $data['content'];
		$return['href'] = $data['href'];
		$return['legend'] = $data['legend'];
		$return['course_subject'] = $data['course_subject'];
		$return['course_number'] = $data['course_number'];
		$return['course_title'] = $data['course_title'];
		$return['course_credits'] = $data['course_credits'];
		echo json_encode($return);
	}// end function fetchCell

	
	function commitHead($id)
	{
		global $DB;
		$DB->Update('post_col', array('title' => $_POST['title']), intval($id));
		echo $_POST['title'];
	}//end function commitHeader

	function commitRowTitle($id)
	{
		global $DB;
		$DB->Update('post_row', array('title' => $_POST['title']), intval($id));
		echo $_POST['title'];
	}

	function commitFooter($id)
	{
		global $DB;

		$href = $_POST['link'];
		$link = FALSE;
		if(isset($_POST['link']) && $_POST['link'] != '')
		{
			$link = TRUE;
			if(substr($href, 0, 7) != 'http://' && substr($href, 0, 8) != 'https://')
				$href = 'http://' . $_POST['link'];
		}

		$DB->Update('post_drawings', array('footer_text' => $_POST['text'], 'footer_link'=>$href), intval($id));
		echo ($link?'<a href="javascript:void(0);">':'') . $_POST['text'] . ($link?'</a>':'');
	}//end function commitFooter
	
	function commitHeader($id)
	{
		global $DB;

		$href = $_POST['link'];
		$link = FALSE;
		if(isset($_POST['link']) && $_POST['link'] != '')
		{
			$link = TRUE;
			if(substr($href, 0, 7) != 'http://' && substr($href, 0, 8) != 'https://')
				$href = 'http://' . $_POST['link'];
		}

		$DB->Update('post_drawings', array('header_text' => $_POST['text'], 'header_link'=>$href), intval($id));
		echo ($link?'<a href="javascript:void(0);">':'') . $_POST['text'] . ($link?'</a>':'');
	}//end function commitHeader

	function commitSwap($fromID, $toID)
	{
		global $DB;
		$rows = $DB->MultiQuery("SELECT `id`, `row_id`, `col_id` FROM `post_cell` WHERE `id` = '" . intval($fromID) . "' OR `id` = '" . intval($toID) . "'");

		$DB->Update('post_cell', array('row_id'=>$rows[0]['row_id'], 'col_id'=>$rows[0]['col_id']), $rows[1]['id']);
		$DB->Update('post_cell', array('row_id'=>$rows[1]['row_id'], 'col_id'=>$rows[1]['col_id']), $rows[0]['id']);
	}//end function commitSwap

	function commitLegend($id)
	{
		global $DB;
		$DB->Update('post_legend', array('text'=>$_POST['text']), $id);
		die($_POST['text']);
	}

	function commitSidebarRight($id)
	{
		global $DB;

		// updating text in a POST drawing
		$text = $_POST['text'];
		$DB->Update('post_drawings', array('sidebar_text_right' => $text), intval($id));
		$post = POSTChart::create($id);
		echo $post->verticalText($text);
	}//end function commitFooter

	function commitConfigSidebar()
	{
		global $DB;
		if( IsAdmin() )
		{
			// editing the list of options
			switch( $_POST['action'] )
			{
				case "add":
					$data = array('type'=>$_POST['post_type'], 'text'=>$_POST['text']);
					$data['id'] = $DB->Insert('post_sidebar_options', $data);
					echo json_encode($data);
					break;
				case "delete":
					$data = array('id'=>$_POST['id']);
					$DB->Query("DELETE FROM post_sidebar_options WHERE id=".$_POST['id']);
					echo json_encode($data);
					break;
			}
		}		
	}
	

	/*****************************/
	/******* FORM PRINTERS *******/
	/*****************************/

	function getHSFormHTML(&$cell = NULL)
	{
		global $DB;

		ob_start();

		$drawing = $DB->SingleQuery('SELECT * FROM post_drawings WHERE id='.$cell['drawing_id']);
		$post = POSTChart::create($cell['drawing_id']);
		$row = $DB->SingleQuery('SELECT * FROM post_row WHERE id='.$cell['row_id']);
		$rowName = str_replace('<br />', ' ', $post->rowNameFromData($row));

		echo '<h3>' .
			( $rowName ? (is_numeric($rowName)?'Grade ' . $rowName:$rowName) . ', ' : '') .
			($cell['col_name']) .
			'</h3>';

?>
		<br />
		<div class="postEditHS">
		<form action="javascript:void(0);">
			<div style="font-weight: bold;">Course Content:</div>
			<textarea id="postFormContent" rows="5" cols="40" style="width: 330px; border: 1px #AAA solid;" class="editorWindow"><?=$cell['content']?></textarea>
			<div class="tip"><p>TIP: To single space, hold down <b>Shift + Enter/Return</b> key for a new single spaced line of content.</p></div>
			<br />
<?php
		$legend = explode('-', $cell['legend']);
		getLegendHTML($legend);
?>
			<div style="text-align: right;">
				<input type="button" id="postFormSave" value="Save" style="padding: 3px; background: #E0E0E0; border: 1px #AAA solid; font-weight: bold;" />
			</div>
		</form>
		</div>
<?php
		return ob_get_clean();
	}//end function printHSFormHTML

	function getCCFormHTML(&$cell = NULL)
	{
		global $DB;
		
		ob_start();

		$drawing = $DB->SingleQuery('SELECT * FROM post_drawings WHERE id='.$cell['drawing_id']);
		$post = POSTChart::create($cell['drawing_id']);
		$row = $DB->SingleQuery('SELECT * FROM post_row WHERE id='.$cell['row_id']);

		echo '<h3>' .
			str_replace('<br />', ' ', $post->rowNameFromData($row)) .
			', Column ' . ($cell['col_num']+1) .
			'</h3>';
?>
		<br />
		<div class="postEditCC">
		<form action="javascript:void(0);">
			<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; height: 100%">
				<tr id="ccDetailRow">
					<td valign="top">
						<input type="radio" class="radio" name="postModeSelector" id="postTopRadio"<?=(($cell['course_subject'] != '' || !$cell['content'])?' checked="checked"':'')?> />
					</td>
					<td id="postTopHalf" style="padding-left: 20px; padding-bottom: 5px; padding-top: 5px;" valign="top">
						<div>
							<div style="float: left; width: 150px; height: 20px; font-weight: bold;">Course Subject:</div>
							<div style="float: left; height: 20px;">
								<input id="postFormSubject" maxlength="4" value="<?=$cell['course_subject']?>" style="width: 50px;" /> (e.g. WR)
							</div>
						</div>
						<div>
							<div style="clear: both; float: left; width: 150px; height: 20px; font-weight: bold;">Course Number:</div>
							<div style="float: left; height: 20px;">
								<input id="postFormNumber" maxlength="7" value="<?=$cell['course_number']?>" style="width: 50px;" /> (e.g. 200)
							</div>
						</div>
						<div>
							<div style="clear: both; float: left; width: 150px; height: 20px; font-weight: bold;">Course Credits:</div>
							<div style="float: left; height: 20px;">
								<input id="postFormCredits" maxlength="2" value="<?=$cell['course_credits']?>" style="width: 50px;" /> (e.g. 4)
							</div>
						</div>
						<div style="clear: both; font-weight: bold;">Course Title:</div>
							<input id="postFormTitle" maxlength="255" style="width: 340px;" value="<?=$cell['course_title']?>" />
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<div style="clear: both; height: 10px;"></div>
						<div style="width: 100%; font-weight: bold; text-align: center;">&mdash;&mdash;&mdash;&mdash;&mdash; OR &mdash;&mdash;&mdash;&mdash;&mdash;</div>
						<div style="clear: both; height: 10px;"></div>
					</td>
				</tr>
				<tr id="ccFreeRow">
					<td valign="top">
						<input type="radio" class="radio" name="postModeSelector" id="postBottomRadio"<?=(($cell['course_subject'] == '' && $cell['content'])?' checked="checked"':'')?> />
					</td>
					<td id="postBottomHalf" valign="top" style="padding-left: 20px; padding-bottom: 5px; padding-top: 5px;">
						<div style="font-weight: bold;">Course Content:</div>
						<textarea id="postFormContent" rows="5" cols="40" style="width: 330px; border: 1px #AAA solid;"><?=$cell['content']?></textarea>
						<div class="tip"><p>TIP: To single space, hold down <b>Shift + Enter/Return</b> key for a new single spaced line of content.</p></div>
					</td>
				</tr>
			</table>
<?php
		$legend = explode("-",$cell['legend']);
		getLegendHTML($legend);
?>
			<div style="text-align: right;">
				<input type="button" id="postFormSave" value="Save" style="padding: 3px; background: #E0E0E0; border: 1px #AAA solid; font-weight: bold;" />
			</div>
		</form>
		</div>
<?php
		return ob_get_clean();	
	}//end function printCCFormHTML

	function getFooterHTML(&$footer = NULL)
	{
		if(!$footer)
			$footer = array('footer_text'=>'', 'footer_link'=>'');

		ob_start();
?>
		<form action="javascript:void(0);">
			<div style="font-weight: bold;">Footer Content:</div>
			<input type="text" id="postFormContent" style="width: 400px; border: 1px #AAA solid;" value="<?=$footer['footer_text']?>" />
			<br /><br />
			<div style="font-weight: bold;">Link this Content: <span style="color: #777777; font-size: 10px; font-weight: normal;">(Optional)</span></div>
			URL: <input type="text" id="postFormURL" style="width: 300px; border: 1px #AAA solid;" value="<?=$footer['footer_link']?>" />
			<br /><br />
			<div style="text-align: right;">
				<input type="button" id="postFormSave" value="Save" style="padding: 3px; background: #E0E0E0; border: 1px #AAA solid; font-weight: bold;" />
			</div>
		</form>
<?php
		return ob_get_clean();
	}//end function getFooterHTML
	
		function getHeaderHTML(&$header = NULL)
	{
		if(!$header)
			$header = array('header_text'=>'', 'header_link'=>'');

		ob_start();
?>
		<form action="javascript:void(0);">
			<div style="font-weight: bold;">Header Content:</div>
			<input type="text" id="postFormContent" style="width: 400px; border: 1px #AAA solid;" value="<?=$header['header_text']?>" />
			<br /><br />
			<div style="font-weight: bold;">Link this Content: <span style="color: #777777; font-size: 10px; font-weight: normal;">(Optional)</span></div>
			URL: <input type="text" id="postFormURL" style="width: 300px; border: 1px #AAA solid;" value="<?=$header['header_link']?>" />
			<br /><br />
			<div style="text-align: right;">
				<input type="button" id="postFormSave" value="Save" style="padding: 3px; background: #E0E0E0; border: 1px #AAA solid; font-weight: bold;" />
			</div>
		</form>
<?php
		return ob_get_clean();
	}//end function getFooterHTML

	/**
	 * This takes a legend array and prints the HTML/javascript for the Legend
	 * This assumes that a <form> tag exists.. although with AJAX it doesn't really matter
	 */
	function getLegendHTML($legend)
	{
		global $DB;

		echo '<div><span style="font-weight: bold;">Legend Symbols:</span> (select one or more)</div>', "\n";
		$legendList = $DB->MultiQuery("SELECT * FROM `post_legend` WHERE `text` != '' ORDER BY `id` ASC");
		foreach($legendList as $item)
		{
			$checked = (in_array($item['id'], $legend)) ? 'c' : 'b';
?>
<img style="margin-right: 20px; cursor: pointer;" class="post_legend_icon" id="legend_icon_<?=$item['id']?>" src="/c/images/legend/<?=$checked . $item['id']?>.png" alt="<?=$item['text']?>" />
<input type="text" class="post_legend_input" id="legend_input_<?=$item['id']?>" style="display: none;" value="<?=in_array($item['id'], $legend)?1:0?>" />
<?php
		}
?>
		<div style="clear: both; margin-top: 5px;" id="post_legend_help">&nbsp;</div>
		<script language="JavaScript" type="text/javascript">

	var legendText = {<?php
	$array = '';
	foreach($legendList as $item)
		$array .= '"' . $item['id'] . '" : "' . str_replace('"', '\"', $item['text']) . '", ';
	echo substr($array, 0 ,-2) . '};', "\n";
	?>

	$(".post_legend_icon").hover(function(){
		var id = $(this).attr("id").split("_")[2];
		$("#post_legend_help").html(legendText[id]);
	}, function() {
		$("#post_legend_help").html("&nbsp;");
	});

	$(".post_legend_icon").click(function(){
		var id = $(this).attr("id").split("_")[2];

		var newValue = ($("#legend_input_" + id).val() == "0") ? "1" : "0";
		$("#legend_input_" + id).val(newValue);
		if(newValue == "1")
			$("#legend_icon_" + id).attr("src", "/c/images/legend/c" + id + ".png");
		else
			$("#legend_icon_" + id).attr("src", "/c/images/legend/b" + id + ".png");
	});
	


		</script>
<?php
	}//end function getLegendHTML
?>
