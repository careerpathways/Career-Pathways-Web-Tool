<?php
chdir("..");
include("inc.php");

ModuleInit('post_drawings');


if( Request('mode') )
	$mode = Request('mode');
else
	$mode = 'pathways';

if( $mode == 'pathways' )
{
	$main_table = 'drawing_main';
	$version_table = 'drawings';
}
else
{
	$main_table = 'post_drawing_main';
	$version_table = 'post_drawings';
}

if( KeyInRequest('id') ) {

	$drawing = $DB->SingleQuery("SELECT *
			FROM $version_table AS v, $main_table AS m
			WHERE v.parent_id=m.id
			AND m.id=".intval(Request('id')));
	if( !(Request('id') == "" || is_array($drawing) && (IsAdmin() || $drawing['school_id'] == $_SESSION['school_id'])) ) {
			// permissions error
//			die();
	}

	if( Request('action') == 'olmis' )
	{
		if( Request('mode') == 'find' )
		{
			$drawings = $DB->MultiQuery('SELECT * FROM drawings WHERE parent_id='.intval(Request('id')));
			$soc = array();
			foreach( $drawings as $d )
			{
				$content = $DB->MultiQuery('SELECT content
					FROM objects
					WHERE drawing_id = ' . $d['id'] . '
					AND (content LIKE "%http://%qualityinfo.org%" OR content LIKE "%http://%olmis.emp.state.or.us%")');
				foreach( $content as $c )
				{
					$soc = array_merge($soc, SearchForOLMISLinks($c['content']));
				}
			}
			$soc = array_unique($soc);

			foreach( $soc as $k=>$s )
			{
				$check = $DB->SingleQuery('SELECT COUNT(*) AS num FROM olmis_links WHERE drawing_id='.intval(Request('id')).' AND olmis_id='.$s);
				if( $check['num'] > 0 )
					unset($soc[$k]);
			}

			$checkboxes = ShowOlmisCheckboxes($soc);
			if( $checkboxes == '' )
				$html = '<div style="">No OLMIS links were found in this drawing</div>';
			else
			{	
				$html = '<div style="margin-top:15px;font-weight: bold;">Add this roadmap to one or more occupational reports</div>';
				$html .= $checkboxes;
			}		

			echo '('.json_encode(array('olmis'=>$html)).')';
		}

		if( Request('mode') == 'add' )
		{
			$soc = SearchForOLMISLinks(Request('content'));
			
			foreach( $soc as $s )
			{
				$check = $DB->SingleQuery('SELECT COUNT(1) AS num FROM olmis_links WHERE drawing_id='.Request('id').' AND olmis_id="'.$s.'"');
				if( $check['num'] == 0 )
				{
					$data = array();
					$data['drawing_id'] = Request('id');
					$data['olmis_id'] = $s;
					$data['enabled'] = 1;
					$DB->Insert('olmis_links', $data);
				}
			}

			$html = ShowOlmisCheckboxes(Request('id'));
		
			echo '('.json_encode(array('olmis'=>$html)).')';
		}
		
		if( Request('mode') == 'enable' )
		{
			$check = $DB->SingleQuery('SELECT COUNT(1) AS num FROM olmis_links WHERE drawing_id='.intval(Request('id')).' AND olmis_id="'.intval(Request('code')).'"');
			if( $check['num'] == 0 )
				$DB->Query('INSERT INTO olmis_links (drawing_id, olmis_id) VALUES ('.intval(Request('id')).',"'.intval(Request('code')).'")');
		}

		if( Request('mode') == 'disable' )
		{
			$DB->Query('DELETE FROM olmis_links WHERE drawing_id = '.intval(Request('id')).' AND olmis_id = "'.intval(Request('code')).'"');
		}
	
		die();
	}

	if( Request('action') == 'skillset' )
	{
		if( KeyInRequest('skillset_id') )
		{
			if( intval(Request('id') != 0) )
			{
				$DB->Update($main_table, array(
					'skillset_id'=>intval(Request('skillset_id')),
					'program_id'=>0
				), Request('id'));
			}

			if( Request('skillset_id') )
				$programs_ = $DB->MultiQuery('SELECT * FROM programs WHERE skillset_id='.intval(Request('skillset_id')).' ORDER BY title');
			else
				$programs_ = $DB->MultiQuery('SELECT * FROM programs ORDER BY title');
			
			$programs = array(array('id'=>'0', 'title'=>'Not Listed'));
			foreach( $programs_ as $p )
			{
				$programs[] = array('id'=>$p['id'], 'title'=>$p['title']);
			}
			
			echo json_encode($programs);
		}
		else
		{
			if( Request('program_id') )
			{
				$skillset = $DB->SingleQuery('SELECT skillset_id FROM programs WHERE id='.intval(Request('program_id')));
			}
			else
			{
				$skillset = array('skillset_id'=>0);
			}

			$drawings = '';
			if( intval(Request('program_id')) != 0 )
			{
				$drawings_ = $DB->MultiQuery('SELECT `drawing_main`.`id`, IF(`name` != "", `name`, `title`) AS `name`
	                FROM `drawing_main`
	                    LEFT JOIN `drawings` ON (`drawing_main`.`id` = `drawings`.`parent_id`)
	                    LEFT JOIN `programs` ON (`drawing_main`.`program_id` = `programs`.`id`)
	                WHERE `program_id` = ' . intval(Request('program_id')) . ' AND `published` = "1"
	                	AND `school_id` = ' . intval(Request('school_id')) . '
	                ORDER BY `name` ASC');
				if( count($drawings_) > 0 )
				{
					$drawings .= '<b>The following drawings already exist at your school for this program name:</b><br />';
					foreach( $drawings_ as $d )
					{
						$drawings .= '<a href="/a/drawings.php?action=drawing_info&id='.$d['id'].'" target="_blank">' . $d['name'] . '</a><br />';
					}
				}
			}

			if( intval(Request('id') != 0) )
			{
				$DB->Update($main_table, array(
					'skillset_id'=>intval($skillset['skillset_id']),
					'program_id'=>Request('program_id')
				), Request('id'));

				$t = $DB->SingleQuery('SELECT * FROM '.$main_table.' WHERE id='.intval(Request('id')));
				$school_abbr = $DB->GetValue('school_abbr', 'schools', $t['school_id']);
				$program = $DB->SingleQuery('SELECT * FROM programs WHERE id = '.$t['program_id']);
				if( count($program) > 0 )
					$t['full_name'] = $t['name'] == '' ? $program['title'] : $t['name'];
				$code = CleanDrawingCode($school_abbr.'_'.$t['full_name']);
			}
			else
			{
				$code = '';
			}

			$header = ShowRoadmapHeader(intval(Request('id')));

			echo '('.json_encode(array('skillset'=>$skillset['skillset_id'], 'code'=>$code, 'header'=>$header, 'drawings'=>$drawings)).')';
		}
		die();
	}

	$school_id = $DB->GetValue('school_id', $main_table, intval($_REQUEST['id']));
	$school_abbr = $DB->GetValue('school_abbr', 'schools', $school_id);

	$content = array();
	$content['name'] = $_REQUEST['title'];
	$content['last_modified'] = $DB->SQLDate();
	$content['last_modified_by'] = $_SESSION['user_id'];

	$DB->Update($main_table,$content,intval($_REQUEST['id']));

	if( KeyInRequest('changeTitle') )
	{
		$t = $DB->SingleQuery('SELECT * FROM '.$main_table.' WHERE id='.intval($_REQUEST['id']));
		$program = $DB->SingleQuery('SELECT * FROM programs WHERE id = '.$drawing['program_id']);
		if( count($program) > 0 )
			$t['full_name'] = $t['name'] == '' ? $program['title'] : $t['name'];
		$header = ShowRoadmapHeader(intval(Request('id')));
		echo '('.json_encode(array('title'=>$t['name'], 'header'=>$header, 'code'=>CleanDrawingCode($school_abbr.'_'.$t['full_name']))).')';
	}
}

if( Request('drawing_id') ) 
{
	// permissions check
	$drawing = GetDrawingInfo(intval(Request('drawing_id')), $mode);
	if( !($drawing['school_id'] == $_SESSION['school_id'] 
		|| $drawing['created_by'] == $_SESSION['user_id'] 
		|| $drawing['last_modified_by'] == $_SESSION['user_id']) )
	{
		die('403 Forbidden');
	}

	if( Request('note') !== false ) 
	{
		$content = array();
		$content['note'] = $_REQUEST['note'];
		$DB->Update($version_table, $content, intval($_REQUEST['drawing_id']));
	}

	if( Request('action') == 'lock' )
	{
		$DB->Update($version_table, array('frozen'=>1), intval(Request('drawing_id')));
	}
	
	die('200 OK');
}

?>
