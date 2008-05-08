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
	$content['sort_index'] = $_REQUEST['sort_index'];

	if( Request('id') ) {
		$DB->Update('news',$content,intval($_REQUEST['id']));
	} else {
		$content['date'] = $DB->SQLDate();
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
			SELECT news.id, news.date, news.text, news.caption, news.active,
				CONCAT(users.first_name,' ',users.last_name) AS name, sort_index
			FROM news, users
			WHERE user_id=users.id
			ORDER BY sort_index");
		?>
		<table width="100%">
		<tr>
			<th width="35"><a href="<?= $_SERVER['PHP_SELF'] ?>?id"><?= SilkIcon('add.png') ?></a></th>
			<th width="120">Date</th>
			<th>Caption</th>
			<th>Content</th>
			<th width="160">Posted By</th>
			<th width="60">Sort</th>
		</tr>
		<?php
		foreach( $news as $n ) {
			echo '<tr>';
			echo '<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$n['id'].'" class="edit">edit</a></td>';
			echo '<td>'.$DB->Date("Y-m-d H:i",$n['date']).'</td>';
			echo '<td>'.$n['caption'].'</td>';
			echo '<td>'.TrimString($n['text'],45).'</td>';
			echo '<td>'.$n['name'].'</td>';
			echo '<td>'.$n['sort_index'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
		PrintFooter();
	}
}


function ShowForm($id) {
global $DB;

	$news = $DB->LoadRecord('news',$id);

	?>
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">

	<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a>

	<p>
	<table>
	<tr>
		<th>Caption</th>
		<td><input type="text" name="caption" style="width: 500px" value="<?= $news['caption'] ?>"></td>
	</tr>
	<tr>
		<th valign="top">Text</th>
		<td><textarea style="width: 500px; height: 300px;" name="text"><?= $news['text'] ?></textarea><br>(wiki syntax allowed)</td>
	</tr>
	<tr>
		<th>Sort Index</th>
		<td><input type="text" name="sort_index" style="width: 50px" value="<?= $news['sort_index'] ?>"></td>
	</tr>

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