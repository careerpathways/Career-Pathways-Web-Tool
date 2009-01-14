<?php
/*
   These classes implement the structure of the CCTI drawings. There should be no other code that
   reads from the CCTI tables except here. The cctiserv.php file contains all the SQL necessary
   to write to the tables. These two files are the only places where SQL code should exist.
*/

function CCTI_check_permission($id)
{
	return true;
	global $DB;

	// SuperAdmins and people of the same school can edit drawings
	$drawing = $DB->SingleQuery("SELECT *
		FROM ccti_drawings
		WHERE id=".$id);
	if( !is_array($drawing) || (!IsAdmin() && $_SESSION['school_id'] != $drawing['school_id']) ) {
		chlog("permissions error (CCTI)");
		//die();
	}
	return true;
}



class CCTI_Drawing
{
	private $data = array();
	private $parent_data = array();
	private $programs = array();

	public function __construct($id='')
	{
		global $DB;


		$this->data = $DB->LoadRecord('ccti_drawings', $id);
		$this->parent_data = $DB->LoadRecord('ccti_drawing_main', $this->data['parent_id']);

		$programs = $DB->VerticalQuery('SELECT id FROM ccti_programs WHERE drawing_id='.$id.' ORDER BY `index`', 'id');
		foreach( $programs as $p )
		{
			$this->programs[$p] = new CCTI_Program($p);
		}

	}

	public function &__get($name)
	{
		switch($name)
		{
			case 'id':
				return $this->data[$name];
				break;
			case 'name':
				return $this->parent_data[$name];
				break;
			case 'programs':
				return $this->programs;
				break;
		}
	}

	public function __set($name, $value)
	{
		switch($name)
		{
			case 'name':
				$this->data[$name] = $value;
				break;
		}
	}

	public function commit()
	{
		global $DB;

		// save local data


		// call each program's commit


	}

	protected function loadDefaults()
	{
		global $DB;
		return $DB->LoadRecord('ccti_drawings','');
	}

}


class CCTI_Program
{

	private $data = array();
	private $sections = array();
	private $school = array();


	public function __construct($id='')
	{
		global $DB;


		if( $id == '' )
		{
			$this->data = $this->loadDefaults();

			$this->sections[] = new CCTI_Section('', $this);
		}
		else
		{
			$this->data = $DB->LoadRecord('ccti_programs', $id);

			if( $this->school_id == -1 )
			{
				$this->school = array('school_name'=>'Name of High School');
			}
			else
			{
				$this->school = $DB->LoadRecord('schools', $this->school_id);
			}

			$sections = $DB->VerticalQuery('SELECT id FROM ccti_sections WHERE program_id='.$id, 'id');
			foreach( $sections as $s )
			{
				$this->sections[$s] = new CCTI_Section($s, $this);
			}
		}
	}

	public function &__get($name)
	{
		switch($name)
		{
			case 'id':
			case 'drawing_id':
			case 'header':
			case 'footer':
			case 'school_id':
			case 'num_columns':
			case 'show_occ_titles':
			case 'occ_titles':
			case 'headleft':
			case 'headright':
				return $this->data[$name];
				break;
			case 'school_name':
				return $this->school['school_name'];
				break;
			case 'total_rows':
				$rows = 0;
				$rows += ($this->header==''?0:1);
				$rows += ($this->footer==''?0:1);
				$rows += $this->content_rows;
				return $rows;
				break;
			case 'total_rows_edit':
				$rows = 0;
				$rows += 1;
				$rows += 1;
				$rows += $this->content_rows_edit;
				return $rows;
				break;
			case 'all_header_rows':
				$rows = 0;
				foreach( $this->sections as $s )
					$rows += $s->header_rows;
				return $rows;
				break;
			case 'content_rows':
				$rows = 0;
				foreach( $this->sections as $s )
					$rows += $s->total_rows;
				return $rows;
				break;
			case 'content_rows_edit':
				$rows = 0;
				foreach( $this->sections as $s )
					$rows += $s->total_rows_edit;
				return $rows;
				break;
			case 'total_cols':
				$cols = 0;
				$cols += $this->content_cols;
				$cols += 3; // school_name, completes_with, and row names
				return $cols;
				break;
			case 'content_cols':
				$cols = 0;
				$cols += $this->num_columns;
				$cols += ($this->show_occ_titles?1:0);
				return $cols;
				break;
			case 'sections':
				return $this->sections;
				break;
		}
	}

	public function __set($name, $value)
	{
		switch($name)
		{
			case 'header':
			case 'footer':
			case 'completes_with':
				$this->data[$name] = $value;
				break;
		}
	}

	public function __toString()
	{
		return __CLASS__;
	}

	public function commit()
	{
		global $DB;
		// saves all variables back to the database

	}

	protected function loadDefaults()
	{
		global $DB;
		return $DB->LoadRecord('ccti_programs','');
	}

}


class CCTI_Section
{
	private $data = array();
	private $labels_x = array();
	private $labels_y = array();
	private $label_xy;
	private $content;
	private $parent;

	public function __construct($id='', &$parent)
	{
		global $DB;

		$this->parent = $parent;
		$this->data = $DB->LoadRecord('ccti_sections', $id);
		$this->content = new CCTI_Section_Content_Row($id);

		if( $id == '' )
		{


		}
		else
		{
			$labels_x = $DB->MultiQuery('SELECT id, row, col FROM ccti_section_labels
				WHERE section_id='.$id.' AND axis="x" ORDER BY row, col');
			foreach( $labels_x as $lx )
			{
				$this->labels_x[$lx['row']][$lx['col']] = new CCTI_Section_Label($lx['id']);
			}

			$labels_y = $DB->MultiQuery('SELECT id, row, col FROM ccti_section_labels
				WHERE section_id='.$id.' AND axis="y" ORDER BY row, col');
			foreach( $labels_y as $ly )
			{
				$this->labels_y[$ly['row']][$ly['col']] = new CCTI_Section_Label($ly['id']);
			}

			$label_xy = $DB->SingleQuery('SELECT id FROM ccti_section_labels
				WHERE section_id='.$id.' AND axis="xy"'); // assume the database is correct and only one record exists
			$this->label_xy = new CCTI_Section_Label($label_xy['id']);

			$content = $DB->MultiQuery('SELECT id, section_id, row, col FROM ccti_data
				WHERE section_id='.$id.' ORDER BY row, col');
			foreach( $content as $c )
			{
				$this->content[$c['row']][$c['col']] = new CCTI_Cell($c['row'], $c['col'], $c['section_id'], intval($c['id']));
			}
		}


	}

	public function &__get($name)
	{
		switch($name)
		{
			case 'id':
			case 'num_rows':
			case 'program_id':
			case 'header':
			case 'index':
				return $this->data[$name];
				break;
			case 'total_rows':
				$rows = 0;
				$rows += $this->num_rows;
				$rows += ($this->header == ''?0:1);
				$rows += count($this->labels_x);
				return $rows;
				break;
			case 'total_rows_edit':
				$rows = 0;
				$rows += $this->num_rows;
				$rows += 1;
				$rows += count($this->labels_x);
				return $rows;
				break;
			case 'header_rows':
				$rows = count($this->labels_x);
				return $rows;
				break;
			case 'labels_x':
				return $this->labels_x;
				break;
			case 'labels_y':
				return $this->labels_y;
				break;
			case 'label_xy':
				return $this->label_xy;
				break;
			case 'content':
				return $this->content;
				break;
		}
	}


	public function commit()
	{
		global $DB;
		// saves all variables back to the database
		/*
		$data = array();
		$data['header'] = $this->header;
		$data['`index`'] = $this->index;
		$data['num_rows'] = $this->num_rows;
		$DB->Update('ccti_sections', $data, $this->id);

		foreach( $this->labels_x as $lx1 )
		{
			foreach( $lx1 as $lx2 )
			{
				$lx2->commit();
			}
		}

		foreach( $this->labels_y as $ly1 )
		{
			foreach( $ly1 as $ly2 )
			{
				$ly2->commit();
			}
		}

		$this->label_xy->commit();
		$this->content->commit();
		*/
	}

	protected function loadDefaults()
	{
		global $DB;
		return $DB->LoadRecord('ccti_sections','');
	}

}


abstract class CCTI_Section_Content extends ArrayIterator implements ArrayAccess
{

	private $i;
	private $arrayKeys;
	private $section_id;

	public function offsetExists($offset)
	{
		//echo $this->type.' offsetExists('.$offset.')<br>';
		return array_key_exists($offset, $this->data);
	}

	public function offsetUnset($offset)
	{
		//echo $this->type.' offsetUnset('.$offset.')<br>';
	}


	public function current()
	{
		return $this->data[$this->arrayKeys[$this->i]];
	}
	public function key()
	{
		return $this->arrayKeys[$i];
	}
	public function next()
	{
		$this->i++;
	}
	public function rewind()
	{
		$this->arrayKeys = array_keys($this->data);
		$this->i = 0;
		reset($this->data);
	}
	public function valid()
	{
		return $this->i < count($this->data);
	}

	abstract function commit();

}


class CCTI_Section_Content_Row extends CCTI_Section_Content
{
	public function __construct($section_id)
	{
		$this->data = array();
		$this->section_id = $section_id;
	}

	public function commit()
	{
		/*
		global $DB;

		foreach( $this->data as $col )
		{
			$col->commit();
		}
		*/
	}

	public function offsetGet($offset)
	{
		if( !array_key_exists($offset, $this->data) )
		{
			$this->data[$offset] = new CCTI_Section_Content_Col($this->section_id, $offset);
		}
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

}

class CCTI_Section_Content_Col extends CCTI_Section_Content
{
	private $index;

	public function __construct($section_id, $index)
	{
		$this->data = array();
		$this->index = $index;
		$this->section_id = $section_id;
	}

	public function commit()
	{
		global $DB;
		/*
		foreach( $this->data as $key=>$cell )
		{
			$cell->commit();
		}
		*/
	}

	public function offsetGet($offset)
	{
		if( !array_key_exists($offset, $this->data) ) $this->data[$offset] = new CCTI_Cell($this->index, $offset, $this->section_id);
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

}



class CCTI_Section_Label
{
	private $data;


	public function __construct($id='')
	{
		global $DB;

		$this->data = $DB->LoadRecord('ccti_section_labels', $id);
	}

	public function &__get($name)
	{
		switch($name)
		{
			case 'id':
			case 'section_id':
			case 'axis':
			case 'col':
			case 'row':
			case 'colspan':
			case 'rowspan':
			case 'text':
				return $this->data[$name];
				break;
			default:
				$m = "";
				return $m;
				break;
		}
	}

	public function __set($name, $value)
	{
		switch($name)
		{
			case 'text':
				$this->data[$name] = $value;
				break;
		}
	}
	
	public function commit()
	{
	}


}


class CCTI_Cell
{
	private $data;
	private $row;
	private $col;
	private $section_id;

	public function __construct($row, $col, $section_id, $id_or_text='')
	{
		global $DB;

		if( is_int($id_or_text) )
		{
			$this->data = $DB->LoadRecord('ccti_data', $id_or_text);
		}
		else
		{
			$this->data = $DB->LoadRecord('ccti_data', '');
			$this->text = $id_or_text;
		}

		$this->row = $row;
		$this->col = $col;
		$this->section_id = $section_id;
	}


	public function &__get($name)
	{
		switch($name)
		{
			case 'id':
			case 'text':
			case 'colspan':
			case 'rowspan':
				return $this->data[$name];
				break;
		}
	}

	public function __set($name, $value)
	{
		switch($name)
		{
			case 'text':
				$this->data[$name] = $value;
				break;
		}
	}

	public function commit()
	{
	}

	public function __toString()
	{
		return $this->row.'x'.$this->col.': "'.$this->data['text'].'"';
	}

}


?>