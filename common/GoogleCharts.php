<?php

class GoogleCharts
{
	private $_attrs = array();
	
	public function __construct($type)
	{
		$this->cht = $type;
	}
	
	public function __set($key, $val)
	{
		switch( $key )
		{
			// chart data can be passed in as a string, array, or array of arrays
			case 'chd':
				if( is_array($val) )
				{
					if( array_key_exists(0, $val) && is_array($val[0]) )
					{
						$newval = 't:';
						foreach( $val as $i=>$dataset )
						{
							if( $i > 0 )
								$newval .= '|';
							$newval .= implode(',', $dataset);
						}
					}
					else
					{
						$newval = 't:' . implode(',', $val);
					}
				}
				else
				{
					$newval = $val;
				}
				$this->_attrs[$key] = $newval;
				break;
			
			// all others are passed through raw
			default:
				$this->_attrs[$key] = $val;
		}
	}
	
	public function img($w, $h)
	{
		$params = '';
		foreach($this->_attrs as $k=>$v)
		{
			$params .= '&amp;' . $k . '=' . urlencode($v);
		}
		return '<img src="http://chart.apis.google.com/chart?chs=' . $w . 'x' . $h . $params . '" width="' . $w . '" height="' . $h . '" alt="chart" />';
	}
	
	public function simpleEncode($values, $max)
	{
		$simpleEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$chartData = 's:';
		foreach( $values as $v )
		{
			if( $v > 0 ) {
				$chartData .= substr($simpleEncoding, round((strlen($simpleEncoding)-1) * $v / $max), 1);
			}
			else {
				$chartData .= '_';
			}
		}
		return $chartData;
	}

}

?>