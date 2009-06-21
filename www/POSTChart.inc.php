<?php

abstract class POSTChart
{
	protected $_id;
	protected $_type;
	protected $_school_id;
	protected $_parent_id;
	
	protected $_name;
	protected $_code;
	protected $_skillset_id;
	protected $_school_name;
	protected $_school_abbr;
	protected $_footer_link;
	protected $_footer_text;
	protected $_sidebar_right;

	// 2D array [row#][col#]
	protected $_cells;

	protected $_cols;
	protected $_rows;

	protected $_knownLegend = array();


	// Create from the a database record
	// factory method to create an object of the correct type
	public static function create($id)
	{
		global $DB;

		$drawing = $DB->SingleQuery('SELECT main.*, `d`.`footer_text`, `d`.`footer_link`, `d`.`sidebar_text_right`, `d`.`id`
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
					$post = new POSTChart_HS();
					break;
				case 'CC':
					$post = new POSTChart_CC();
					break;
				default:
					throw new Exception('No drawing type was found in the record.');
			}
			$post->loadDataFromDB($id);
			$post->name = $drawing['name'];
			$post->school_id = $drawing['school_id'];
			$post->footer_link = $drawing['footer_link'];
			$post->footer_text = $drawing['footer_text'];
			$post->sidebar_right = $drawing['sidebar_text_right'];
			
			return $post;
		}
		else
		{
			throw new Exception('Drawing not found or it has been deleted');
		}
	}
	
	public function __construct()
	{
		
		
	}
	
	public function loadDataFromDB($version_id)
	{
		global $DB;
		
		$this->_cols = $DB->MultiQuery('SELECT id, title, num FROM post_col WHERE drawing_id='.$version_id.' ORDER BY num');
		$this->_rows = $DB->MultiQuery('SELECT id, row_type, row_year, row_term FROM post_row WHERE drawing_id='.$version_id.' ORDER BY row_type, row_year, row_term, id');
		
		$colmap = array();
		foreach( $this->_cols as $i=>$a )
			$colmap[$a['id']] = $i;

		$rowmap = array();
		foreach( $this->_rows as $i=>$a )
			$rowmap[$a['id']] = $i;
		
		$cells = $DB->MultiQuery('SELECT id, row_id, col_id, content, href, legend, course_subject, course_number, course_title FROM post_cell WHERE row_id > 0 AND col_id > 0 AND drawing_id='.$version_id);

		foreach( $cells as $c )
		{
			$row = $rowmap[$c['row_id']];
			$col = $colmap[$c['col_id']];
			$this->_cells[$row][$col] = new POSTCell($c);
		}
		if( is_array($this->_cells) )
		{
			foreach( $this->_cells as $k=>$v )
			{
				ksort($this->_cells[$k]);
			}
			ksort($this->_cells);
		}
	
		$this->_id = $version_id;
	}

	/**
	 * Fill up the rows/cols/cells arrays with empty values, so that when we call saveToDB there is some stuff to save
	 */
	public function createEmptyChart()
	{
		$this->_createEmptyChart();

		// now create all the empty cells for the drawing
		for( $x=0; $x<count($this->_cols); $x++ )
		{
			for( $y=0; $y<count($this->_rows); $y++ )
			{
				$this->_cells[$y][$x] = new POSTCell();
			}
		}
		
	}

	// Used to import drawings from excel files
	public function loadDataFromArray($data)
	{
		// not actually used now...
	}
	
	private function _gatherLegend($legend)
	{
		global $DB;
		$legendText = $DB->MultiQuery("SELECT `text` FROM `post_legend` ORDER BY `id` ASC");

		// Figure out our legend code
		$background = $titleTag = '';
		foreach(explode('-', $legend) as $id)
			if( $id )
			{
				$background .= $id . '-';
				$titleTag .= $legendText[($id - 1)]['text'] . ', ';
				@$this->_knownLegend[$id] = $legendText[($id - 1)]['text'];
			}
		$titleTag = (strlen($titleTag) > 0) ? ' title="' . substr($titleTag, 0, -2) . '"' : '';
		$background = (strlen($background) > 0) ? 'background: url(/c/images/legend/' . substr($background, 0, -1) . '.png) top left no-repeat;' : '';	
		// Return our information
		return array($titleTag, $background);
	}
	
	protected function _rowName($num)
	{
		return $this->rowNameFromData($this->_rows[$num]);
	}

	protected function _printHeaderRow() {}
	protected function _printHeaderRowMini() {}

	public function verticalText($text)
	{
		return '<img src="/files/postv/' . base64_encode($text) . '.png" alt="' . $text . '" />';	
	}

	public function saveToDB($parent_id=0)
	{
		global $DB;

		$post_drawing = array();

		// create post_drawing_main record
		if( $parent_id == 0 )
		{
			$post_drawing_main = array();
			$post_drawing_main['school_id'] = $this->_school_id;
			$post_drawing_main['skillset_id'] = dv($this->_skillset_id);
			$post_drawing_main['name'] = $this->_name;
			$post_drawing_main['date_created'] = $DB->SQLDate();
			$post_drawing_main['last_modified'] = $DB->SQLDate();
			$post_drawing_main['created_by'] = $_SESSION['user_id'];
			$post_drawing_main['last_modified_by'] = $_SESSION['user_id'];
			$post_drawing_main['type'] = $this->_type;
			$post_drawing_main_id = $DB->Insert('post_drawing_main', $post_drawing_main);

			$DB->Query('UPDATE post_drawing_main SET `code` = "'.$post_drawing_main_id.'" WHERE `id` = '.$post_drawing_main_id);

			$post_drawing['version_num'] = 1;
		}
		else
		{
			$post_drawing_main_id = $parent_id;
			$nvn = $DB->SingleQuery('SELECT MAX(version_num)+1 AS next_version_num
				FROM post_drawings 
				WHERE parent_id='.$parent_id);
			$post_drawing['version_num'] = $nvn['next_version_num'];
		}

		$post_drawing['parent_id'] = $post_drawing_main_id;
		$post_drawing['footer_text'] = "".$this->_footer_text;
		$post_drawing['footer_link'] = "".$this->_footer_link;
		$post_drawing['sidebar_text_right'] = "".$this->_sidebar_right;
		$post_drawing['published'] = 0;
		$post_drawing['frozen'] = 0;
		$post_drawing['deleted'] = 0;
		$post_drawing['date_created'] = $DB->SQLDate();
		$post_drawing['last_modified'] = $DB->SQLDate();
		$post_drawing['created_by'] = $_SESSION['user_id'];
		$post_drawing['last_modified_by'] = $_SESSION['user_id'];

		$post_drawing_id = $DB->Insert('post_drawings', $post_drawing);

		$colmap = array();
		foreach( $this->_cols as $i=>$col )
		{
			$post_col = array();
			$post_col['drawing_id'] = $post_drawing_id;
			$post_col['title'] = dv($col['title']);
			$post_col['num'] = $i;
			$post_col_id = $DB->Insert('post_col', $post_col);
			$colmap[$i] = $post_col_id;
		}

		$rowmap = array();
		foreach( $this->_rows as $i=>$row )
		{
			$post_row = array();
			$post_row['drawing_id'] = $post_drawing_id;
			$post_row['row_type'] = $row['row_type'];
			$post_row['row_year'] = $row['row_year'];
			$post_row['row_term'] = ($row['row_term']?$row['row_term']:"");
			$post_row_id = $DB->Insert('post_row', $post_row);
			$rowmap[$i] = $post_row_id;
		}

		foreach( $this->_cells as $row_num=>$row )
		{
			foreach( $row as $col_num=>$cell )
			{
				$post_cell = array();
				$post_cell['drawing_id'] = $post_drawing_id;
				$post_cell['row_id'] = (array_key_exists($row_num, $rowmap) ? $rowmap[$row_num] : -1);
				$post_cell['col_id'] = (array_key_exists($col_num, $colmap) ? $colmap[$col_num] : -1);
				$post_cell['content'] = dv($cell->content);
				$post_cell['href'] = dv($cell->href);
				$post_cell['legend'] = dv($cell->legend);
				$post_cell['course_subject'] = dv($cell->course_subject);
				$post_cell['course_number'] = dv($cell->course_number);
				$post_cell['course_title'] = dv($cell->course_title);
				$DB->Insert('post_cell', $post_cell);
			}
		}

		return $post_drawing_id;
	}

	public function __get($key)
	{
		// some predefined variables
		switch( $key )
		{
			case 'numCols':
				return count($this->_cols);

			case 'totalRows':
				return count($this->_rows) + 2;
				
			case 'totalCols':
				return count($this->_cols) + 3;
				
			case 'footerCols':
				return $this->totalCols - 2;
			
			case 'schoolName':
				return $this->_school_name;
			
			case 'school_id':
				return $this->_school_id;
			
			case 'school_abbr':
				return $this->_school_abbr;
				
			case 'drawingName':
				return $this->_drawing['name'];
			
			case 'type':
				return $this->_type;
		
			case 'name':
				return $this->_name;
	
			case 'rows':
				$rows = $this->_rows;
				foreach( $rows as $i=>$r )
				{
					$rows[$i]['rowName'] = $this->_rowName($i);
					$cellCount = 0;
					foreach( $this->_cells[$i] as $cell )
					{
						if( $this->_cellHasContent($cell) ) $cellCount++;
					}
					$rows[$i]['cellCount'] = $cellCount;
				}
				return $rows;

			default:
				throw new Exception('Invalid key: '.$key);
		}
	}
	
	public function __set($key, $val)
	{
		global $DB;

		switch( $key )
		{
			case 'school_id':
				$this->_school_id = $val;
				$this->_school_name = $DB->GetValue('school_name', 'schools', $val);
				$this->_school_abbr = $DB->GetValue('school_abbr', 'schools', $val);
				break;

			case 'footer_link':
				$this->_footer_link = $val;
				break;
			
			case 'footer_text':
				$this->_footer_text = $val;
				break;

			case 'sidebar_right':
				$this->_sidebar_right = $val;
				break;

			case 'type':
				$this->_type = $val;
				break;

			case 'name':
				$this->_name = $val;
				break;
			
			case 'code':
				$this->_code = $val;
				break;
			
			case 'skillset_id':
				$this->_skillset_id = $val;
				break;

			default:
				echo '<pre>';
				throw new Exception('Invalid key: '.$key);
		}
	}

	public function display()
	{
		if( !is_array($this->_cells) )
		{
			echo '<div class="error">This chart has no data</div>';
			return FALSE;
		}
		
		echo '<table border="1" class="post_chart">', "\n";
		$this->_printHeaderRow();

		foreach( $this->_cells as $rowNum=>$row )
		{
			echo '<tr>', "\n";
			echo '<td class="post_head_row post_head" id="post_row_'.$this->_rows[$rowNum]['id'].'">' . $this->_rowName($rowNum) . '</td>', "\n";
			foreach( $row as $cell )
			{
				list($titleTag, $background) = $this->_gatherLegend($cell->legend);

				// Write the cell to the page
				echo '<td class="post_cell" style="' . $background . '"><div id="post_cell_' . $cell->id . '"' . $titleTag . ' class="post_draggable">' . $this->_cellContent($cell) . '</div></td>', "\n";
			}
			echo '</tr>', "\n";
		}
		echo '<tr>', "\n";

		echo '<td id="post_footer_' . $this->_id . '" class="post_footer" colspan="' . $this->footerCols . '">'
			. ($this->_footer_link?'<a href="'.$this->_footer_link.'">':'')
			. $this->_footer_text
			. ($this->_footer_link?'</a>':'')
			. '</td>', "\n";
		echo '</tr>', "\n";

		// Draw the legend if it exists
		if(count($this->_knownLegend) > 0)
		{
				echo '<tr>', "\n";
				echo '<td width="' . ((($this->footerCols - 1) * 120) + 18) . '" colspan="' . ($this->footerCols + 2) . '" style="padding: 4px 0;">', "\n";

				foreach($this->_knownLegend as $id=>$text)
						echo '<div style="float: left;"><img src="/c/images/legend/b' . $id . '.png" alt="' . $text . '" style="float: left;" /><div style="float: left; padding-top: 3px;"> = ' . $text . ' &nbsp;&nbsp;</div></div>', "\n";
				echo '<div style="clear: both;"></div>', "\n";

				echo '</td>', "\n";
				echo '</tr>', "\n";
		}//if (drawing a legend of characters)

		echo '</table>', "\n";
	}

	public function displayMini()
	{
		echo '<table class="post_chart_mini">', "\n";
		$this->_printHeaderRowMini();

		if( is_array($this->_cells) )
		foreach( $this->_cells as $rowNum=>$row )
		{
			echo '<tr>', "\n";
			echo '<td class="post_head_row post_mini_full">'. $this->_rowNameMini($rowNum) . '</td>', "\n";
			foreach( $row as $cell )
			{
				echo '<td class="post_cell'.($this->_cellHasContent($cell)?' post_mini_full':'').'"><div id="post_cell_' . $cell->id . '"></div></td>', "\n";
			}
			echo '</tr>', "\n";
		}
		echo '<tr>', "\n";

		echo '<td id="post_footer_' . $this->_id . '" class="post_footer'.($this->_footer_text?' post_mini_full':'').'" colspan="' . $this->footerCols . '"></td>', "\n";
		echo '</tr>', "\n";
		echo '</table>', "\n";
	}
	
}


