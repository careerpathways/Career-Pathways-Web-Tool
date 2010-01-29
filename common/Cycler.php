<?php
/*
 * Copied (mostly) from http://github.com/tylerhall/simple-php-framework/blob/master/includes/class.loop.php
 * 
 * Example:
 * $trClass = new Cycler('light', 'dark');
 * foreach($rows as $row)
 * {
 *    echo '<tr class="' . $trClass . '">';
 * }
 */
class Cycler
{
	private $_index;
	private $_elements;
	private $_numElements;

	public function __construct()
	{
		$this->_index = 0;
		$this->_elements = func_get_args();
		$this->_numElements = func_num_args();
	}

	public function __tostring()
	{
		return (string)$this->get();
	}

	public function get()
	{
		if($this->_numElements == 0)
			return null;

		$val = $this->_elements[$this->_index++];

		$this->_index = $this->_index % $this->_numElements;

		return $val;
	}

	public function rand()
	{
		return $this->_elements[array_rand($this->_elements)];
	}
}
?>