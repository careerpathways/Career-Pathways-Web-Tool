<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="drawing_form">
<table>
<tr>
	<th valign="bottom">Occupation/Program</th>
	<td>
		<input type="text" id="drawing_title" name="name" size="80" value="<?= $drawing['name'] ?>" onblur="checkName(this)">
		<div id="checkNameResponse" class="error"></div>
	</td>
</tr>
<tr>
	<th width="80">Organization</th>
	<td>
	<?php

		$user_school = $DB->SingleQuery('SELECT * FROM schools WHERE id = ' . $school_id);

		if( Request('type') == 'cc' )
		{
			if(IsAdmin()) {
				$these_schools = $DB->VerticalQuery('SELECT * FROM schools WHERE organization_type!="HS" ORDER BY school_name', 'school_name', 'id');
			} else {
				if($user_school['organization_type'] == 'Other')
					$these_schools = GetAffiliatedSchools('CC');
	
				$these_schools[$school_id] = $user_school['school_name'];
			}
		}
		else
		{
			$these_schools = GetAffiliatedSchools();

			// Add the user's school if their school is the same type as the new drawing
			if( strtolower(Request('type')) == strtolower($user_school['organization_type']) )
				$these_schools[$school_id] = $user_school['school_name'];
				
		}

		// REPLACED THIS CODE BELOW --> if(count($these_schools) == 1)
		//JGD: I don't know how this happens, but I ran into a case where these_schools only had one entry, but it didn't match the school id.
		//JGD: So I show the selection box if that is the case. Not sure if that's the perfect solution...
		//logmsg( "school_id: $school_id\n" );
		//logmsg( "these_schools: ".varDumpString($these_schools)."\n" );
		if( (count($these_schools) == 1) && (isset($these_schools[$school_id])) )
		{
			echo '<b>'.$these_schools[$school_id].'</b>';
		}
		else
		{
			echo GenerateSelectBox($these_schools, 'school_id', $_SESSION['school_id']);
		}

	?>
	</td>
</tr>
<?php
if($SITE->hasFeature('oregon_skillset')){
?>
<tr>
	<th><?=l('skillset name')?></th>
	<td valign="top"><span id="skillset"><?php
		echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', '', array(''=>''));
	?></span>(optional)</td>
</tr>
<?php
}
?>
<tr>
	<td>&nbsp;</td>
	<td><input type="button" class="submit" value="Create" id="submitButton" onclick="submitform()"></td>
</tr>
</table>
<input type="hidden" name="id" value="" />
<input type="hidden" name="type" value="<?= Request('type') ?>" />
</form>