class POSTChart_HS extends POSTChart
{
	protected $_type = "HS";

	protected function _cellContent(&$cell)
	{
		// Is there a link?
		$link = ($cell->href != '');

		// Draw the item inside the post_cell
		return ($link?'<a href="' . $cell->href . '">':'') . (($cell->content)?htmlentities($cell->content):'') . ($link?'</a>':'');
	}
	
	protected function _cellHasContent(&$cell)
	{
		return ($cell->content != '');
	}
	
	protected function _printHeaderRow()
	{
		echo '<tr>', "\n";
			echo '<td class="post_sidebar_left" rowspan="' . ($this->totalRows+1) . '"></td>', "\n";
			echo '<th class="post_head_main post_head post_head_noClick" colspan="' . (count($this->_cols)+1) . '">' . $this->schoolName . '</th>', "\n";
			echo '<td id="postsidebarright_'.$this->_id.'" class="post_sidebar_right" rowspan="' . ($this->totalRows+1) . '">' . $this->verticalText($this->_sidebar_right) . '</td>', "\n";
		echo '</tr>', "\n";
		echo '<tr>', "\n";
			echo '<th class="post_head_xy post_head">Grade</th>', "\n";
			foreach( $this->_cols as $col )
			{
				echo '<th id="post_header_' . $col['id'] . '" class="post_head_main post_head">' . $col['title'] . '</th>', "\n";
			}
		echo '</tr>', "\n";
	}

