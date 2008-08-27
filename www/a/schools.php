<?php
chdir("..");
include("inc.php");
include("states.inc.php");

ModuleInit('schools');



if( KeyInRequest('id') ) {

	if( PostRequest() ) {

		if( Request('delete') == 'delete' ) {

			$DB->Query("DELETE FROM color_schemes WHERE school_id=".intval($_REQUEST['id']));
			$DB->Query("DELETE FROM schools WHERE id=".intval($_REQUEST['id']));

		} else {

			$content = Array( 'school_name' => $_REQUEST['school_name'],
							  'school_abbr' => $_REQUEST['school_abbr'],
							  'school_website' => $_REQUEST['school_website'],
							  'school_addr' => $_REQUEST['school_addr'],
							  'school_city' => $_REQUEST['school_city'],
							  'school_state' => $_REQUEST['school_state'],
							  'school_zip' => $_REQUEST['school_zip'],
							);

			$content['school_website'] = str_replace('http://','',$content['school_website']);
			if( substr($content['school_website'],-1) == '/' ) {
				$content['school_website'] = substr($content['school_website'],0,-1);
			}

			if( Request('id') ) {
				$DB->Update('schools',$content,$_REQUEST['id']);
				$school_id = $_REQUEST['id'];
			} else {
				$school_id = $DB->Insert('schools',$content);
			}

		}

		header("Location: ".$_SERVER['PHP_SELF']);

	} else {

		PrintHeader();
		ShowSchoolForm($_REQUEST['id']);
		PrintFooter();

	}

} else {

	PrintHeader();

	echo '<table>';

	$schools = $DB->MultiQuery("SELECT * FROM schools ORDER BY school_name");

	echo '<tr>';
		echo '<th width="30"><a href="'.$_SERVER['PHP_SELF'].'?id" class="edit"><img src="/common/silk/add.png" width="16" height="16"></a></th>';
		echo '<th width="40">Abbr.</th>';
		echo '<th width="270">Organization Name</th>';
		echo '<th width="50">Users</th>';
		echo '<th width="70">Drawings</th>';
		echo '<th>Colors</th>';
	echo '</tr>';

	foreach( $schools as $num=>$s ) {

		echo '<tr class="row'.($num%2).'">';
			echo '<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$s['id'].'" class="edit">edit</a></td>';
			echo '<td>'.$s['school_abbr'].'</td>';
			echo '<td>'.$s['school_name'].'</td>';

			$users = $DB->SingleQuery("SELECT COUNT(*) AS num FROM users WHERE school_id=".$s['id']." AND user_active=1");
			echo '<td>'.($users['num']==0?'&nbsp;':$users['num']).'</td>';

			$drawings = $DB->SingleQuery("SELECT COUNT(*) AS num FROM drawing_main WHERE school_id=".$s['id']."");
			echo '<td>'.($drawings['num']==0?'&nbsp;':$drawings['num']).'</td>';

			echo '<td>';

			$str = '';
			$colors = $DB->MultiQuery("SELECT * FROM color_schemes WHERE school_id=".$s['id']);
			foreach( $colors as $c ) {
				$str .= '<div title="#'.$c['hex'].'" style="background-color:#'.$c['hex'].'" class="school_color_box_mini"></div>';
			}
			$str .= '<div title="#333333" style="background-color:#333333" class="school_color_box_mini"></div>';
			echo $str;

			echo '</td>';
		echo '</tr>';

	}

	echo '</table>';

	PrintFooter();

}



function ShowSchoolForm($id="") {
global $DB, $STATES;

	$school = $DB->LoadRecord('schools',$id);

?>
<a href="<?= $_SERVER['PHP_SELF'] ?>" class="edit">back</a><br>
<br>

<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
<table width="600">

	<tr>
		<td colspan="3"><hr></td>
	</tr>
	<tr>
		<td valign="top">Abbreviation:</td>
		<td colspan="2" valign="top"><input type="text" name="school_abbr" value="<?= $school['school_abbr'] ?>" size="10"></td>
	</tr>
	<tr>
		<td width="100">Organization Name:</td>
		<td colspan="2"><input type="text" name="school_name" value="<?= $school['school_name'] ?>" size="50"></td>
	</tr>
	<tr>
		<td width="100">Website:</td>
		<td colspan="2"><input type="text" name="school_website" id="school_website" value="<?= $school['school_website'] ?>" size="50"></td>
	</tr>
	<tr>
		<td width="100">Address:</td>
		<td colspan="2"><input type="text" name="school_addr" id="school_addr" value="<?= $school['school_addr'] ?>" size="50"></td>
	</tr>
	<tr>
		<td width="100">City:</td>
		<td colspan="2"><input type="text" name="school_city" id="school_city" value="<?= $school['school_city'] ?>" size="20"></td>
	</tr>
	<tr>
		<td width="100">State:</td>
		<td colspan="2"><?php
			echo GenerateSelectBox($STATES,'school_state','OR');
		?></td>
	</tr>
	<tr>
		<td width="100">Zip Code:</td>
		<td colspan="2"><input type="text" name="school_zip" id="school_zip" value="<?= $school['school_zip'] ?>" size="10"></td>
	</tr>

	<tr>
		<td colspan="3"><hr></td>
	</tr>

	<tr>
		<td>&nbsp;</td>
		<td>
			<input type="submit" value="Submit" class="submit">
			</td>
		<td align="right">
			<?php if( $id != "" ) { ?>
				Delete: <select name="delete"><option value="">-------</option><option value="delete">Delete</option></select>
			<?php } else { ?>
				&nbsp;
			<?php } ?>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?= $id ?>">
</form>
<?php

}


?>