<?php 
class Localize_Strings extends Localize
{
	public function __construct()
	{
		$this->add('drawing head post 1', 'PROGRAM');
		$this->add('drawing head post 2', 'OF STUDY');
		$this->add('drawing head pathways 1', 'CAREER');
		$this->add('drawing head pathways 2', 'PATHWAYS');
		$this->add('school state abbr', 'WA');
		$this->add('post row type', 'quarter');
		$this->add('skillset name', 'Career Cluster');
		$this->add('program name label', 'Career Cluster Pathway');
		$this->add('show program name for post', TRUE);
		$this->add('google analytics drawings', '');
	}
	
	public function term_name($row)
	{
		return ordinalize($row['row_qtr'], true) . ' Qtr';
	}
	
	public function term_name_short(&$row)
	{
		return $row['row_qtr'];
	}
}
?>