	protected function _printHeaderRowMini()
	{
		echo '<tr>', "\n";
			echo '<td class="post_sidebar_left post_mini_full" rowspan="' . $this->totalRows . '"></td>', "\n";
			echo '<th class="post_head_xy post_head post_mini_full"></th>', "\n";
			foreach( $this->_cols as $col )
			{
				echo '<th id="post_header_' . $col['id'] . '" class="post_head_main post_head'.($col['title']?' post_mini_full':'').'"></th>', "\n";
			}
			echo '<td  class="post_sidebar_right post_mini_full" rowspan="' . $this->totalRows . '"></td>', "\n";
		echo '</tr>', "\n";
	}
	
	public function rowNameFromData(&$row)
	{
		switch( $row['row_type'] )
		{
			case 'term':
				return $row['row_year'];
			case 'electives':
				return 'Electives';
			default:
				return '';
		}
	}

	protected function _rowNameMini($num)
	{
		return $this->_rowName($num);
	}

	protected function _createEmptyChart()
	{
		global $DB;

		for( $i=9; $i<=12; $i++ )
		{
			$row = array();
			$row['row_type'] = 'term';
			$row['row_year'] = $i;
			$row['row_term'] = '';
			$this->_rows[] = $row;
		}

		// copy the default columns to this drawing
		$cols = $DB->MultiQuery('SELECT * FROM post_default_col WHERE school_id='.$this->_school_id.' ORDER BY num');
		foreach( $cols as $c )
		{
			$col = array();
			$col['title'] = $c['title'];
			$col['num'] = $c['num'];
			$this->_cols[] = $col;
		}
	}
}


