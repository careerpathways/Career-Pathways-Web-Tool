<?php

abstract class POSTChart
{
	protected $_id;
	protected $_drawing;

	// 2D array [row][col]
	protected $_content;

	protected $_cols;
	protected $_cells;

	// factory method to create an object of the correct type
	public static function create($id)
	{
		global $DB;
		
		$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, schools.school_name, d.num_rows, d.id
			FROM post_drawing_main AS main, post_drawings AS d, schools
			WHERE d.parent_id = main.id
				AND main.school_id = schools.id
				AND d.id = '.$id.'
				AND deleted = 0');
		if( is_array($drawing) ) 
		{
			switch( $drawing['type'] )
			{
				case 'HS':
					return new POSTChart_HS($drawing);
				case 'CC':
					return new POSTChart_CC($drawing);
			}
		}	
	}


	public function __construct($drawing)
	{
		global $DB;
	
		$this->_id = $drawing['id'];
		$this->_drawing = $drawing;

	}

	// create an empty $_cells array of the appropriate size
	private function _initCells()
	{
		global $DB;

		$num_rows = $this->_drawing['num_rows'];

		$cols = $DB->MultiQuery('SELECT * FROM post_col WHERE drawing_id=' . $this->_id . ' ORDER BY num');

		// store the column names for later
		foreach( $cols as $col )
		{
			$this->_cols[$col['num']] = $col['title'];
		}

		// create the empty 2D array
		for( $row = 1; $row <= $num_rows; $row++ )
		{
			$this->_cells[$row] = array();
			foreach( $cols as $col )
			{
				$this->_content[$row][$col['num']] = new POSTCell();
			}
		}
	}


	// populate the $_cells array from the DB
	private function _loadData()
	{
		global $DB;

		// load cell data
		$cells = $DB->MultiQuery('
			SELECT cell.*, col.num AS col_num
			FROM post_cell AS cell
			JOIN post_col AS col ON cell.col_id=col.id
			WHERE cell.drawing_id = ' . $this->_id . '
		');
		foreach( $cells as $cell )
		{
			$this->_content[$cell['row_num']][$cell['col_num']] = new POSTCell($cell);
		}
	}


	public function display()
	{
		$this->_initCells();
		$this->_loadData();

		echo '<table border="1" class="post_chart">';
		$this->_printHeaderRow();
		foreach( $this->_content as $rowNum=>$row )
		{
			echo '<tr>';
			echo '<td class="post_head_row post_head">' . $this->_rowName($rowNum) . '</td>';
			foreach( $row as $cell )
			{
				echo '<td class="post_cell">' . $this->_cellContent($cell) . '</td>';		
			}
			echo '</tr>';
		}
		echo '<tr>';
			echo '<td class="post_footer" colspan="' . $this->footerCols . '">footer</td>';
		echo '</tr>';
		echo '</table>';
	}



	protected abstract function _printHeaderRow();
	protected abstract function _rowName($num);
	
	public function verticalText($text)
	{
		return '<img src="/files/postv/' . base64_encode($text) . '.png" alt="' . $text . '" />';	
	}
	
	public function __get($key)
	{
		// some predefined variables
		switch( $key )
		{
			case 'totalRows':
				return count($this->_content) + 2;
				
			case 'totalCols':
				return count($this->_cols) + 3;
				
			case 'footerCols':
				return $this->totalCols - 2;
			
			case 'schoolName':
				return $this->_drawing['school_name'];
				
			case 'drawingName':
				return $this->_drawing['name'];
		}

		// lastly, check for any keys in the drawing record
		if( array_key_exists($key, $this->_drawing) )
			return $this->_drawing[$key];
		else
			return null;		
	}
	
}


class POSTChart_HS extends POSTChart
{

	protected function _rowName($num)
	{
		switch( $num )
		{
			case 1:
			case 2:
			case 3:
			case 4:
				return '' . $num+8;
			default:
				return '';
		}
	}
	
	protected function _printHeaderRow()
	{
		echo '<tr>';
			echo '<td class="post_sidebar_left" rowspan="' . $this->totalRows . '">' . $this->verticalText($this->schoolName). '</td>';
			echo '<th class="post_head_xy post_head">Grade</th>';
			foreach( $this->_cols as $col )
			{
				echo '<th class="post_head_main post_head">' . $col . '</th>';
			}
			echo '<td  class="post_sidebar_right" rowspan="' . $this->totalRows . '">' . $this->verticalText('High School Diploma') . '</td>';
		echo '</tr>';
	}

	protected function _cellContent(&$cell)
	{
		return htmlentities($cell->content);
	}
}

class POSTChart_CC extends POSTChart
{

	protected function _rowName($num)
	{
		return ucfirst(ordinalize($num)) . ' Term';
	}

	protected function _printHeaderRow()
	{
		echo '<tr>';
			echo '<td class="post_sidebar_left" valign="middle" rowspan="' . $this->totalRows . '">' . $this->verticalText($this->schoolName). '</td>';
			echo '<th class="post_head_xy post_head" style="width:40px;">Term</th>';
			echo '<th class="post_head_main post_head" colspan="' . count($this->_cols) . '">' . $this->drawingName . '</th>';
			echo '<td class="post_sidebar_right" valign="middle" rowspan="' . $this->totalRows . '">' . $this->verticalText('Career Pathway Certificate of Completion') . '</td>';
		echo '</tr>';
	}

	protected function _cellContent(&$cell)
	{
		return '<a href="#">' . $cell->course_subject . ' ' . $cell->course_number . '<br />' . $cell->course_title . '</a>';
	}
}


class POSTCell
{
	private $_data;
	
	public function __construct($data=array())
	{
		$this->_data = $data;
	}
	
	public function __get($key)
	{
		if( array_key_exists($key, $this->_data) )
			return $this->_data[$key];
		else
			return ' ';
	}
}


?>