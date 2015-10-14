<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="drawing_form" class="new_drawing">
<table>

<?php if($SITE->hasFeature('oregon_skillset')): ?>
    <tr>
        <th width="115"><?=l('skillset name')?></th>
        <td><div id="skillset"><?php
                echo GenerateSelectBoxDB('oregon_skillsets', 'skillset_id', 'id', 'title', 'title', '', array('0'=>''));
                ?></div><div id="skillsetConf" style="color:#393; font-weight: bold"></div></td>
    </tr>
<?php endif; ?>

<?php if( $school['organization_type'] != 'Other'): ?>
    <tr>
        <th width="115"><?=l('program name label')?></th>
        <td><div id="program"><?php
                echo GenerateSelectBoxDB('programs', 'program_id', 'id', 'title', 'title', '', array('0'=>'Not Listed'));
                ?></div></td>
    </tr>    
<?php endif; ?>

<tr>
    <th valign="bottom"><div id="drawing_title_label">Custom Program Name (not recommended)</div></th>
    <td>
        <input type="text" id="drawing_title" name="name" size="40" value="<?= $drawing['name'] ?>">
    	<div id="checkNameResponse" class="error"></div>
    </td>
</tr>

<tr>
	<th width="115">Organization</th>
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

<tr>
	<th width="115"><?=l('degreetype name label')?></th>
    <td><div id="degreetype"><?php
    	$drawingType = strtoupper(Request('type'));
    	echo GenerateSelectBoxDB('post_sidebar_options', 'degree_type', 'text', 'text', 'text', '', array(''=>''), "type='$drawingType'");
    	?></div>
    </td>
</tr>
<tr>
	<th width="115">&nbsp;</th>
	<td><input type="button" class="submit" value="Create" id="submitButton" onclick="submitform()"></td>
</tr>
</table>
<input type="hidden" name="id" value="" />
<input type="hidden" name="type" value="<?= Request('type') ?>" />
</form>
