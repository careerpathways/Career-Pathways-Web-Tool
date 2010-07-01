<?php 
class Localize
{
	protected $_strings = array(); 

	public function get($str)
	{
		if(array_key_exists($str, $this->_strings))
			return $this->_strings[$str];
		else
			return '[' . $str . ']';
	}
	
	protected function add($str, $trans)
	{
		$this->_strings[$str] = $trans;
	}
}
?>