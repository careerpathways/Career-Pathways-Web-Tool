<?php
chdir("..");
require_once("inc.php");

// give everything a fake ID
$rollingIDs = 0;


$goodData = array();


$xmlData = file_get_contents('/web/aaron/tmp/POST Template 2009-01-07. ECE WILLAMETTE.EX.xml');
$xml = new SimpleXMLElement($xmlData);
$excelXml = ($xml->Worksheet->Table);

d('Beginning import process');

$excelData = array();

// We go through the XML file and put all the cell contents into a 2D array.
// At this point we're losing all information except for text. No formatting is copied.
foreach( $excelXml->Row as $r )
{
	$row = array();
	foreach( $r as $cell )
	{
		$row[] = str_replace("\n", " ", (string)$cell->Data);
	}
	$excelData[] = $row;
}
unset($row);
d('Finished reading XML data into array. '.count($excelData).' rows found.');


$rowI = 0;

// Search the array for a row where a cell contains "Grade". That row is the headers.
d('Beginning High School search');
d('Looking for "Grade"');

$hsHeaders = array();

$gradeFound = false;
while( $gradeFound == false && $rowI < count($excelData) )
{
	$row = $excelData[$rowI];
	
	if( array_key_exists(1, $row) && $row[1] == 'Grade' )
	{
		$gradeFound = true;
		d('Found "Grade" at row '.$rowI);

		for( $j = 2; $j < count($row); $j++ )
		{
			if( trim($row[$j]) != '' )
			{
				$hsHeaders[] = array('title'=>$row[$j], 'id'=>++$rollingIDs);
				d('Found header text: "'.$row[$j].'"');
			}
		}

	}
	
	$rowI++;
}

if( $gradeFound == false )
{
	d('Reached the end of the file and no "Grade" found. Quitting...');
	throw new Exception('I couldn\'t find the beginning of the High School section. Make sure there is a cell named "Grade" at the start of the High School section.');
	return false;
}


// Set up the HS array
$hsContent = array();
$hsContent[9] = array();
$hsContent[10] = array();
$hsContent[11] = array();
$hsContent[12] = array();
$hsContent['extra'] = array();

$hsDone = false;
$lastContentStart = 0;
while( !$hsDone && $rowI < count($excelData) )
{
	$row = $excelData[$rowI];
	d('Beginning row '.$rowI.' with '.count($row).' columns');
	
	// in this row, look for the first cell which is just a number
	$cI = 0;
	$hsRowGradeFound = false;
	while( $hsRowGradeFound === false && $cI < count($row) )
	{
		if( is_numeric($row[$cI]) )
		{
			// we found a 9/10/11/12, store that grade
			$hsRowGradeFound = intval($row[$cI]);
			d('Now starting grade '.$hsRowGradeFound);
		}
		$cI++;
	}

	// $cI points to the first cell of the content
	if( $hsRowGradeFound !== false )
	{
		$hsContentStart = $cI;
		$lastContentStart = $cI;
		$j=0;
		for( $i = $hsContentStart; $i < count($row) && $i-$hsContentStart < count($hsHeaders); $i++ )
		{
			// This 'if' shouldn't fire, but just in case...
			if( strtolower($row[$i]) == 'high school diploma' ) $row[$i] = '';
			
 			$hsContent[$hsRowGradeFound][++$j] = array('id'=>++$rollingIDs, 'content'=>$row[$i], 'row_num'=>$hsRowGradeFound - 8, 'col_num'=>$j);
			#d('Found data for row '.$hsRowGradeFound.' col '.$j);
		}
	}
	else
	{
		// We're probably dealing with some extra cells after the grade list, which go in the "extra credit" section.
		// We're going to just have to guess about which columns they go in, since we can't know for sure without col/rowspan data.
		$j = 0;
		for( $i = $lastContentStart; $i < count($row) && $i-$hsContentStart < count($hsHeaders); $i++ )
		{
			$hsContent['extra'][++$j] = array('id'=>++$rollingIDs, 'content'=>$row[$i], 'row_num'=>$rowI, 'col_num'=>$j);
			#d('Found data for row "extra" col '.$j);
		}
		
		// Force the end of the HS section now
		$hsDone = true;
		d('End of High School section found');
	}

	$rowI++;
}

$extraContent = 0;
foreach( $hsContent['extra'] as $e )
{
	if( $e['content'] != '' ) $extraContent++;
}
if( $extraContent == 0 ) unset($hsContent['extra']);

// At this point, all the HS data has been read in.
$goodData['hsHeaders'] = $hsHeaders;
$goodData['hsContent'] = $hsContent;

#pa($goodData, 'EFE0E0', 'Good Data');



d('Beginning College section');

$ccContent = array();  // keys are term numbers, or 'extra'
// Begin loading the Community College data

d('Will process '.(count($excelData)-$rowI).' rows');

