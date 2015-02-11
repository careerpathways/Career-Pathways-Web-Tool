<?php

// returns true/false if the request method is post
function PostRequest() {
	return strtolower($_SERVER['REQUEST_METHOD']) == "post";
}

//**************************
// GenerateSelectBoxDB()
//
// $table		the name of the table in the database to grab values from
// $select_name	the name the select box should have in the html
// $value_field	the name of the field to grab keys (select box values) from
// $name_field	the name of the field to grab values (select box names) from
// $sort_field	the name of the field to sort by
// $current		the value of the current (if any) value. this will be selected by default
// $addl        array of key,value pairs to insert before the elt's from the database
//**************************
function GenerateSelectBoxDB($table, $select_name, $value_field, $name_field, $sort_field="", $current="", $addl=array(), $where="") {
global $DB;
	if( $where != "" ) {
		$where = " WHERE ".$where." ";
	}
	if( $sort_field!="" ) {
		$sort_by = " ORDER BY `".$sort_field."`";
	} else {
		$sort_by = "";
	}
	$DB->Query("SELECT `$value_field`, `$name_field` FROM ".$table.$where.$sort_by);
	$str = "<select name=\"".$select_name."\" id=\"".$select_name."\">\n";
	foreach( $addl as $key=>$value ) {
		$str .= "<option value=\"".$key."\">".$value."</option>\n";
	}
	while( $option = $DB->NextRecord() ) {
		$str .= "<option value=\"".$option[$value_field]."\"".($option[$value_field]==$current?" selected":"").">".$option[$name_field]."</option>\n";
	}
	$str .= "</select>\n";
	return $str;
}

function GenerateSelectBox($array, $select_name, $current="", $onclick="") {
	$str = "<select name=\"".$select_name."\" id=\"".$select_name."\" onchange=\"$onclick\">\n";
	while( list($value,$name) = each($array) ) {
		$str .= "<option value=\"".$value."\"".($value==$current?" selected":"").">".$name."</option>\n";
	}
	$str .= "</select>\n";
	return $str;
}

function GenerateRadioButtons($array, $opt_name, $spacer=" ", $current="", $class="") {
	if( $class ) {
		$class = ' class="'.$class.'"';
	}
	$str = "";
	while( list($value,$name) = each($array) ) {
		if( is_numeric($current) ) {
			$selected = $value == $current;
		} else {
			$selected = $name == $current;
		}
		$str .= '<input type="radio" name="'.$opt_name.'" value="'.$value.'" '.($selected?"checked":"").$class.'> '.$name;
		$str .= $spacer;
	}
	return $str;
}

function GenerateCheckboxes($array, $opt_name, $spacer=" ", $current=array(), $class="") {
	if( $class ) {
		$class = ' class="'.$class.'"';
	}
	$str = "";
	while( list($value,$name) = each($array) ) {
		$selected = in_array($value, $current);
		$str .= '<input type="checkbox" name="'.$opt_name.'[]" value="'.$value.'" '.($selected?"checked":"").$class.'> '.$name;
		$str .= $spacer;
	}
	return $str;
}

function GetCheckbox($name, &$array) {
	if( array_key_exists($name,$array) && $array[$name]=='on' ) {
		return 1;
	} else {
		return 0;
	}
}



function ShowDateSelect($select_name, $date="", $onclick="") {
	if( $date != "" ) {
		$day = substr($date,8,2);
		$month = substr($date,5,2);
		$year = substr($date,0,4);
	} else {
		$day = "";
		$month = "";
		$year = "";
	}

	ShowMonthSelect($select_name, $month, $onclick);
	ShowDaySelect($select_name, $day, $onclick);
	ShowYearSelect($select_name, $year, $onclick);
}
	function ShowMonthSelect($select_name, $month="", $onclick="") {
	global $MONTHS;
		echo "<select name=\"".$select_name."_month\" onclick=\"$onclick\">\n";
		if( $month==false ) {
			echo "<option value=\"-1\"></option>\n";
		}
		for( $m=1; $m<=12; $m++ ) {
			$selected = ($m == $month?"SELECTED":"");
			echo "<option value=\"$m\" $selected>".$MONTHS[$m]."</option>\n";
		}
		echo "</select>\n";
	}
	function ShowDaySelect($select_name, $day="", $onclick="") {
		echo "<select name=\"".$select_name."_day\" onclick=\"$onclick\">\n";
		if( $day==false ) {
			echo "<option value=\"-1\"></option>\n";
		}
		for( $d=1; $d<=31; $d++ ) {
			$selected = ($d == $day?"SELECTED":"");
			echo "<option value=\"$d\" $selected>$d</option>\n";
		}
		echo "</select>\n";
	}
	function ShowYearSelect($select_name, $year="", $onclick="") {
		echo "<select name=\"".$select_name."_year\" onclick=\"$onclick\">\n";
		if( $year==false ) {
			echo "<option value=\"-1\"></option>\n";
		}
		for( $y=date("Y"); $y<=(date("Y")+3); $y++ ) {
			$selected = ($y == $year?"SELECTED":"");
			echo "<option value=\"$y\" $selected>$y</option>\n";
		}
		echo "</select>\n";
	}


