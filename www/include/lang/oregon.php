<?php 
class Localize_Strings extends Localize
{
	public function __construct()
	{
		$this->add('drawing head post 1', 'PLAN');
		$this->add('drawing head post 2', 'OF STUDY');
		$this->add('drawing head pathways 1', 'CAREER');
		$this->add('drawing head pathways 2', 'PATHWAYS');
		$this->add('school state abbr', 'OR');
		$this->add('post row type', 'year/term');
		$this->add('skillset name', 'Oregon Skill Set');
		$this->add('program name label', 'Approved Program Name');
		$this->add('show program name for post', FALSE);
		$this->add('google analytics drawings', 'UA-8726801-2');
	}

	public function term_name(&$row)
	{
		$terms['F'] = 'Fall';
		$terms['W'] = 'Winter';
		$terms['S'] = 'Spring';
		$terms['U'] = 'Summer';
		$terms['M'] = 'Summer';

		return '<nobr>' . ordinalize($row['row_year'], true) . ' Yr</nobr><br />' . $terms[$row['row_term']];
	}
	
	public function term_name_short(&$row)
	{
		return $row['row_year'] . $row['row_term'];
	}
}
?>