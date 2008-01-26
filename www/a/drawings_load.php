<?php
chdir("..");
include("inc.php");

ModuleInit('drawings');


switch( Request('mode') ) {

	case 'list_schools':

		// only need to filter by categories, not by people

		if( Request('categories') ) {
			$schools = $DB->VerticalQuery('
				SELECT schools.id, school_name
				FROM drawing_main
				LEFT JOIN schools ON school_id=schools.id
				WHERE drawing_main.name IN ("'.str_replace(',','","',Request('categories')).'")',
			'school_name','id');
		} else {
			$schools = $DB->VerticalQuery("
				SELECT id, school_name
				FROM schools
				ORDER BY school_name",
			'school_name','id');
		}

		$response[0] = Request('mode');
		$response[1] = array_keys($schools);
		$response[2] = array_values($schools);

		if( KeyInRequest('selectdefault') ) {
			foreach( array_keys($schools) as $k ) {
				$response[3][] = ($_SESSION['school_id']==$k?1:0);
			}
		} else {
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
				FROM drawing_main
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
			$people_school = $DB->VerticalQuery('
				SELECT id, CONCAT(first_name," ",last_name) AS name
				FROM users
				WHERE school_id IN ("'.str_replace(',','","',Request('school_id')).'")
				ORDER BY last_name, first_name'
			,'name','id');
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

		if( KeyInRequest('selectdefault') ) {
			foreach( array_keys($people) as $k ) {
				$response[3][] = ($_SESSION['user_id']==$k?1:0);
			}
		} else {
			if( count($people) > 0 ) {
				$response[3] = array_fill(0,count($people),0);
			} else {
				$response[3] = array();
			}
		}

		echo(json_encode_array($response));

		break;

	case 'list_categories':

		if( Request('people_id') ) {
			$cats_people = $DB->VerticalQuery("
				SELECT DISTINCT(IF(name='','(no title)',name)) AS name
				FROM drawing_main
				WHERE drawing_main.id IN (SELECT parent_id FROM drawings
					WHERE (created_by IN (".Request('people_id').") OR last_modified_by IN (".Request('people_id')."))
					)
				ORDER BY name", 'name','name');
			$cats = $cats_people;
		}
		if( Request('school_id') ) {
			$cats_school = $DB->VerticalQuery("
				SELECT DISTINCT(IF(name='','(no title)',name)) AS name
				FROM drawing_main
				WHERE drawing_main.id IN (SELECT parent_id FROM drawings
					WHERE (school_id IN (".Request('school_id')."))
					)
				ORDER BY name", 'name','name');
			$cats = $cats_school;
		}

		if( Request('people_id')!="" && Request('school_id')!="" ) {
			// if they're both set, take the intersection of the two searches instead of writing a separate query
			$cats = array_intersect($cats_people,$cats_school);

		} elseif( Request('people_id')=="" && Request('school_id')=="" ) {
			// if neither search is requested, return all titles
			$cats = $DB->VerticalQuery("
				SELECT DISTINCT(name) AS name
				FROM drawing_main
				ORDER BY name"
			,'name','name');
		}

		//if( count($cats) == 0 ) {
		//	$cats[] = '(none)';
		//}

		$response[0] = Request('mode');
		$response[1] = array_values($cats);
		$response[2] = array_values($cats);
		if( count($cats) > 0 ) {
			$response[3] = array_fill(0,count($cats),0);
		} else {
			$response[3] = array();
		}
		echo(json_encode_array($response));

		break;

	case 'drawing_list':

		if( Request('search') == "" ) {
			$search = array();
			$fields = array('schools','people','categories');
			$field_sql = array(
				'schools'=>array('school_id'),
				'people'=>array('created_by','last_modified_by'),
				'categories'=>array('name')
			);
			foreach( $fields as $f ) {
				if( Request($f) ) {
					$search[$f] = explode(',',Request($f));
				}
			}

			$where = "";
			$where2 = "";
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
							$where .= ") ";
							break;
						case 'people':
							$where .= "AND drawing_main.id IN (SELECT parent_id FROM drawings WHERE (";
							$where2 .= " AND (";
							$i=0;
							foreach( $items as $s ) {
								foreach( $field_sql[$field] as $sfield ) {
									if( $i > 0 ) {
										$where .= ' OR ';
										$where2 .= ' OR ';
									}
									$where .= $sfield.'="'.$s.'" ';
									$where2 .= $sfield.'="'.$s.'" ';
									$i++;
								}
							}
							$where .= "))";
							$where2 .= ")";
							break;
					}
				}
			}

			$mains = $DB->MultiQuery("
				SELECT drawing_main.id, CONCAT(school_abbr,': ',IF(name='','(no title)',name)) AS name, code,
					created_by, last_modified_by, date_created, last_modified, school_id
				FROM drawing_main, schools
				WHERE school_id=schools.id
					$where
				ORDER BY name");

			foreach( $mains as &$parent ) {
				$drawings = $DB->ArrayQuery("
					SELECT *
					FROM drawings
					WHERE drawings.parent_id=".$parent['id']."
						AND deleted=0
						$where2
					ORDER BY version_num");
				$parent['drawings'] = $drawings;
			}
		} else {

			$mains = $DB->MultiQuery("
				SELECT m.id, s.school_name, CONCAT(school_abbr,': ',IF(m.name='','(no title)',m.name)) AS name, m.code, GROUP_CONCAT(DISTINCT(d.id)) AS drawing_list,
					m.created_by, m.last_modified_by, m.date_created, m.last_modified, m.school_id
				FROM objects AS o
				JOIN drawings AS d ON o.drawing_id=d.id
				JOIN drawing_main AS m ON d.parent_id=m.id
				JOIN schools AS s ON m.school_id=s.id
				LEFT JOIN users AS dum ON dum.id=d.last_modified_by
				LEFT JOIN users AS duc ON duc.id=d.created_by
				LEFT JOIN users AS mum ON mum.id=m.last_modified_by
				LEFT JOIN users AS muc ON muc.id=m.created_by
				WHERE d.deleted=0
					AND (INSTR(content,'".$DB->Safe(Request('search'))."')
						OR INSTR(m.name,'".$DB->Safe(Request('search'))."')
						OR INSTR(s.school_name,'".$DB->Safe(Request('search'))."')
						OR INSTR(s.school_abbr,'".$DB->Safe(Request('search'))."')
						OR INSTR(s.school_abbr,'".$DB->Safe(Request('search'))."')
						OR INSTR(CONCAT(dum.first_name,' ',dum.last_name),'".$DB->Safe(Request('search'))."')
						OR INSTR(CONCAT(duc.first_name,' ',duc.last_name),'".$DB->Safe(Request('search'))."')
						OR INSTR(CONCAT(mum.first_name,' ',dum.last_name),'".$DB->Safe(Request('search'))."')
						OR INSTR(CONCAT(muc.first_name,' ',duc.last_name),'".$DB->Safe(Request('search'))."')
					)
				GROUP BY m.id
				ORDER BY version_num");

			foreach( $mains as &$parent ) {
				foreach( explode(',',$parent['drawing_list']) as $d_id ) {
					$drawing = $DB->SingleQuery("
						SELECT *
						FROM drawings
						WHERE id=".$d_id);
					$parent['drawings'][] = $drawing;
				}
				usort($parent['drawings'],'drawing_sort_by_version');
			}

		}

		ShowDrawingList($mains);

		break;
}




?>