function ShowTimeSelect($select_name, $time="") {
	if( $time != "" ) {
		$hour = substr($time,0,2);
		$minute = substr($time,3,2);
	} else {
		$hour = -1;
		$minute = -1;
	}

	echo "<select name=\"".$select_name."_hour\">\n";
	if( $time==false ) {
		echo "<option value=\"-1\"></option>\n";
	}
	for( $h=0; $h<=11; $h++ ) {
		$selected = ($h == $hour?"SELECTED":"");
		$show_hour = ($h==0?12:($h));
		echo "<option value=\"$h\" $selected>".$show_hour."am</option>\n";
	}
	for( $h=12; $h<=23; $h++ ) {
		$selected = ($h == $hour?"SELECTED":"");
		$show_hour = ($h==12?12:($h-12));
		echo "<option value=\"$h\" $selected>".$show_hour."pm</option>\n";
	}
	echo "</select>\n";

	echo ": ";

	echo "<select name=\"".$select_name."_minute\">\n";
	if( $time==false ) {
		echo "<option value=\"-1\"></option>\n";
	}
	for( $m=0; $m<=55; $m+=5 ) {
		$selected = ($m == $minute?"SELECTED":"");
		echo "<option value=\"$m\" $selected>".($m<10?"0".$m:$m)."</option>\n";
	}
	echo "</select>\n";

}

function GetRequestDate($name) {
	if( $_REQUEST[$name.'_month'] == -1 ) {
		return -1;
	} else {
		$year = GetRequestYear($name);
		$month = GetRequestMonth($name);
		$day = GetRequestDay($name);
		return $year."-".$month."-".$day;
	}
}
	function GetRequestMonth($name) {
		if( array_key_exists($name.'_month', $_REQUEST) ) {
			return sprintf("%02d",$_REQUEST[$name.'_month']);
		} else {
			return -1;
		}
	}
	function GetRequestDay($name) {
		if( array_key_exists($name.'_day', $_REQUEST) ) {
			return sprintf("%02d",$_REQUEST[$name.'_day']);
		} else {
			return -1;
		}
	}
	function GetRequestYear($name) {
		if( array_key_exists($name.'_year', $_REQUEST) ) {
			return $_REQUEST[$name.'_year'];
		} else {
			return -1;
		}
	}


function GetRequestTime($name) {
	if( $_REQUEST[$name.'_hour'] == -1 ) {
		return -1;
	} else {
		$hour = sprintf("%02d",$_REQUEST[$name.'_hour']);
		if( $_REQUEST[$name."_minute"] == -1 ) {
			$minute = "00";
		} else {
			$minute = sprintf("%02d",$_REQUEST[$name.'_minute']);
		}
		return $hour.":".$minute.":00";
	}
}







