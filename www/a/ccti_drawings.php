<?php
chdir("..");
include("inc.php");

ModuleInit('ccti_drawings');


PrintHeader();

$time_start = microtime();
$memory_start = memory_get_usage();



$ccti = new CCTI_Drawing(1);

echo '<h2>'.$ccti->name.'</h2>';

foreach( $ccti->programs as $program )
{
	echo '<table class="ccti_program">';

	if( $program->header != '' )
	{
		echo '<tr>';
			echo '<td rowspan="'.$program->total_rows.'" class="ccti_program_headleft">';
				echo '<img src="/files/cctiv/'.$program->school_name.'.png">';
			echo '</td>';
			echo '<td colspan="'.($program->content_cols+1).'" class="ccti_program_header">';
				echo $program->header;
			echo '</td>';
			echo '<td rowspan="'.$program->total_rows.'" class="ccti_program_headright">';
				echo '<img src="/files/cctiv/'.$program->completes_with.'.png">';
			echo '</td>';
		echo '</tr>';
	}

	foreach( $program->sections as $snum=>$section )
	{
		if( $section->header != '' )
		{
			echo '<tr>';
				if( $snum == 0 && $program->header == '' )
				{
					echo '<td rowspan="'.$program->total_rows.'" class="ccti_program_headleft">';
						echo '<img src="/files/cctiv/'.$program->school_name.'.png">';
					echo '</td>';
				}

				echo '<td class="ccti_xy_header">'.($section->label_xy->text?$section->label_xy->text:'&nbsp;').'</td>';
				echo '<td colspan="'.($program->content_cols-$program->show_occ_titles).'" class="ccti_header">'.$section->header.'</td>';
				if( $snum == 0 && $program->show_occ_titles )
				{
					echo '<td class="ccti_occu_head">List occupational titles here</td>';
				}

				if( $snum == 0 && $program->header == '' )
				{
					echo '<td rowspan="'.$program->total_rows.'" class="ccti_program_headright">';
						echo '<img src="/files/cctiv/'.$program->completes_with.'.png">';
					echo '</td>';
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
					echo '<td rowspan="'.$program->total_rows.'" class="ccti_program_headleft">';
						echo '<img src="/files/cctiv/'.$program->school_name.'.png">';
					echo '</td>';
				}

				if( $lxi == 0 && $i == 0 && $section->header == '' )
				{
					echo '<td rowspan="'.count($section->labels_x).'" class="ccti_xy_header">'.($section->label_xy->text?$section->label_xy->text:'.&nbsp;').'</td>';
				}

				if( array_key_exists($i, $lx) )
				{
					$label = $lx[$i];
					echo '<td colspan="'.$label->colspan.'" class="ccti_col_header">'.$label->text.'</td>';
					$i += $label->colspan;
				}
				else
				{
					echo '<td class="ccti_col_header">&nbsp;</td>';
					$i++;
				}

				if( $snum == 0 && $lxi == 0 && $i >= ($program->content_cols-$program->show_occ_titles) && $section->header == '' && $program->header == '' )
				{
					echo '<td rowspan="'.$program->total_rows.'" class="ccti_program_headright">';
						echo '<img src="/files/cctiv/'.$program->completes_with.'.png">';
					echo '</td>';
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
						if( array_key_exists($i, $section->content) && array_key_exists($j, $section->content[$i]) )
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
							echo '<td class="ccti_content" width="'.round(1 / $program->num_columns * $content_width).'">&nbsp;</td>';
						}
					}
				}
				if( $snum == 0 && $program->show_occ_titles )
				{
					echo '<td rowspan="'.($program->content_rows-1).'" width="100" class="ccti_content">&nbsp;</td>';
				}
			echo '</tr>';
		}

	}

	echo '</table>';
	echo '<br>';
}

/*
echo '<pre>';
print_r($ccti);
echo '</pre>';
*/

/*
$time_end = microtime();
$memory_end = memory_get_usage();
echo round(($time_end - $time_start)*1000).' milliseconds<br>';
echo round(($memory_end - $memory_start)/1024).' memory difference<br>';
*/


PrintFooter();

?>