class POSTChart_CC extends POSTChart
{
	protected $_type = "CC";

	protected function _cellContent(&$cell)
	{
		if( $cell->course_subject )
		{
			return $cell->course_subject . ' ' . $cell->course_number . '<br />' . $cell->course_title;
			return '<a href="#">' . $cell->course_subject . ' ' . $cell->course_number . '<br />' . $cell->course_title . '</a>';
		}
		else
		{
			// Is there a link?
			$link = ($cell->href != '');
	
			// Draw the item inside the post_cell
			return ($link?'<a href="' . $cell->href . '">':'') . (($cell->content)?htmlentities($cell->content):'') . ($link?'</a>':'');
		}
	}
	
	protected function _cellHasContent(&$cell)
	{
		return $cell->content != '' || $cell->course_subject != '';		
	}

	protected function _printHeaderRow()
	{
		echo '<tr>', "\n";
			echo '<td class="post_sidebar_left" valign="middle" rowspan="' . $this->totalRows . '"></td>', "\n";
			echo '<th class="post_head_main post_head post_head_noClick" colspan="' . (count($this->_cols)+1) . '">' . $this->schoolName . '</th>', "\n";
			echo '<td id="postsidebarright_'.$this->_id.'" class="post_sidebar_right" valign="middle" rowspan="' . $this->totalRows . '">' . $this->verticalText($this->_sidebar_right) . '</td>', "\n";
		echo '</tr>', "\n";
	}