function ShowMultiDateForm($event=array()) {
global $DAYS_OF_WEEK, $DAYS;
	//$event is an array:

	if( count($event) == 0 ) {
		$md_date_type = 5;
		$md_single_day = date("Y-m-d");
		$md_days_of_week = array();
		$md_weekpos = 1;
		$md_weekday = 0;
		$md_range_start = date("Y-m-d");
		$md_range_end = date("Y-m-d");
		$md_single_day_everyyear = 0;
		$md_range_noend = 0;
		$md_start_time = "";
		$md_end_time = "";
	} else {
		$md_date_type = $event['date_type'];
		$md_single_day = $event['range_start'];
		$md_range_start = $event['range_start'];
		$md_range_end = $event['range_end'];
		$md_single_day_everyyear = $event['indefinite'];
		$md_range_noend = $event['indefinite'];
		$md_start_time = $event['start_time'];
		$md_end_time = $event['end_time'];

		switch( $md_date_type ) {
		case 1:
			$md_days_of_week = explode(",",$event['data']);
			$md_weekpos = 1;
			$md_weekday = 0;
			break;
		case 3:
			$md_days_of_week = array();
			$tmp = explode(":",$event['data']);
			$md_weekpos = $tmp[0];
			$md_weekday = $tmp[1];
			break;
		default:
			$md_days_of_week = array();
			$md_weekpos = 1;
			$md_weekday = 0;
		}
	}

	?>
	<script type="text/javascript" language="javascript">
	<!--
	function setfocus(radioindex) {
		document.getElementById('md_date_type'+radioindex).checked=true;
	}
	//-->
	</script>
	<?php


	echo "<table class=\"redborder\" width=\"100%\">";
	echo "<tr>";
		echo "<td width=\"20\"><input type=\"radio\" id=\"md_date_type5\" name=\"md_date_type\" value=\"5\" ".($md_date_type==5?"CHECKED":"")."></td>";
		echo "<td>On ";
			ShowDateSelect("md_single_day", $md_single_day, "setfocus(5)");
			echo "<input type=\"checkbox\" name=\"md_single_day_everyyear\" ".($md_single_day_everyyear==1?"CHECKED":"").">Repeat every year";
		echo "</td>";
	echo "</tr>";
	echo "</table>";

	echo "<table class=\"redborder\" style=\"margin-top:4px;\" width=\"100%\">";
	echo "<tr>";
		echo "<td width=\"20\"><input type=\"radio\" id=\"md_date_type1\" name=\"md_date_type\" value=\"1\" ".($md_date_type==1?"CHECKED":"")."></td>";
		echo "<td>Every <span class=\"small\">";
			while( list($day_num, $day_name) = each($DAYS_OF_WEEK) ) {
				echo "<input onclick=\"setfocus(1);\" type=\"checkbox\" name=\"md_days_of_week[]\" value=\"$day_num\" ".(in_array($day_num,$md_days_of_week)?"CHECKED":"")."> ".substr($day_name,0,3)." &nbsp; ";
			}
		echo "</span></td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td><input type=\"radio\" name=\"md_date_type\" id=\"md_date_type3\" value=\"3\" ".($md_date_type==3?"CHECKED":"")."></td>";

		$weeks = array( 1=>"First", 2=>"Second", 3=>"Third", 4=>"Fourth", -2=>"Second to last", -1=>"Last" );
		echo "<td>The ";
		echo GenerateSelectBox($weeks,"md_weekpos",$md_weekpos, "setfocus(3)");
		echo GenerateSelectBox($DAYS_OF_WEEK,"md_weekday",$md_weekday, "setfocus(3)");
		echo " of the month";
		echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td><input type=\"radio\" name=\"md_date_type\" value=\"4\" ".($md_date_type==4?"CHECKED":"")."></td>";

		echo "<td>Monthly on the day defined below ";
		echo "</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td><input type=\"radio\" name=\"md_date_type\" value=\"2\" ".($md_date_type==2?"CHECKED":"")."></td>";
		echo "<td>Daily</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td colspan=\"2\">Starting: ";
			ShowDateSelect("md_range_start",$md_range_start);
			echo "<br>";
			echo "Ending: &nbsp;&nbsp;";
			ShowDateSelect("md_range_end",$md_range_end);
		echo "</td>";
	echo "</tr>";

	echo "</table>";

	echo "<table class=\"redborder\" style=\"margin-top:4px;\" width=\"100%\">";
	echo "<tr>";
		echo "<td width=\"80\">Start Time:</td>";
		echo "<td width=\"130\">";
			ShowTimeSelect("md_start_time", $md_start_time);
		echo "</td>";
		echo "<td rowspan=\"2\">(optional)</td>";
	echo "</tr>";
	echo "<tr>";
		echo "<td>End Time:</td>";
		echo "<td>";
			ShowTimeSelect("md_end_time", $md_end_time);
		echo "</td>";
	echo "</tr>";
	echo "</table>";

}

// write function to take all request variables md_ and create an
// array that can be used as an insert statement to the database:
// range_start, range_end, indefinite, date_type, data
function GetRequestMultiDate() {
// return an array, with keys date_type, data, range_start, range_end, indefinite:

	$ret = array('range_start'=>'-1','range_end'=>'-1', 'date_type'=>'', 'data'=>'',
					'everyyear'=>0, 'start_time'=>'', 'end_time'=>'' );

	if( KeyInRequest('md_date_type') ) {
		$ret['date_type'] = $_REQUEST['md_date_type'];
		$ret['range_start'] = GetRequestDate('md_range_start');
		$ret['range_end'] = GetRequestDate('md_range_end');

		switch( $ret['date_type'] ) {
		case 1:
			$ret['data'] = csl($_REQUEST['md_days_of_week'],",");
			break;
		case 2:
			$ret['data'] = "";
			break;
		case 3:
			$ret['data'] = $_REQUEST['md_weekpos'].":".$_REQUEST['md_weekday'];
			break;
		case 4:
			$ret['data'] = "";
			break;
		case 5:
			$ret['range_start'] = GetRequestDate('md_single_day');
			$ret['range_end'] = $ret['range_start'];
			if( KeyInRequest('md_single_day_everyyear') ) {
				$ret['everyyear'] = 1;
			} else {
				$ret['everyyear'] = 0;
			}
			break;
		}
	}

	$ret['start_time'] = GetRequestTime('md_start_time');
	if( $ret['start_time'] == -1 ) $ret['start_time'] = "NULL";
	$ret['end_time'] = GetRequestTime('md_end_time');
	if( $ret['end_time'] == -1 ) $ret['end_time'] = "NULL";

	return $ret;
}

