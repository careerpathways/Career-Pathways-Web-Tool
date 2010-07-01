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
	}

	public function term_name(&$row)
	{
		return '<nobr>' . ordinalize($row['row_year'], true) . ' Yr</nobr><br />' . $terms[$row['row_term']];
	}
	
	public function term_name_short(&$row)
	{
		return $row['row_year'] . $row['row_term'];
	}
}
?>