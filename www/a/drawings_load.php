<?php
chdir("..");
include("inc.php");

switch( Request('type') ) {
	case 'post':
		$module_name = 'post_drawings';
		$session_key = 'post_drawing_list';
		$main_table = 'post_drawing_main';
		$version_table = 'post_drawings';
		break;
	case 'pathways':
	default:
		$module_name = 'drawings';
		$session_key = 'drawing_list';
		$main_table = 'drawing_main';
		$version_table = 'drawings';
		break;
}

ModuleInit($module_name);

if( Request('mode') != 'drawing_list' ) {
	// save state of selections
	if( !array_key_exists($session_key,$_SESSION) || KeyInRequest('userdefaults') ) {
		$_SESSION[$session_key] = array('school_id'=>'',
			'people_id'=>$_SESSION['user_id'],
			'categories'=>'');
	}
	if( KeyInRequest('school_id') ) {
		$_SESSION[$session_key]['school_id'] = Request('school_id');
	}
	if( KeyInRequest('people_id') ) {
		$_SESSION[$session_key]['people_id'] = Request('people_id');
	}
	if( KeyInRequest('categories') ) {
		$_SESSION[$session_key]['categories'] = Request('categories');
	}
}


switch( Request('mode') ) {

	case 'list_schools':

		// only need to filter by categories, not by people

		if( Request('categories') ) {
			$where = '';
			// don't show high schools when looking at roadmaps
			if( Request('type') == 'pathways' ) {
				$where = 'AND organization_type != "HS"';
			}
			
			$schools = $DB->VerticalQuery('
				SELECT schools.id, school_name
				FROM '.$main_table.' AS m
				LEFT JOIN schools ON school_id=schools.id
				WHERE m.name IN ("'.str_replace(',','","',Request('categories')).'")
				'.$where,
			'school_name','id');
		} else {
			$where = '';
			// don't show high schools when looking at roadmaps
			if( Request('type') == 'pathways' ) {
				$where = 'WHERE organization_type != "HS"';
			}
			
			$schools = $DB->VerticalQuery("
				SELECT id, school_name
				FROM schools
				$where
				ORDER BY school_name",
			'school_name','id');
		}

		$response[0] = Request('mode');
		$response[1] = array_keys($schools);
		$response[2] = array_values($schools);

		if( KeyInRequest('selectdefault') && array_key_exists('school_id',$_SESSION[$session_key]) ) {
			foreach( array_keys($schools) as $k ) {
				$response[3][] = (in_array($k,explode(',',$_SESSION[$session_key]['school_id']))?1:0);
			}
		} else {
			$_SESSION[$session_key]['school_id'] = "";
			if( count($schools) > 0 ) {
				$response[3] = array_fill(0,count($schools),0);
			} else {
				$response[3] = array();
			}
		}

		echo(json_encode_array($response));

		break;

	case 'list_people':

		if( Request('categories') ) {
			$people_ = $DB->MultiQuery('
				SELECT u1.id AS u1_id, CONCAT(u1.first_name," ",u1.last_name) AS u1_name,
					u2.id AS u2_id, CONCAT(u2.first_name," ",u2.last_name) AS u2_name
				FROM '.$main_table.' AS m
				LEFT JOIN users AS u1 ON u1.id=created_by
				LEFT JOIN users AS u2 ON u2.id=last_modified_by
				WHERE name IN ("'.str_replace(',','","',Request('categories')).'")');
			$people_cat = array();
			foreach( $people_ as $p ) {
				$people_cat[$p['u1_id']] = $p['u1_name'];
				$people_cat[$p['u2_id']] = $p['u2_name'];
			}
			$people = $people_cat;
		}
		if( Request('school_id') ) {
			$school_ids = implode('","', array_filter(explode(',',Request('school_id')), 'is_numeric'));
			$org_types = strtoupper(implode('","', array_filter(explode(',',Request('school_id')), 'is_not_numeric')));
			
			$sql = 'SELECT users.id, CONCAT(first_name," ",last_name) AS name
					FROM users
					JOIN schools ON users.school_id = schools.id
					WHERE ' . (strlen($school_ids)>0 ? 'school_id IN ("'.$school_ids.'")' : 'organization_type IN ("'.$org_types.'")' ) . '
					ORDER BY last_name, first_name';
			$people_school = $DB->VerticalQuery($sql,'name','id');
			$people = $people_school;
		}

		if( Request('categories')!="" && Request('school_id')!="" ) {
			// if they're both set, take the intersection of the two searches instead of writing a separate query
			$people = array_intersect($people_cat,$people_school);

		} elseif( Request('categories')=="" && Request('school_id')=="" ) {
			// if neither search is requested, return all people
			$people = $DB->VerticalQuery("
				SELECT id, CONCAT(first_name,' ',last_name) AS name
				FROM users
				ORDER BY last_name, first_name"
			,'name','id');
		}

		$response[0] = Request('mode');
		$response[1] = array_keys($people);
		$response[2] = array_values($people);

		if( KeyInRequest('selectdefault') && array_key_exists('people_id',$_SESSION[$session_key]) ) {
			foreach( array_keys($people) as $k ) {
				$response[3][] = (in_array($k,explode(',',$_SESSION[$session_key]['people_id']))?1:0);
			}
		} else {
			$_SESSION[$session_key]['people_id'] = "";
			if( count($people) > 0 ) {
				$response[3] = array_fill(0,count($people),0);
			} else {
				$response[3] = array();
			}
		}

		echo(json_encode_array($response));

		break;

	case 'list_categories':

		if( Request('people_id') )
		{
			$cats_people = $DB->VerticalQuery("
				SELECT DISTINCT(IF(name='',IFNULL(p.title,'(no title)'),name)) AS name
				FROM ".$main_table." AS m
				LEFT JOIN programs AS p ON m.program_id=p.id
				WHERE m.id IN (SELECT parent_id FROM ".$version_table."
					WHERE (created_by IN (".Request('people_id').") OR last_modified_by IN (".Request('people_id')."))
					)
				ORDER BY name", 'name','name');
			$cats = $cats_people;
		}
		if( Request('school_id') )
		{
			$school_ids = implode('","', array_filter(explode(',',Request('school_id')), 'is_numeric'));
			$org_types = strtoupper(implode('","', array_filter(explode(',',Request('school_id')), 'is_not_numeric')));

			$sql = "
				SELECT DISTINCT(IF(name='',IFNULL(p.title,'(no title)'),name)) AS name
				FROM ".$main_table.' AS m
				LEFT JOIN programs AS p ON m.program_id=p.id
				JOIN schools ON school_id=schools.id
				WHERE ' . (strlen($school_ids)>0 ? 'school_id IN ("'.$school_ids.'")' : 'organization_type IN ("'.$org_types.'")' ) . '
				ORDER BY name';
			$cats_school = $DB->VerticalQuery($sql, 'name','name');
			$cats = $cats_school;
		}

		if( Request('people_id')!="" && Request('school_id')!="" )
		{
			// if they're both set, take the intersection of the two searches instead of writing a separate query
			//$cats = array_intersect($cats_people,$cats_school);
			$cats = $cats_people;
		}
		elseif( Request('people_id')=="" && Request('school_id')=="" )
		{
			// if neither search is requested, return all titles
			$cats = $DB->VerticalQuery("
				SELECT DISTINCT(IF(name='',IFNULL(p.title,'(no title)'),name)) AS name
				FROM ".$main_table." AS m
				LEFT JOIN programs AS p ON m.program_id=p.id
				ORDER BY name"
			,'name','name');
		}

		$response[0] = Request('mode');
		$response[1] = array_values($cats);
		$response[2] = array_values($cats);
		if( KeyInRequest('selectdefault') && array_key_exists('categories',$_SESSION[$session_key]) )
		{
			foreach( array_keys($cats) as $k )
			{
				$response[3][] = (in_array($k,explode(',',$_SESSION[$session_key]['categories']))?1:0);
			}
		}
		else
		{
			$_SESSION['drawing_list']['categories'] = "";
			if( count($cats) > 0 )
				$response[3] = array_fill(0,count($cats),0);
			else
				$response[3] = array();
		}
		echo(json_encode_array($response));

		break;

	case 'drawing_list':
	
		if( Request('type') == 'post' )
		{
			if( count(explode(',', Request('schools'))) == 1 )
			{
				switch( Request('schools') )
				{
					case 'hs':
					case 'cc':
						$types = array(strtoupper(Request('schools')));
						break;
					case -1:
					case '':
						$types = array('HS','CC');
						break;
					default:
						$sch = $DB->SingleQuery('SELECT organization_type FROM schools WHERE id='.intval(Request('schools')));
						$types = array(($sch['organization_type']=='HS'?'HS':'CC'));
					break;
				}
			}
			else
			{
				$types = array('HS', 'CC');
			}
		}
		else
		{
			$types = array('Pathways');
		}

		foreach( $types as $t )
		{
			if( $t == 'HS' || $t == 'CC' )
			{
				$post_where = ' AND `type`="'.$t.'"';
			}
			else
			{
				$post_where = '';
			}

			if( Request('search') == "" )			
			{	
				$_SESSION['drawing_list_search_' . Request('type')] = '';
				$search = array();
				$fields = array('schools','people','categories');
				$field_sql = array(
					'schools'=>array('school_id'),
					'people'=>array('created_by','last_modified_by'),
					'categories'=>array('m.name','p.title')
				);
				foreach( $fields as $f ) 
				{
					if( Request($f) ) 
					{
						if($f == 'categories')
							$search[$f] = explode(',', Request($f));
						else
							// only include numbers in the filter array. this effectively removes the "cc" or "hs" when getting a list of all schools of a type 
							$search[$f] = array_filter(explode(',',Request($f)), 'is_numeric');
					}
				}
				$where = "";
				if( count($search) > 0 ) {
					foreach( $search as $field=>$items ) {
						switch( $field ) {
							case 'schools':
							case 'categories':
								$where .= " AND (";
								$i=0;
								foreach( $items as $s ) {
									foreach( $field_sql[$field] as $sfield ) {
										if( $i > 0 ) {
											$where .= ' OR ';
										}
										$where .= $sfield.'="'.$s.'" ';
										$i++;
									}
								}

								if( count($items) == 0 ) $where .= "1";
								$where .= ") ";
								break;
							case 'people':
								$where .= "AND m.id IN (SELECT parent_id FROM ".$version_table." WHERE (";
								$i=0;
								foreach( $items as $s ) {
									foreach( $field_sql[$field] as $sfield ) {
										if( $i > 0 ) {
											$where .= ' OR ';
										}
										$where .= $sfield.'="'.$s.'" ';
										$i++;
									}
								}
								$where .= "))";
								break;
						}
					}
				}

				$non_numeric = array_filter(explode(',', Request('schools')), 'is_not_numeric');
				if( count($non_numeric) > 0 )
				{
					$where_post_type = ' AND schools.`organization_type` IN ("' . strtoupper(implode('","', $non_numeric)) . '")';
				}
				else
				{
					$where_post_type = '';
				}
	
				$sql = "SELECT m.id, CONCAT(school_abbr,': ',IF(m.name='',IFNULL(p.title,'(no title)'),m.name)) AS name,
						code, created_by, last_modified_by, m.date_created, last_modified, school_id
					FROM ".$main_table." AS m
					LEFT JOIN programs AS p ON m.program_id=p.id
					JOIN schools ON school_id=schools.id
					WHERE 1
						$where $post_where $where_post_type
					ORDER BY name";
				$mains = $DB->MultiQuery($sql);
	
				foreach( $mains as &$parent ) {
					$drawings = $DB->ArrayQuery("
						SELECT *
						FROM ".$version_table."
						WHERE ".$version_table.".parent_id=".$parent['id']."
							AND deleted=0
						ORDER BY version_num DESC");
					$parent['drawings'] = $drawings;
				}
			} else {
			// Request('search') is not empty

				$_SESSION['drawing_list_search_' . Request('type')] = Request('search');
				$_SESSION[$session_key]['school_id'] = '';
				$_SESSION[$session_key]['people_id'] = '';
				$_SESSION[$session_key]['categories'] = '';
			
				if( $t == 'Pathways' )
				{
					$objects_table = 'objects';
					$post_ors = '';
				}
				else
				{
					$objects_table = 'post_cell';
					$post_ors = "OR INSTR(o.course_subject, '".$DB->Safe(Request('search'))."')
						OR INSTR(o.course_number, '".$DB->Safe(Request('search'))."')
						OR INSTR(o.course_title, '".$DB->Safe(Request('search'))."')";
				}

				$mains = $DB->MultiQuery("
					SELECT m.id, s.school_name, CONCAT(school_abbr,': ',IF(m.name='',IFNULL(p.title,'(no title)'),m.name)) AS name,
						m.code, GROUP_CONCAT(DISTINCT(d.id)) AS drawing_list,
						m.created_by, m.last_modified_by, m.date_created, m.last_modified, m.school_id
					FROM ".$objects_table." AS o
					JOIN ".$version_table." AS d ON o.drawing_id=d.id
					JOIN ".$main_table." AS m ON d.parent_id=m.id
					JOIN schools AS s ON m.school_id=s.id
					LEFT JOIN programs AS p ON m.program_id=p.id
					LEFT JOIN users AS dum ON dum.id=d.last_modified_by
					LEFT JOIN users AS duc ON duc.id=d.created_by
					LEFT JOIN users AS mum ON mum.id=m.last_modified_by
					LEFT JOIN users AS muc ON muc.id=m.created_by
					WHERE d.deleted=0
						AND (INSTR(o.content,'".$DB->Safe(Request('search'))."')
							$post_ors
							OR INSTR(m.name,'".$DB->Safe(Request('search'))."')
							OR INSTR(s.school_name,'".$DB->Safe(Request('search'))."')
							OR INSTR(s.school_abbr,'".$DB->Safe(Request('search'))."')
							OR INSTR(s.school_abbr,'".$DB->Safe(Request('search'))."')
							OR INSTR(CONCAT(dum.first_name,' ',dum.last_name),'".$DB->Safe(Request('search'))."')
							OR INSTR(CONCAT(duc.first_name,' ',duc.last_name),'".$DB->Safe(Request('search'))."')
							OR INSTR(CONCAT(mum.first_name,' ',dum.last_name),'".$DB->Safe(Request('search'))."')
							OR INSTR(CONCAT(muc.first_name,' ',duc.last_name),'".$DB->Safe(Request('search'))."')
						)
						$post_where
					GROUP BY m.id
					ORDER BY version_num DESC");
	
				foreach( $mains as &$parent ) {
					/*
					foreach( explode(',',$parent['drawing_list']) as $d_id ) {
						$drawing = $DB->SingleQuery("
							SELECT *
							FROM ".$version_table."
							WHERE id=".$d_id);
						$parent['drawings'][] = $drawing;
					}
					*/
					$drawings = $DB->MultiQuery("
						SELECT *
						FROM ".$version_table."
						WHERE parent_id=".$parent['id']." 
							AND deleted=0");
					$parent['drawings'] = $drawings;
					
					usort($parent['drawings'],'drawing_sort_by_version');
				}
	
			}
	
			if( $t == 'HS' )
				echo '<h3>High School Programs</h3>';
			
			if( $t == 'CC' )
				echo '<h3>Community College Pathways</h3>';
	
			ShowDrawingList($mains, Request('type'));
		}
		
		break;
}


function is_not_numeric($a)
{
	return $a != '' && !is_numeric($a);
}

?>