function InsertMultiDate($data, $table_name, $table_id, $sku="") {
global $DB, $DAYS_OF_WEEK;

	$item = array();
	$item['table_name'] = $table_name;
	$item['table_id'] = $table_id;
	$item['sku'] = $sku;
	$item['date_desc'] = MultiDateText($data);
	$item_id = $DB->Insert('calendar_item',$item);

	$data['day_of_month'] = $DB->Date("d",$data['range_start']);

	$dates = array();

	$mstart = new Date($DB->Timestamp($data['range_start']));
	$mend = new Date($DB->Timestamp($data['range_end']));

	for( $i=$mstart; $i->less_than_e($mend); $i->addMonth(1) ) {
		$days = GetDayOfMonth($i->month, $i->year, $data);
		foreach( $days as $d ) {
			$dates[] = $i->year.'-'.sprintf("%02d",$i->month).'-'.sprintf("%02d",$d);
		}
	}

	foreach( $dates as $d ) {
		$idate = array();
		$idate['item_id'] = $item_id;
		$idate['date'] = $d;
		$idate['start_time'] = $data['start_time'];
		$idate['end_time'] = $data['end_time'];
		$DB->Insert('calendar_item_date', $idate);
	}

	return true;
}


function MultiDateText($data) {
global $DB, $DAYS_OF_WEEK;

	$range_start = $DB->TimeStamp($data['range_start']);
	$range_end = $DB->TimeStamp($data['range_end']);

	switch($data['date_type']) {
	case 1:
		$days = explode(",",$data['data']);
		foreach( $days as $day ) {
			$newdays[] = $DAYS_OF_WEEK[$day];
		}
		$text = "Every ".csl($newdays, ", ", true);
		break;
	case 2:
		//$text = "Daily";
		$text = "";
		break;
	case 3:
		$tmp = explode(":",$data['data']);
		$weekpos = array( 1=>"First", 2=>"Second", 3=>"Third", 4=>"Fourth", -2=>"Second to last", -1=>"Last" );
		$text = "The ".$weekpos[$tmp[0]]." ".$DAYS_OF_WEEK[$tmp[1]]." of every month";
		break;
	case 4:
		$text = "The ".date("jS",$range_start)." of every month";
		break;
	case 5:
		$text = date("l, M jS",$range_start);
		if( $data['everyyear'] == 0 ) {
			$text .= ", ".date("Y",$range_start);
		} else {
			$text .= " of every year";
		}
		break;
	}

	if( $data['everyyear'] == 0 && $data['range_start'] != $data['range_end'] ) {
		if( $data['date_type'] != 2 ) {
			$text .= "\n&nbsp;&nbsp;&nbsp;&nbsp;";
		}

		if( date("F",$range_start) == date("F",$range_end) &&  // if theyre in the same month
			date("Y",$range_start) == date("Y",$range_end)     // if theyre in the same year
	      ) {
			$range = date("j", $range_end) - date("j", $range_start);
		} else {
			$range = 30;
		}
		if( $range > 0 ) {
			if( $range < 3 ) {
				$text .= date("F ",$range_start);
				$days = array();
				for( $i=date("j", $range_start);
					 $i<=date("j", $range_end);
					 $i++ ) {
					$days[] = $i;
					}
				$text .= csl($days);
				$text .= date(", Y",$range_start);
			} else {
				$text .= "From ";
				$text .= date("M ",$range_start);
				$text .= date("jS", $range_start);
				if( date("Y",$range_start) != date("Y",$range_end) ) { // if theyre in different years
					$text .= date(", Y", $range_start);
				}
				$text .= " to ";
				if( date("M",$range_start) == date("M",$range_end) &&  // if theyre in the same month
				    date("Y",$range_start) == date("Y",$range_end) ) { // if theyre in the same year
					$text .= date("jS", $range_end);
				} else {
					$text .= date("M jS", $range_end);
				}
				$text .= date(", Y",$range_end);
			}
		} else {
			$text .= "From ";
			$text .= date("M j, Y",$range_start);
			$text .= " to ";
			$text .= date("M j, Y",$range_end);
		}
	}

	if( $data['start_time'] != "NULL" ) {
		$text .= "\n&nbsp;&nbsp;&nbsp;&nbsp;";
		if( ($data['end_time'] == "NULL") || ($data['start_time'] == $data['end_time']) ) {
			$text .= "at ";
			$text .= date("g:i a",$DB->TimeStamp($data['start_time']));
		} else {
			$text .= "from ";
			$text .= date("g:i a",$DB->TimeStamp($data['start_time']));
			$text .= " to ";
			$text .= date("g:i a",$DB->TimeStamp($data['end_time']));
		}

	}

	return $text;
}