$currentTerm = 0;
$lastTerm = 0; $lastTerm2 = 0;
$ccI = 0;   
for( $rowI; $rowI < count($excelData); $rowI++ )
{
	$row = $excelData[$rowI];
	d('Beginning row '.$rowI.' with '.count($row).' columns');
	
	// if the first cell is first/second/etc, set $currentTerm to that number and start reading in data
	foreach( $row as $cell )
	{
		// look for "FIRST TERM", etc
		if( preg_match('/([a-z]+(th|rd|nd|st))\s+term/i', $cell, $match) )
		{
			$lastTerm = $lastTerm2;
			$currentTerm = deordinalize($match[1]);
			if( $currentTerm != 0 ) $lastTerm2 = $currentTerm;
			d('Found new term: '.$currentTerm);
			$colI = 0;
		}
		
		// look for "LIST OF PROGRAM ELECTIVES"
		if( $lastTerm >= 5 && preg_match('/program\s+electives/i', $cell, $match) )
		{
			$currentTerm = 100;
			$colI = 0;
			d('Found "LIST OF PROGRAM ELECTIVES"');
		}
	}

	// If there's no content in this row, reset currentTerm until we find a new first/second/etc
	if( count($row) == 0 )
	{
		$currentTerm = 0;
	}

	if( $currentTerm > 0 )
	{
		if( $currentTerm < $lastTerm )
		{
			// start a new drawing
			$ccI++;
			$lastTerm = $currentTerm;
		}

		foreach( $row as $i=>$cell )
		{
			$cell = trim($cell);
			if( !preg_match('/([a-z]+)\s+term/i', $cell) )
			{
				// try to parse a class subject/number out of it
				if( $prg = preg_match('/^([a-z]{2,4}) ([0-9]{3}[a-z]{0,2}) (.+)$/i', $cell, $match) )
				{
					$goodCell = array(
						'id' => ++$rollingIDs,
						'course_subject' => $match[1],
						'course_number' => $match[2],
						'course_title' => $match[3],
						'row_num' => $currentTerm,
						'col_num' => ++$colI
					);
					$ccContent[$ccI][$currentTerm][$colI] = $goodCell;
				}
				else
				{
					if( trim($cell)
					   #&& strpos(strtolower($cell), 'choose one') === false
					   && strpos(strtolower($cell), 'occupational') === false
					   && strpos(strtolower($cell), 'program electives') === false
					   && strpos(strtolower($cell), '(continued)') === false
					  )
					{
						$goodCell = array(
							'id' => ++$rollingIDs,
							'content' => $cell,
							'row_num' => $currentTerm,
							'col_num' => ++$colI
						);
						$ccContent[$ccI][$currentTerm][$colI] = $goodCell;
					}
					elseif( $currentTerm != 100 )
					{
						$goodCell = array(
							'id' => ++$rollingIDs,
							'content' => '',
							'row_num' => $currentTerm,
							'col_num' => ++$colI
						);
						$ccContent[$ccI][$currentTerm][$colI] = $goodCell;
					}
				}
			}
		}
	}
	
}



// post-process the college arrays, filling each row out with empty cells
// and wrapping the 100th term data to that max length

foreach( $ccContent as $j=>$cc )
{
	// find the longest row
	
	$max_row = 0;
	foreach( $cc as $term=>$row )
	{
		if( $term < 100 && count($row) > $max_row ) $max_row = count($row);
	}
	
	d('Longest row has '.$max_row.' cells');

	foreach( $cc as $term=>$row )
	{
		if( $term < 100 && count($row) < $max_row )
		{
			// fill out each row until they all have $max_row rows
			for( $i=count($row); $i<$max_row; $i++ )
			{
				$ccContent[$j][$term][] = array('id' => ++$rollingIDs,
							   'content' => '',
							   'row_num' => $term,
							   'col_num' => $i);
			}
		}
		if( $term == 100 )
		{
			// split up this row into multiple rows
			$new_rows = array();
			foreach( $row as $i=>$course )
			{
				d('Distributing extra cell to row '.intval($i/$max_row));
				$new_rows[intval($i / $max_row)][] = $course;
			}

			unset($ccContent[$j][100]);

			foreach( array_reverse($new_rows) as $i=>$row )
			{
				$new_row = array();
				foreach( $row as $k=>$r )
				{
					$new_cell = $r;
					$new_cell['row_num'] = $i + 100;
					$new_row[] = $new_cell;
				}
				$ccContent[$j][$i+100] = $new_row;
			}
		}
	}
}









$return = array();

$hsDrawing['headers'] = $hsHeaders;
$hsDrawing['content'] = $hsContent;
$hsDrawing['type'] = 'HS';
$hsDrawing['drawing'] = array(
	'num_rows'=>count($hsContent),
	'name'=>'Import Preview (HS)',
	'school_name'=>'High School',
	'footer_text'=>'',
	'footer_link'=>'');

$return[] = $hsDrawing;

foreach( $ccContent as $cc )
{
	$ccDrawing = array();
	$ccDrawing['headers'] = array_fill(0, count($cc[1]), '');
	$ccDrawing['content'] = $cc;
	$ccDrawing['type'] = 'CC';

	$ccDrawing['drawing'] = array(
		'num_rows'=>count($cc),
		'name'=>'Import Preview (CC)',
		'school_name'=>'Community College',
		'footer_text'=>'',
		'footer_link'=>'');

	$return[] = $ccDrawing;
}




$page_title = 'Import Tool';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?=$page_title?></title>
	<link rel="stylesheet" type="text/css" href="/c/pstyle.css" />
</head>
<body>
	<div id="post_title">
		<img src="/files/titles/<?=base64_encode('Import Tool')?>/<?=base64_encode($page_title)?>.png" alt="Career POST" />
	</div>
<?php
	include('POSTChart.inc.php');

	$post = POSTChart::createFromArray($return[0]['type'], $return[0]);
	$post->display();
	?><br /><br /><br /><?php
	$post = POSTChart::createFromArray($return[1]['type'], $return[1]);
	$post->display();
	?><br /><br /><br /><?php
	$post = POSTChart::createFromArray($return[2]['type'], $return[2]);
	$post->display();

?>
</body>
</html>
<?php

function d($msg)
{
	// print debugging messages
	// echo date('Y-m-d H:i:s') . ' ' . $msg . "<br />\n";
}

?>