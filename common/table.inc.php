<?php

class WrappingTable {
	var $cols;  // how many columns to show per row
	var $items = array();  // array of the contents of all the cells
	var $header = array(); // array to print at the top of the table as a header

	var $table_class;
	var $table_style = "border-collapse:collapse;";
	var $table_width = "100%";
	var $table_align = "center";
	var $td_class = "content_cell";
	var $td_style;
	var $td_align = "center";
	var $td_height;

	function Show() {
		if( count($this->items)+count($this->header) > 0 ) {
			$width = ($this->table_width==""?"":"width=\"$this->table_width\"");
			$table_class = ($this->table_class==""?"":"class=\"$this->table_class\"");
			$table_style = ($this->table_style==""?"":"style=\"$this->table_style\"");
			$td_class = ($this->td_class==""?"":"class=\"$this->td_class\"");
			$td_style = ($this->td_style==""?"":"style=\"$this->td_style\"");
			$td_height = ($this->td_height==""?"":"height=\"$this->td_height\"");

			echo "<table $width $table_class $table_style align=\"$this->table_align\" cellspacing=\"0\">\n";

			if( count($this->header) > 0 ) {
				for($i=0; $i<(count($this->header)%$this->cols); $i++ ) {
					$this->header[] = "&nbsp;";
				}
				$i=0;
				foreach( $this->header as $item ) {
					if( $i % $this->cols == 0 ) {
						echo "<tr>\n";
					}

					$td_width = "width=\"".round(100/$this->cols)."%\"";

					echo "\t<td $td_class $td_width $td_style align=\"$this->td_align\">$item</td>\n";

					if( $i % $this->cols == $this->cols-1 ) {
						echo "</tr>\n";
					}

					$i++;
				}

			}
			if( count($this->items) > 0 ) {
				// calculate how many blank cells we will need and add them to the array
				// this evens up the number of elements to fill up all the columns
				for($i=0; $i<(count($this->items)%$this->cols); $i++ ) {
					$this->items[] = "&nbsp;";
				}

				$i=0;
				foreach( $this->items as $item ) {
					if( $i % $this->cols == 0 ) {
						echo "<tr>\n";
					}

					$td_width = "width=\"".round(100/$this->cols)."%\"";

					echo "\t<td $td_class $td_width $td_height $td_style align=\"$this->td_align\">$item</td>\n";

					if( $i % $this->cols == $this->cols-1 ) {
						echo "</tr>\n";
					}

					$i++;
				}

			}

			echo "</table>\n";
		}
	}

	function Output() {
		$this->Show();
	}

	function AddItem($item) {
		$this->items[] = $item;
	}

	function AddHeaderItem($item) {
		$this->header[] = $item;
	}

}


class Chart {
	// values to display as the header of the table
	var $header;   // array("Title","Title2","Title3")
	// contents of the table, an array of arrays
	var $rows;     // array(array("Column 1","Column 2"),array("Column 1","Column 2"))

	var $table_class;
	var $table_style = "border-collapse:collapse;";
	var $table_width = "100%";
	var $table_align = "center";
	var $td_class = "content_cell";
	var $td_header_class = "header_cell";
	var $td_style;
	var $td_header_style;
	var $td_align;
	var $td_height;
	var $td_valign;
	var $a_header_class = "chart_header";


	function SetHeadElement($value) {
		// set header elements in the order you want them shown in the table
		if( is_array($value) ) {
			$this->header = $value;
		} else {
			$this->header[] = $value;
		}
	}

	function AddRow($data) {
		// data is an array
		$next_index = count($this->rows);
		foreach( $data as $cell ) {
			$this->rows[$next_index][] = $cell;
		}
	}

	function Output() {
		if( count($this->rows)+count($this->header) > 0 ) {
			$width = ($this->table_width==""?"":"width=\"$this->table_width\"");
			$table_class = ($this->table_class==""?"":"class=\"$this->table_class\"");
			$table_style = ($this->table_style==""?"":"style=\"$this->table_style\"");
			$td_class = ($this->td_class==""?"":"class=\"$this->td_class\"");
			$td_header_class = ($this->td_header_class==""?"":"class=\"$this->td_header_class\"");
			$td_style = ($this->td_style==""?"":"style=\"$this->td_style\"");
			$td_align = ($this->td_align==""?"":"align=\"$this->td_align\"");
			$td_valign = ($this->td_valign==""?"":"valign=\"$this->td_valign\"");
			$td_header_style = ($this->td_header_style==""?"":"style=\"$this->td_header_style\"");
			$td_height = ($this->td_height==""?"":"height=\"$this->td_height\"");

			echo "<table $width $table_class $table_style align=\"$this->table_align\" cellspacing=\"0\">\n";

			if( count($this->header) > 0 ) {
				echo "<tr>\n";
				foreach( $this->header as $item ) {
					if( is_array($item) ) {
						if( array_key_exists('link',$item) ) {
							$link_1 = "<a href=\"".$item['link']."\" class=\"$this->a_header_class\">";
							$link_2 = "</a>";
						} else {
							$link_1 = $link_2 = "";
						}
						$text = $item['text'];
						if( array_key_exists('width',$item) ) {
							$width = "width=\"".$item['width']."\"";
						} else {
							$width = "";
						}
					} else {
						if( $item == "" ) $item = "&nbsp;";
						$link_1 = $link_2 = "";
						$text = $item;
						$width = "";
					}

					echo "\t<td $td_header_class $td_header_style $td_align $width valign=\"bottom\">";
					echo $link_1;
					echo $text;
					echo $link_2;
					echo "</td>\n";
				}
				echo "</tr>\n";
			}
			if( count($this->rows) > 0 ) {
				foreach( $this->rows as $row ) {
					echo "<tr>\n";
					foreach( $row as $cell ) {
						if( is_array($cell) ) {
							if( $cell['content'] == "" ) $cell['content'] = "&nbsp;";
							if( array_key_exists('width',$cell) ) {
								$td_width = "width=\"".$cell['width']."\"";
							} else {
								$td_width = "";
							}
							if( array_key_exists('align',$cell) ) {
								$td_this_align = 'align="'.$cell['align'].'"';
							} else {
								$td_this_align = $td_align;
							}
							if( array_key_exists('rowspan',$cell) ) {
								$td_this_rowspan = 'rowspan="'.$cell['rowspan'].'"';
							} else {
								$td_this_rowspan = $td_align;
							}
							$content = $cell['content'];
						} else {
							$td_width = "";
							$content = $cell;
							if( $content == "" ) $content = "&nbsp;";
							$td_this_align = $td_align;
							$td_this_rowspan = "";
						}
						echo "\t<td $td_width $td_this_rowspan $td_class $td_height $td_style $td_this_align $td_valign>$content</td>\n";
					}
					echo "</tr>\n";
				}
			}

			echo "</table>\n";
		}
	}

}


?>