<?php
chdir("..");
include("inc.php");


//PrintHeader();
$drawing_id = 0;

if( Request('page') == 'published' ) {
	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, d.id
		FROM ccti_drawing_main AS main, ccti_drawings AS d, schools 
		WHERE d.parent_id = main.id
			AND main.school_id = schools.id
			AND published = 1
			AND deleted = 0
			AND code="'.Request('d').'"');
	if( is_array($drawing) ) {
		CCTI_check_permission($drawing['id']);
		$drawing_id = $drawing['id'];
	}
} elseif( Request('page') == 'version') {

	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, d.id
		FROM ccti_drawing_main AS main, ccti_drawings AS d, schools
		WHERE d.parent_id = main.id
			AND main.school_id = schools.id
			AND version_num = '.Request('v').'
			AND deleted = 0
			AND code="'.Request('d').'"');
	if( is_array($drawing) ) {
		CCTI_check_permission($drawing['id']);
		$drawing_id = $drawing['id'];
	}
}

if( $drawing_id == 0 ) {
	die();
}

$ccti = new CCTI_Drawing($drawing_id);

?>
<link rel="stylesheet" type="text/css" href="/c/ctstyle.css" />
<?php

echo '<div id="ccti_title"><img src="/files/titles/'.base64_encode($drawing['school_abbr']).'/'.base64_encode($ccti->name).'.png"></div>';

$pnum = 0;
foreach( $ccti->programs as $pid=>$program )
{
	echo '<table class="ccti_program" id="ccti_program'.$pid.'">';

	if( $program->header != '' )
	{
		echo '<tr>';
			img_head($program->headleft, 'left', $pid);
			echo '<td colspan="'.($program->content_cols+1).'" class="ccti_program_header">';
				echo $program->header;
			echo '</td>';
			img_head($program->headright, 'right', $pid);
		echo '</tr>';
	}

	$snum = 0;
	foreach( $program->sections as $sid=>$section )
	{
		if( $section->header != '' )
		{
			echo '<tr>';
				if( $snum == 0 && $program->header == '' )
				{
					img_head($program->headleft, 'left', $pid);
				}

				echo '<td class="ccti_xy_header">'.($section->label_xy->text?$section->label_xy->text:'&nbsp;').'</td>';
				echo '<td colspan="'.($program->content_cols-$program->show_occ_titles).'" class="ccti_header">'.$section->header.'</td>';
				if( $snum == 0 && $program->show_occ_titles )
				{
					echo '<td class="ccti_occu_head">List occupational titles here</td>';
				}

				if( $snum == 0 && $program->header == '' )
				{
					img_head($program->headright, 'right', $pid);
				}
			echo '</tr>';
		}

		foreach( $section->labels_x as $lxi=>$lx )  // multiple rows of x labels supported
		{
			echo '<tr>';
			for( $i=0; $i<($program->content_cols-$program->show_occ_titles); $i )  // don't increment here
			{
				if( $snum == 0 && $lxi == 0 && $i == 0 && $section->header == '' && $program->header == '' )
				{
					img_head($program->headleft, 'left', $pid);
				}

				if( $lxi == 0 && $i == 0 && $section->header == '' )
				{
					echo '<td rowspan="'.count($section->labels_x).'" class="ccti_xy_header">'.($section->label_xy->text?$section->label_xy->text:'.&nbsp;').'</td>';
				}

				if( array_key_exists($i, $lx) )
				{
					$label = $lx[$i];
					echo '<td colspan="'.$label->colspan.'" class="ccti_col_header">'.($label->text).'</td>';
					$i += $label->colspan;
				}
				else
				{
					echo '<td class="ccti_col_header">&nbsp;</td>';
					$i++;
				}

				if( $snum == 0 && $lxi == 0 && $i >= ($program->content_cols-$program->show_occ_titles) && $section->header == '' && $program->header == '' )
				{
					img_head($program->headright, 'right', $pid);
				}
			}

			echo '</tr>';
		}


		$content_width = 700;
		if( $program->show_occ_titles ) $content_width -= 100;

		$cells_spanned = array();
		for( $i=0; $i<$section->num_rows; $i++ )
		{
			echo '<tr>';
				echo '<td class="ccti_row_header">';
					if( array_key_exists($i, $section->labels_y) )
						echo $section->labels_y[$i][0]->text;
					else
						echo '&nbsp;';
				echo '</td>';
				for( $j=0; $j<$program->num_columns; $j++ )
				{
					if( !in_array($i.','.$j, $cells_spanned) )
					{
						if( $section->content[$i]->offsetExists($j) )
						{
							echo '<td colspan="'.$section->content[$i][$j]->colspan.'" width="'.round($section->content[$i][$j]->colspan / $program->num_columns * $content_width).'" class="ccti_content">';
							echo $section->content[$i][$j]->text;
							echo '</td>';
							if( $section->content[$i][$j]->colspan > 1 )
							{
								for( $ci=0; $ci<$section->content[$i][$j]->colspan; $ci++ )
								{
									$cells_spanned[] = $i.','.($j+$ci);
								}
							}
						}
						else
						{
							echo '<td class="ccti_content" width="'.round(1 / $program->num_columns * $content_width).'">';
							echo '&nbsp;';
							echo '</td>';
						}
					}
				}
				if( $snum == 0 && $i == 0 && $program->show_occ_titles )
				{
					echo '<td rowspan="'.($program->content_rows-1).'" width="100" class="ccti_content occ">';
					echo $program->occ_titles==""?"&nbsp;":$program->occ_titles;
					echo '</td>';
				}
			echo '</tr>';
		}

		$snum++;
	}

	echo '<tr>';
		echo '<td colspan="'.($program->num_columns+1+$program->show_occ_titles).'">';
		echo ($program->footer?$program->footer:'&nbsp;');
		echo '</td>';
	echo '</tr>';

	echo '</table>';
	echo '<br>';
	
	$pnum++;
}

function img_head($text, $type, $pid) 
{
	global $program;
	echo '<td rowspan="'.$program->total_rows.'" class="ccti_program_head'.$type.'">';
		echo '<img src="/files/cctiv/'.base64_encode($text).'.png" alt="'.$text.'">';
	echo '</td>';
}

//PrintFooter();

?>