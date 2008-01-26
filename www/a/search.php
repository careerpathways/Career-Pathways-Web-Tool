<?php
chdir("..");
include("inc.php");

ModuleInit('search');

PrintHeader();

?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
	<input type="text" size="20" name="search" value="<?= Request('search') ?>">
	<input type="submit" class="submit" value="Search">
</form>
<br>
<?php


if( Request('search') ) {
	$results = $DB->MultiQuery("
		SELECT DISTINCT(d.id), s.school_name, m.name, m.code, d.*
		FROM objects AS o, drawings AS d, drawing_main AS m, schools AS s
		WHERE o.drawing_id=d.id
			AND d.parent_id=m.id
			AND m.school_id=s.id
			AND (INSTR(content,'".$DB->Safe(Request('search'))."') OR INSTR(m.name,'".$DB->Safe(Request('search'))."') )");

	echo '<table width="100%">';
	echo '<tr>';
		echo '<th width="40">&nbsp;</th>';
		echo '<th>School</th>';
		echo '<th width="300">Title</th>';
		echo '<th>Last Modified</th>';
		echo '<th>Created</th>';
	echo '</tr>';

	foreach( $results as $dr ) {
		echo '<tr>';

			echo '<td><a href="drawings.php?view&drawing_id='.$dr['id'].'" class="edit">view</a></td>';
			echo '<td>'.$dr['school_name'].'</td>';
			echo '<td>';
				echo $dr['name'].': ';
				echo 'Version '.$dr['version_num'];
				echo ($dr['note']==""?"":' ('.$dr['note'].')');
			echo '</td>';
			$created = ($dr['created_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$dr['created_by']));
			$modified = ($dr['last_modified_by']==''?array('name'=>''):$DB->SingleQuery("SELECT CONCAT(first_name,' ',last_name) AS name FROM users WHERE id=".$dr['last_modified_by']));
			echo '<td>'.($dr['last_modified']==''?'':$DB->Date("m/d/Y g:ia",$dr['last_modified'])).' '.$modified['name'].'</td>';
			echo '<td>'.($dr['date_created']==''?'':$DB->Date("m/d/Y g:ia",$dr['date_created'])).' '.$created['name'].'</td>';

		echo '</tr>';
	}

	echo '</table>';
}



PrintFooter();

?>