<?php
chdir("..");
require_once("inc.php");



$ord['first'] = 1;
$ord['second'] = 2;
$ord['third'] = 3;
$ord['fourth'] = 4;
$ord['fifth'] = 5;
$ord['sixth'] = 6;
$ord['seventh'] = 7;
$ord['eighth'] = 8;
$ord['ninth'] = 9;
$ord['tenth'] = 10;
$ord['eleventh'] = 11;
$ord['twelfth'] = 12;



$goodData = array();


$xmlData = file_get_contents('/web/aaron/tmp/POST Template 2009-01-07. ECE COTTAGE GROVE.X.xml');
$xml = new SimpleXMLElement($xmlData);
$excelXml = ($xml->Worksheet->Table);

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


$rowI = 0;

// Search the array for a row where a cell contains "Grade". That row is the headers.
$hsHeaders = array();

$gradeFound = false;
while( $gradeFound == false && $rowI < count($excelData) )
{
	$row = $excelData[$rowI];
	
	if( array_key_exists(1, $row) && $row[1] == 'Grade' )
	{
		$gradeFound = true;

		for( $j = 2; $j < count($row); $j++ )
		{
			if( trim($row[$j]) != '' ) $hsHeaders[] = $row[$j];
		}

	}
	
	$rowI++;
}

if( $gradeFound == false )
{
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
	
	// in this row, look for the first cell which is just a number
	$cI = 0;
	$hsRowGradeFound = false;
	while( $hsRowGradeFound === false && $cI < count($row) )
	{
		if( is_numeric($row[$cI]) )
		{
			// we found a 9/10/11/12, store that grade
			$hsRowGradeFound = intval($row[$cI]);
		}
		$cI++;
	}

	// $cI points to the first cell of the content
	if( $hsRowGradeFound !== false )
	{
		$hsContentStart = $cI;
		$lastContentStart = $cI;
		for( $i = $hsContentStart; $i < count($row) && $i-$hsContentStart < count($hsHeaders); $i++ )
		{
			// This 'if' shouldn't fire, but just in case...
			if( strtolower($row[$i]) == 'high school diploma' ) $row[$i] = '';
			
			$hsContent[$hsRowGradeFound][] = $row[$i];
		}
	}
	else
	{
		// We're probably dealing with some extra cells after the grade list, which go in the "extra credit" section.
		// We're going to just have to guess about which columns they go in, since we can't know for sure w/o col/rowspan data.
		for( $i = $lastContentStart; $i < count($row) && $i-$hsContentStart < count($hsHeaders); $i++ )
		{
			$hsContent['extra'][] = $row[$i];
		}
		
		// Force the end of the HS section now
		$hsDone = true;
	}

	$rowI++;
}

// At this point, all the HS data has been read in.
$goodData['hsHeaders'] = $hsHeaders;
$goodData['hsContent'] = $hsContent;

#pa($goodData, 'EFE0E0', 'Good Data');


$ccContent = array();  // keys are term numbers, or 'extra'
// Begin loading the Community College data

$currentTerm = 0;
$lastTerm = 0; $lastTerm2 = 0;
$ccI = 0;
for( $rowI; $rowI < count($excelData); $rowI++ )
{
	$row = $excelData[$rowI];
	
	// if the first cell is first/second/etc, set $currentTerm to that number and start reading in data
	foreach( $row as $cell )
	{
		// look for "FIRST TERM", etc
		if( preg_match('/([a-z]+(th|rd|nd|st))\s+term/i', $cell, $match) )
		{
			$lastTerm = $lastTerm2;
			$currentTerm = $ord[strtolower($match[1])];
			if( $currentTerm != 0 ) $lastTerm2 = $currentTerm;
		}
		
		// look for "LIST OF PROGRAM ELECTIVES"
		if( $lastTerm >= 5 && preg_match('/program\s+electives/i', $cell, $match) )
		{
			$currentTerm = 99;
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

		foreach( $row as $cell )
		{
			$cell = trim($cell);
			if( !preg_match('/([a-z]+)\s+term/i', $cell) )
			{
				// try to parse a class subject/number out of it
				if( $prg = preg_match('/^([a-z]{2,4}) ([0-9]{3}[a-z]{0,1}) (.+)$/i', $cell, $match) )
				{
					$goodCell = array(
						'course_subject' => $match[1],
						'course_number' => $match[2],
						'course_title' => $match[3]
					);
					$ccContent[$ccI][$currentTerm][] = $goodCell;
				}
				else
				{
					if( trim($cell)
					   && strpos(strtolower($cell), 'choose one') === false
					   && strpos(strtolower($cell), 'occupational') === false
					   && strpos(strtolower($cell), 'program electives') === false
					  )
					{
						$goodCell = array(
							'content' => $cell
						);
						$ccContent[$ccI][$currentTerm][] = $goodCell;
					}
				}
			}
		}
	}

	
}


$return = array();

$hsDrawing['headers'] = $hsHeaders;
$hsDrawing['content'] = $hsContent;
$hsDrawing['type'] = 'HS';

$return[] = $hsDrawing;

foreach( $ccContent as $cc )
{
	$ccDrawing = array();
	$ccDrawing['headers'] = array_fill(0, count($cc[1]), '');
	$ccDrawing['content'] = $cc;
	$ccDrawing['type'] = 'CC';
	$return[] = $ccDrawing;
}

pa($return);



?>