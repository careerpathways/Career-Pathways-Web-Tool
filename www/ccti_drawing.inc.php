<?php
/*
   These classes implement the structure of the CCTI drawings. The PHP code should ONLY 
   interact with these objects, and never with the database directly. These objects
   contain *all* the SQL necessary for dealing with CCTI drawings.
   
   Nothing should ever be written back to the database until "commit" is called.

*/

class CCTI_Drawing
{
	private $data = array();
	private $programs = array();

	public function __construct($id='')
	{
		global $DB;

		
		if( $id == '' )
		{
			$this->data = $this->loadDefaults();

			$this->programs[] = new CCTI_Program();
		} 
		else 
		{
			$this->data = $DB->LoadRecord('ccti_drawings', $id);

			$programs = $DB->VerticalQuery('SELECT id FROM ccti_programs WHERE drawing_id='.$id.' ORDER BY `index`', 'id');
			foreach( $programs as $p )
			{
				$this->programs[] = new CCTI_Program($p);
			}
		}
	
	}
	
	public function &__get($name) 
	{
		switch($name)
		{
			case 'id':
			case 'name':
				return $this->data[$name];
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
				$this->sections[] = new CCTI_Section($s, $this);
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
			case 'completes_with':
			case 'num_columns':
			case 'show_occ_titles':
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
			case 'content_rows':
				$rows = 0;
				foreach( $this->sections as $s ) 
					$rows += $s->total_rows;
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
	private $content = array();
	private $parent;

	public function __construct($id='', &$parent)
	{
		global $DB;

		$this->parent = $parent;
		$this->data = $DB->LoadRecord('ccti_sections', $id);

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

			$content = $DB->MultiQuery('SELECT id, row, col FROM ccti_data
				WHERE section_id='.$id.' ORDER BY row, col');
			foreach( $content as $c )
			{
				$this->content[$c['row']][$c['col']] = new CCTI_Cell($c['id']);
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
	
	}
	
	protected function loadDefaults()
	{
		global $DB;
		return $DB->LoadRecord('ccti_sections','');
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
		}
	}

	public function commit()
	{
		global $DB;
		// saves all variables back to the database
	
	}


}


class CCTI_Cell
{
	private $data;

	public function __construct($id='')
	{
		global $DB;

		$this->data = $DB->LoadRecord('ccti_data', $id);
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

}





?>