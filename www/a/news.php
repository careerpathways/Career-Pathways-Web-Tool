<?php
chdir("..");
include("inc.php");


ModuleInit('news');


if( PostRequest() ) {

	if( Request('delete') == 'delete' ) {
		$DB->Query("DELETE FROM news WHERE id=".intval($_REQUEST['id']));
		header("Location: ".$_SERVER['PHP_SELF']);
		die();
	}

	$content = array();
	$content['caption'] = $_REQUEST['caption'];
	$content['text'] = $_REQUEST['text'];
	$content['category'] = $_REQUEST['category'];
	$content['date'] = (array_key_exists('date',$_REQUEST)?$DB->SQLDate(strtotime($_REQUEST['date'])):$DB->SQLDate());
	$content['sort_index'] = (array_key_exists('sort_index',$_REQUEST)?$_REQUEST['sort_index']:0);

	if( Request('id') ) {
		$DB->Update('news',$content,intval($_REQUEST['id']));
	} else {
		$content['user_id'] = $_SESSION['user_id'];
		$DB->Insert('news',$content);
	}

	header("Location: ".$_SERVER['PHP_SELF']);

} else {

	if( KeyInRequest('id') ) {

		PrintHeader();
		ShowForm(Request('id'));
		PrintFooter();

	} else {

		PrintHeader();
		$news = $DB->MultiQuery("
			SELECT news.id, news.date, news.category, news.text, news.caption, news.active,
				CONCAT(users.first_name,' ',users.last_name) AS name, sort_index
			FROM news, users
			WHERE user_id=users.id
			ORDER BY category, sort_index, date DESC");
		?>
		<table width="100%">
		<tr>
			<th width="35"><a href="<?= $_SERVER['PHP_SELF'] ?>?id"><?= SilkIcon('add.png') ?></a></th>
			<th width="100">Category</th>
			<th>Caption</th>
			<th width="140">Posted By</th>
			<th width="90">Sort</th>
		</tr>
		<?php
		$categories = $TEMPLATE->resource_categories();
		foreach( $news as $i=>$n ) {
			echo '<tr class="row'.($i%2).'">';
			echo '<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$n['id'].'" class="edit">edit</a></td>';
			echo '<td>'.$categories[$n['category']].'</td>';
			echo '<td>'.$n['caption'].'</td>';
			echo '<td>'.$n['name'].'</td>';
			echo '<td>'.($n['category']=='release_info'?$DB->Date("Y-m-d",$n['date']):$n['sort_index']).'</td>';
			echo '</tr>';
		}
		echo '</table>';
		?>
		<br>
		<p>Note: All categories sort their entries by the "Sort" value (descending) except for the "Release Info" category. Entries in this category are sorted reverse chronologically.</p>
		
		<div style="float: left; margin-right: 30px;">
		<b>Dashboard Page</b> (logged in users & guests)<br>
		<table class="border" border="1">
		<tr><td>First entry from "Welcome" category<br><br>If there are pending user requests that this user can approve, they appear here<br>&nbsp;</td><td>Quick Links</td></tr>
		<tr><td colspan="2">Newest entry from "Release Info" category</td></tr>
		<tr><td colspan="2">All entries from "Dashboard" category, sorted by index<br><br><br></td></tr>
		</table>
		</div>

		<b>Dashboard Page</b> (logged out view)<br>
		<table class="border" border="1">
		<tr><td>First entry from "Welcome" category</td><td>Login form</td></tr>
		</table>

		<br><br><br><br><br><br><br><br><br>
		
		<b>Formatting Help</b>
		<br>
		<em>Resource pages are formatted with wiki syntax: </em> <a href="/a/formatting.php">click here for tips and techniques</a> 

		<?php
		PrintFooter();
	}
}


function ShowForm($id) {
global $DB, $TEMPLATE;

	$news = $DB->LoadRecord('news',$id);
	if( $id == '' ) {
		$news['date'] = date('Y-m-d');
		$news['category'] = '';
	}	
	
	?>
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">

	<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

	<p>
	<table>
	<tr>
		<th>Category</th>
		<td><?= GenerateSelectBox($TEMPLATE->resource_categories(),'category',$news['category']) ?></td>
	</tr>
	<tr>
		<th>Caption</th>
		<td><input type="text" name="caption" style="width: 500px" value="<?= $news['caption'] ?>"></td>
	</tr>
	<tr>
		<th valign="top">Text</th>
		<td><textarea style="width: 500px; height: 300px;" name="text"><?= $news['text'] ?></textarea><br>(wiki syntax allowed)</td>
	</tr>
	<?php if( $news['category'] == 'release_info' || $news['category'] == '' ) { ?>
	<tr>
		<th>Date</th>
		<td><input type="text" name="date" style="width: 140px" value="<?= $news['date'] ?>"></td>
	</tr>
	<?php } if( $news['category'] != 'release_info' || $news['category'] == '' ) { ?>
	<tr>
		<th>Sort Index</th>
		<td><input type="text" name="sort_index" style="width: 50px" value="<?= $news['sort_index'] ?>"></td>
	</tr>
	<?php } ?>

	<tr>
		<th>&nbsp;</th>
		<td><br><input type="submit" class="submit" value="Submit" id="submitButton"></td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td align="right">
			Delete: <select name="delete">
			<option value="---">---</option>
			<option value="delete">Delete</option>
			</select>
		</td>
	</tr>

	</table>

	<input type="hidden" name="id" value="<?= $news['id'] ?>">

	</form>

	<?php
}


?>