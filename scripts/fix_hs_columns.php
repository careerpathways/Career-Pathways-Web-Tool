#!/usr/bin/php
<?php
include('scriptinc.php');

if( !array_key_exists(1, $argv) )
	die('Usage: '.$argv[0]." [preview|go]\n");

$mode = $argv[1];

$schools = $DB->MultiQuery('SELECT * FROM schools WHERE organization_type="HS"');
foreach( $schools as $s )
{
	$cols = $DB->SingleQuery('SELECT COUNT(1) AS num FROM post_default_col WHERE school_id = ' . $s['id']);
	if( $cols['num'] == 0 )
	{
		echo $s['school_name'] . "\n";

		foreach( array('English', 'Math', 'Science', 'Social Studies', 'Electives', 'Career and Technical Courses', 'Employment') as $num=>$title )
		{
			$data = array();
			$data['school_id'] = $s['id'];
			$data['title'] = $title;
			$data['num'] = $num;
			$DB->Insert('post_default_col', $data);
		}
		
	}
}


?>