	protected function _printHeaderRowMini()
	{
		echo '<tr>', "\n";
			echo '<td class="post_sidebar_left post_mini_full" valign="middle" rowspan="' . $this->totalRows . '"></td>', "\n";
			echo '<th class="post_head_xy post_head post_mini_full"></th>', "\n";
			echo '<th class="post_head_main post_head post_mini_full" colspan="' . count($this->_cols) . '"></th>', "\n";
			echo '<td class="post_sidebar_right post_mini_full" valign="middle" rowspan="' . $this->totalRows . '"></td>', "\n";
		echo '</tr>', "\n";
	}


	public function rowNameFromData(&$row)
	{
		$terms['F'] = 'Fall';
		$terms['W'] = 'Winter';
		$terms['S'] = 'Spring';
		$terms['U'] = 'Summer';
		$terms['M'] = 'Summer';

		switch( $row['row_type'] )
		{
			case 'term':
				return '<nobr>' . ordinalize($row['row_year'], true) . ' Yr</nobr><br />' . $terms[$row['row_term']];

			case 'prereq':
				return 'Prereqs';
			
			case 'electives':
				return 'Electives';
			
			case 'unlabeled':
				return '';

			default:
				return '';
		}
	}

	protected function _rowNameMini($num)
	{
		$row = $this->_rows[$num];
		switch( $row['row_type'] )
		{
			case 'term':
				return ($row['row_year']) . $row['row_term'];

			case 'prereq':
				return 'P';
			
			case 'electives':
				return 'E';
			
			case 'unlabeled':
				return '';

			default:
				return '';
		}
	}
	
	protected function _createEmptyChart()
	{
		for( $i=1; $i<=6; $i++ )
		{
			$row = array();
			$row['row_type'] = 'term';
			$row['row_year'] = floor(($i-1) / 3) + 1;
			$row['row_term'] = (($i-1) % 3) + 2;
			$this->_rows[] = $row;
			
			$col = array();
			$col['title'] = '';
			$col['num'] = $i;
			$this->_cols[] = $col;
		}
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
		if( is_array($this->_data) && array_key_exists($key, $this->_data) )
			return $this->_data[$key];
		else
			return NULL;
	}
}

?>
