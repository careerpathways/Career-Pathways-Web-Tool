<?php

//************************
// $MONTHS, $MONTHS_SHORT
//
// Global array containing
// indexed array
$MONTHS = Array(1=>"January","February","March",
                   "April","May","June","July",
                   "August","September","October",
                   "November","December");
$MONTHS_SHORT = Array(1=>"Jan","Feb","Mar","Apr",
                         "May","Jun","Jul","Aug",
                         "Sep","Oct","Nov","Dec");
$DAYS_OF_WEEK = Array(0=>"Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
$DAYS = Array(1=>1,2,3,4,5,6,7,8,9,10,11,12,13,14,
				15,16,17,18,19,20,21,22,23,24,25,
				26,27,28,29,30,31);
//************************


function WeekOf( $Date ) {
	$DAYS = array("Sat","Fri","Thu","Wed","Tue","Mon","Sun");

	while(date("D",$Date)!="Sun") {
		$Date = strtotime("-1 days",$Date);
	}

	$WeekStart = $Date;
	$WeekEnd = strtotime("+1 week -1 day", $WeekStart);

	return array($WeekStart, $WeekEnd);
}


class Time {
	// times are stored in two parts: hours and minutes
	var $hours; // public
	var $minutes; // public

	function hours_ampm() {
		if( $this->hours == 0 ) {
			$hours = 12;
			$ampm = "am";
		} elseif( $this->hours < 12 ) {
			$hours = $this->hours;
			$ampm = "am";
		} elseif( $this->hours == 12 ) {
			$hours = 12;
			$ampm = "pm";
		} elseif( $this->hours > 12 ) {
			$hours = $this->hours % 12;
			$ampm = "pm";
		}
		return Array('hours'=>$hours, 'ampm'=>$ampm);
	}

	function compare($time) { // public
		if( $this->hours > $time->hours ) {
			// this hours are bigger, return 1
			return 1;
		} elseif( $this->hours < $time->hours ) {
			// this hours are smaller, return -1
			return -1;
		} else {
			// hours are the same, compare minutes
			if( $this->minutes > $time->minutes ) {
				return 1;
			} elseif( $this->minutes < $time->minutes ) {
				return -1;
			} else {
				// hours and minutes are the same, return 0
				return 0;
			}
		}
	}

	function equals($time) { // public
		if( $this->hours == $time->hours && $this->minutes == $time->minutes ) {
			return true;
		} else {
			return false;
		}
	}

	function add($time) { // public
		$hours = $this->hours + $time->hours;
		$minutes = $this->minutes + $time->minutes;

		if( $minutes >= 60 ) {
			$hours += floor($minutes/60);
			$minutes = $minutes % 60;
		}
		return new Time($hours, $minutes);
	}

	function subtract($time) { // public
		$hours = $this->hours - $time->hours;
		$minutes = $this->minutes - $time->minutes;

		if( $minutes < 0 ) {
			$hours -= ceil(abs($minutes/60));
			$minutes = ($minutes % 60) + 60;
		}
		return new Time($hours, $minutes);
	}

	function divide($time) { // public
		// returns a float

		// convert everything to minutes
		$this_minutes = ($this->hours * 60) + $this->minutes;
		$time_minutes = ($time->hours * 60) + $time->minutes;

		// do the division
		return $this_minutes / $time_minutes;
	}

	function ceil() { // public
		if( $this->minutes > 0 ) {
			return new Time($this->hours + 1, 0);
		} else {
			return new Time($this->hours, 0);
		}
	}

	function floor() { // public
		return new Time($this->hours, 0);
	}

	function round_down($minutes) { // public
		return new Time($this->hours, floor($this->minutes/$minutes)*$minutes );
	}

	function round_up($minutes) { // public
		return new Time($this->hours, ceil($this->minutes/$minutes)*$minutes );
	}

	function Time($str="00:00",$minutes=0) { // constructor
		if( !is_numeric($str) ) {
			$hours = substr($str,0,2);
			$minutes = substr($str,3,2);
		} else {
			if( floor($str) != $str ) {     // check for hours entered like 2.5 for 2:30
				$hours = floor($str);
				// :30   = (2.5  - 2)      * 60;
				$minutes = ($str - $hours) * 60;
			} else {
				$hours = $str;
			}
		}

		if( $minutes >= 60 ) {
			$hours += floor($minutes/60);
			$minutes = $minutes % 60;
		} elseif( $minutes < 0 ) {
			$hours -= ceil(abs($minutes/60));
			$minutes = ($minutes % 60) + 60;
		}
		$this->hours = $hours;
		$this->minutes = $minutes;
	}

	function to_string($twelve_hour=false, $show_ampm=false) { // public
		if( $twelve_hour ) {
			if( $this->hours != 12 ) {
				$hours = ($this->hours%12);
				$ampm = ($show_ampm?($this->hours > 12?"pm":"am"):"");
			} else {
				$hours = $this->hours;
				$ampm = ($show_ampm?"pm":"");
			}
			return sprintf("%d:%02d",$hours,$this->minutes).$ampm;
		} else {
			return sprintf("%02d:%02d",$this->hours,$this->minutes);
		}
	}
}


class Date {

	var $year;
	var $month;
	var $day;

	function Date($timestamp) {
		$this->year = date("Y",$timestamp);
		$this->month = date("m",$timestamp);
		$this->day = date("d",$timestamp);
	}

	function addMonth($num) {
		$this->month += $num;
		while( $this->month > 12 ) {
			$this->year  += 1;
			$this->month -= 12;
		}
	}

	function less_than($date) {
		return $this->toTimestamp() < $date->toTimestamp();
	}
	function less_than_e($date) {
		return $this->toTimestamp() <= $date->toTimestamp();
	}

	function toTimestamp() {
		return mktime(0,0,0,$this->month,$this->day,$this->year);
	}

	function toString($format="Y-m-d") {
		return date($format, $this->toTimestamp());
	}

}


function PrintRelativeDateString($week, $day) {
global $DAYS_OF_WEEK;
	// week should be 1,2,3,4,-2,-1
	// day should be 1..7 1=Sunday
	switch($week) {
		case 1: $week_str = "first"; break;
		case 2: $week_str = "second"; break;
		case 3: $week_str = "third"; break;
		case 4: $week_str = "fourth"; break;
		case -2: $week_str = "second to last"; break;
		case -1: $week_str = "last"; break;
		default: $week_str = ""; break;
	}
	return $week_str." ".$DAYS_OF_WEEK[$day];
}



?>