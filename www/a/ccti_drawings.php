<?php
chdir("..");
include("inc.php");

ModuleInit('ccti_drawings');


PrintHeader();

$time_start = microtime();
$memory_start = memory_get_usage();


$drawing_id = Request('id');
$drawing_id = 1; // debug
CCTI_check_permission($drawing_id);


$ccti = new CCTI_Drawing($drawing_id);

echo '<script type="text/javascript" src="/c/cctiedit.js"></script>';
echo "\n";
?>
<script type="text/javascript">
	CCTI.drawing_id = <?= $drawing_id ?>;
</script>

<link rel="stylesheet" href="/common/jquery/jquery-treeview/jquery.treeview.css" />

<script src="/common/jquery-1.2.6.pack.js" type="text/javascript"></script>
<script src="/common/jquery/jquery-treeview/lib/jquery.cookie.js" type="text/javascript"></script>
<script src="/common/jquery/jquery-treeview/jquery.treeview.js" type="text/javascript"></script>

<script type="text/javascript">
		$(function() {
			$("#tree").treeview({
				collapsed: true,
				animated: "medium",
				control:"#sidetreecontrol",
				persist: "location"
			});
		})
</script>

<?php

echo '<h2>'.$ccti->name.'</h2>';
echo '<br>';

foreach( $ccti->programs as $pnum=>$program )
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
						if( $section->content[$i]->offsetExists($j) )
						{
							echo '<td onclick="CCTI.editContent(this, '.$pnum.','.$snum.','.$i.','.$j.')" colspan="'.$section->content[$i][$j]->colspan.'" width="'.round($section->content[$i][$j]->colspan / $program->num_columns * $content_width).'" class="ccti_content">';
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
							echo '<td onclick="CCTI.editContent(this, '.$pnum.','.$snum.','.$i.','.$j.')" class="ccti_content" width="'.round(1 / $program->num_columns * $content_width).'">';
							echo '&nbsp;';
							echo '</td>';
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

echo '<h3>Hierarchy View</h3>';

echo '<ul id="tree">';
foreach( $ccti->programs as $pnum=>$program )
{
	echo '<li>Program: '.($program->header?$program->header:'[Untitled]');
	echo '<ul>';
	foreach( $program->sections as $snum=>$section )
	{
		echo '<li>Section: '.($section->header?$section->header:'[Untitled]');
		echo '<ul>';
		for( $i=0; $i<$section->num_rows; $i++ )
		{
			echo '<li>Row: ';
				if( array_key_exists($i, $section->labels_y) )
					echo $section->labels_y[$i][0]->text;
				else
					echo '[Untitled]';
			echo '<ul>';
			for( $j=0; $j<$program->num_columns; $j++ )
			{
				echo '<li>Cell: '.$section->content[$i][$j]->text.'</li>';
			}
			echo '</ul>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</li>';
	}
	echo '</ul>';
	echo '</li>';
}
echo '</ul>';

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