// for placing an event on a monthly calendar view
function GetDayOfMonth($NOW_month, $NOW_year, $event) {
global $DB, $DAYS_OF_WEEK;

	// all these keys are required in $event
	$range_start = $event['range_start'];
	$range_end = $event['range_end'];
	$date_type = $event['date_type'];
	$date_data = $event['data'];
	$indefinite = $event['everyyear'];
	$day_of_month = $event['day_of_month'];

	$return = -1;
	$a_return = array();

	switch( $date_type ) {
		case 1:
			// weekly: "every Monday", "every Monday Wednesday Friday"
			// data: [0..6],[0..6],[0..6]...
			// goal is to find all the Mondays in May
			$days = explode(",",$date_data);
			foreach( $days as $day_of_week ) {
				$i = 0; $cur_month = $NOW_month;

				$cur = mktime(0,0,0,$NOW_month,1,$NOW_year);
				$cur_day = date("j",strtotime($DAYS_OF_WEEK[$day_of_week],$cur));

				while( $cur_day <= date("t",$cur) ) {
					$vv = mktime(0,0,0,$NOW_month,$cur_day,$NOW_year);

					if( ($indefinite ||
					       	( $DB->TimeStamp($range_start) <= $vv && $DB->TimeStamp($range_end) >= $vv))
					  ) {
						$a_return[] = $cur_day;
					}

					$cur_day += 7;
				}
			}
			break;
		case 2:
			// daily
			// data: '', range_start and _end specify date range
			$rs = $DB->TimeStamp($range_start);
			$re = $DB->TimeStamp($range_end);

			$rs_m = date("n",$rs); // start month
			$rs_d = date("j",$rs); // start day
			$re_m = date("n",$re); // end month
			$re_d = date("j",$re); // end day

			if( $rs_m < $NOW_month ) {
				$rs_d = 1;
			}
			if( $re_m > $NOW_month ) {
				$re_d = date("t",$DB->TimeStamp($NOW_year."-".$NOW_month."-01"));
			}

			for( $i=$rs_d; $i<=$re_d; $i++ ) {
				$a_return[] = $i;
			}
			break;
		case 3:
			// monthly: "second Friday"
			// data: [-2/-1/1..4]:[0..6]

			$data = explode(":",$date_data);
			$week = $data[0];
			$day = $DAYS_OF_WEEK[$data[1]];

			if( $week > 0 ) {
				$vv = strtotime("+".($week-1)." week",mktime(0,0,0,$NOW_month,1,$NOW_year));
			} else {
				$vv = strtotime("-".($week)." week",mktime(0,0,0,$NOW_month+1,1,$NOW_year));
			}
			$vv = strtotime($day,$vv);
			$day_of_month = date("j",$vv);
			if( intval(date("n",$vv)) == $NOW_month ) { // this just prevents crazy things like the 5th tuesday from getting messed up
				$return = $day_of_month;
			}
			break;
		case 4:
			// monthly: "the 2nd of every month"
			// data: '' range_start specifies date
			$return = $day_of_month;
			break;
		case 5:
			// once or yearly: "May 5th"
			// data: '' range_start specifies date
			if( date("n",$DB->TimeStamp($range_start)) == $NOW_month ) {
				$return = $day_of_month;
			}
			break;
	}

	if( count($a_return) == 0 ) {
		return array($return);
	} else {
		return $a_return;
        }
}

/**
 *searches the value passed in for anchor tags and adds "http://" to the front of those
 *that do not have http
 **/
function absolutePaths($value){
		//Change regex to skip links with mailto in them.
        //replace any occurances of href=" that are not followed by http, with href="http://
        $text = preg_replace('/href="(?!(http|\/|mailto))/','href="http://',$value);
        
        //make sure there was no regex error
        if(!isset($text)){
                $text = $value;
        }
        
        return $text;
}

?>
