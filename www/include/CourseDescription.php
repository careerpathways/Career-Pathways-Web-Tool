<?php
class CourseDescription
{
	private $_school_id;
	private $_subject;
	private $_number;
	
	
	public function __construct($school_id, $subject, $number)
	{
		$this->_school_id = $school_id;
		$this->_subject = $subject;
		$this->_number = $number;		
	}
	
	/**
	 * Return the description of this course
	 */
	public function get()
	{
		// eventually cache and return from the database here
		return $this->_fetch();
	}
	
	private function _fetch()
	{
		$school = db()->SingleQuery('SELECT * FROM schools WHERE id = ' . intval($this->_school_id));
		
		if($school['course_description_url'] == '')
			return FALSE;
		
		$url = str_replace(array(
			'%subject',
			'%number'
		), array(
			urlencode($this->_subject),
			urlencode($this->_number)
		), $school['course_description_url']);

		$xml = file_get_contents($url);

		$xml = new SimpleXMLElement($xml);

		$course = array();
		
		$courseAttrs = $xml->course->attributes();
		
		$links = array();
		foreach($xml->course->link as $l)
		{
			$a = $l->attributes();
			$links[(string)$a['type']] = (string)$a['url'];
		}
		
		$prereqs = array();
		foreach($xml->course->prereq as $p)
		{
			if(property_exists($p, 'sub'))
				$prereqs[] = array('subject'=>(string)$p->sub, 'num'=>(string)$p->num);
			elseif(property_exists($p, 'text'))
				$prereqs[] = array('text'=>(string)$p->text);
		}
		
		$course['subject'] = (string)$courseAttrs['sub'];
		$course['number'] = (string)$courseAttrs['num'];
		$course['credits'] = (string)$courseAttrs['credits'];
		$course['description'] = (string)$xml->course->description;
		$course['links'] = $links;
		$course['prereqs'] = $prereqs;
		
		ob_start();
?>
		<div>
			<div style="font-size: 18pt;"><?=$course['subject'] . ' ' . $course['number']?></div>
			<div style=""><?=$course['credits']?> credits</div>
			<div style=""><?=$course['description']?></div>
		</div>
<?php 
		$course['html'] = trim(ob_get_clean());

		return $course;
	}
}
?>