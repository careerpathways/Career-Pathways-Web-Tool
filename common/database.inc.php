<?php

class common_db
{
	// set these from the config file
	var $host;
	var $user;
	var $pass;
	var $name;
	var $tp;

	var $always_alpha=array();

	var $logging=false;
	var $display_errors=true;
	var $halt_on_error=true;

	var $error;

	var $db;
	var $conn;
	var $count=0;

	var $pswd_salt;

	function Connect() {
		$this->db = new HDB_Sql;
		$this->db->Halt_On_Error = ($this->halt_on_error ? "yes" : "no");
		$this->db->DisplayErrors = $this->display_errors;
		$this->db->Host = $this->host;
		$this->db->User = $this->user;
		$this->db->Password = $this->pass;
		$this->db->Database = $this->name;
		return $this->db->Connect();
	}
	
	function halt_on_error($bool) {
		$this->db->Halt_On_Error = ($bool ? "yes" : "no");
	}

	//************************//
	// Query()
	// Performs a query on the database.and returns the result
	//************************//
	function Query($query, $debug=FALSE, $echo=FALSE) {
		if($debug==TRUE) {
			echo $query.";<br>";
		} else {
			if($echo==TRUE) { echo $query.";<br>"; };
			$this->Log($query);
			$result = $this->db->query($query);
			$this->count++;
			if( $this->db->Error == "" ) {
				$this->error = "";
				return $result;
			} else {
				$this->error = $this->db->Error;
				$this->ShowError($this->db->Error);
				return FALSE;
			}
		}
	}
	function SingleQuery($query, $debug=FALSE, $echo=FALSE) {
		if($debug==TRUE) {
			echo $query.";<br>";
		} else {
			if($echo==TRUE) { echo $query.";<br>"; };
			$result = $this->Query($query);
			if( $this->db->Error == "" ) {
				$this->error = "";
				return $this->NextRecord();
			} else {
				$this->error = $this->db->Error;
				$this->ShowError($this->db->Error);
				return FALSE;
			}
		}
	}

	function MultiQuery($query) {
		$this->Query($query);
		if( $this->db->Error == "" ) {
			$result = array();
			while( $line = $this->NextRecord() ) {
				$result[] = $line;
			}
			return $result;
		}
	}

	// returns an array of the $field column from the $query
	// if $keys is empty, just stick the elements in the array
	// otherwise, $keys is the field to use as keys in the array
	function VerticalQuery($query, $field, $keys="") {
		$results = array();
		$records = $this->MultiQuery($query);
		foreach( $records as $r ) {
			if( $keys == "" ) {
				$results[] = $r[$field];
			} else {
				$results[$r[$keys]] = $r[$field];
			}
		}
		return $results;
	}


	// like MultiQuery, but uses the pk as the keys for the array returned
	function ArrayQuery($query, $pk='id') {
		$data = $this->MultiQuery($query);
		$result = array();
		foreach( $data as $row ) {
			$result[$row[$pk]] = $row;
		}
		return $result;
	}


	// returns the contents of $field from the query
	function GetValue($field, $table, $search, $pk='id') {
		$result = $this->SingleQuery("SELECT $field FROM $table WHERE `$pk`='".$this->Safe($search)."'");
		//return $result[$field];
		if( is_array($result) ) {
			return array_pop($result);  // allows us to do things like SELECT CONCAT(first,' ',last)
		} else {
			return "";
		}
	}

	// inserts $data into $table if it is not already there
	// (compares to $unique_field value)
	// returns id of that record
	//
	function QueryInsert($table, $data, $unique_field, $pk='id') {
		$SQL = "SELECT $pk, $unique_field FROM $table WHERE $unique_field = '".$this->Safe($data[$unique_field])."'";
		$result = $this->SingleQuery($SQL);
		if( $result == "" ) {
			return $this->Insert($table, $data);
		} else {
			return $result[$pk];
		}
	}


	// inserts $data into $table if it is not already there, or updates the record
	// (compares to $unique_field value)
	// returns id of that record
	//
	// 4/17/07 This is how QueryUpdate should have worked from the start, but can't change it now
	// The difference is QueryUpdate assumes unique_field is the primary key, this does not
	//
	function InsertOrUpdate($table, $data, $unique_field, $pk='id') {
		$SQL = "SELECT $pk, $unique_field FROM $table WHERE $unique_field = '".$this->Safe($data[$unique_field])."'";
		$result = $this->SingleQuery($SQL);
		if( $result == "" ) {
			return $this->Insert($table, $data);
		} else {
			$this->Update($table, $data, $result[$pk], $pk);
			return $result[$pk];
		}
	}


	// Runs an INSERT ... ON DUPLICATE KEY UPDATE ... query
	function InsertODUKU($table, $data, $pk='id') {
		$tmp = $this->ArrayToSQL_Insert($data);
		$SQL =  'INSERT INTO '.$table.' ('.$tmp['keys'].')';
		$SQL .= 'VALUES ('.$tmp['values'].')';
		$SQL .= 'ON DUPLICATE KEY UPDATE '.$pk.'=LAST_INSERT_ID('.$pk.'), '.$this->ArrayToSQL_Update($data);
		$this->Query($SQL);
		return $this->InsertID();
	}



	// inserts $data into $table
	//
	// used as a shortcut to QueryInsert($table, $data, "")
	// for clarity. (I really shouldn't have made the unique
	// field in QueryInsert optional, but now that would mean
	// changing the API, so I will do everything in the future
	// this way.
	function Insert($table, $data) {
		$tmp = $this->ArrayToSQL_Insert($data);
		$SQL = "INSERT INTO $table (".$tmp['keys'].") ";
		$SQL .= "VALUES(".$tmp['values'].")";
		$this->Query($SQL);
		return $this->InsertID();
	}


	// updates record $id with $data
	//
	// it is possible that in the future I would want to add a
	// check to see if the table and all the keys exist in the database
	function Update($table, $data, $id, $pk="id") {
		$id = $this->Safe($id);
		$SQL = "UPDATE $table SET ";
		$SQL .= $this->ArrayToSQL_Update($data);
		$SQL .= " WHERE $pk='$id'";
		return $this->Query($SQL);
	}


	// Runs REPLACE INTO query
	function Replace($table, $data) {
		$id = $this->Safe($id);
		$SQL = "REPLACE INTO $table SET ";
		$SQL .= $this->ArrayToSQL_Update($data);
		$this->Query($SQL);
		return $this->InsertID();
	}


	// UPDATE/INSERT as appropriate
	// updates record $id with $data. if it doesn't exist, it creates it.
	// (probably not used on auto-incrementing tables?)
	function QueryUpdate($table, $data, $id, $pk="id") {
		$this->Query("SELECT * FROM $table WHERE `$pk` = '$id'");
		if( $this->NumRecords() == 0 ) {
			return $this->Insert($table, $data);
		} else {
			$this->Update($table, $data, $id, $pk);
			return $id;
		}
	}


	// returns an associative array that can be used to update or insert data back into the table.
	// if $id is empty, it will return an array with all values the default set in the table
	function LoadRecord($table, $id="", $pk="id") {
		if( $id == "" ) {
			$rec = array();
			$fields = $this->MultiQuery("DESCRIBE $table");
			foreach( $fields as $f ) {
				$rec[$f['Field']] = $f['Default'];
			}
			return $rec;
		} else {
			return $this->SingleQuery("SELECT * FROM $table WHERE $pk=$id");
		}

	}




	function NumRecords() {
		return $this->db->num_rows();
	}

	function NextRecord() {
		$this->db->next_record();
		return $this->db->Record;
	}

	function InsertID() {
		// THERE'S NO insert_id() FUNCTION IN PHPLIB??! I CAN'T FIND IT!
	    return mysql_insert_id();
	}

	function AffectedRows() {
		return $this->db->affected_rows();
	}
	
	function Error() {
		return $this->error;
	}

	function GetCount() {
		return $this->count;
	}

	function TableName($table) {
		return $this->tp.$table;
	}


	function SQLDate($timestamp="") {
		if( $timestamp == "" ) { $timestamp=time(); }
		return date("Y-m-d H:i:s",$timestamp);
	}

	function SQLDateOnly($timestamp="") {
		if( $timestamp == "" ) { $timestamp=time(); }
		return date("Y-m-d",$timestamp);
	}

	function SQLTimeOnly($timestamp="") {
		if( $timestamp == "" ) { $timestamp=time(); }
		return date("H:i:s",$timestamp);
	}

	function TimeStamp($mysql) {
		if( strlen($mysql) == 8 ) {
		// time
			$yyyy = 2000;
			$month = 01;
			$dd = 01;
			$hh = substr($mysql, 0,2);
			$mm = substr($mysql, 3,2);
			$ss = substr($mysql, 6,2);
		} elseif( strlen($mysql) == 10 ) {
		// date
			$yyyy = substr($mysql, 0,4);
			$month = substr($mysql, 5,2);
			$dd = substr($mysql, 8,2);
			$hh = 0;
			$mm = 0;
			$ss = 0;
		} elseif( strlen($mysql) == 14 ) {
		// datetime, no - between parts
			$yyyy = substr($mysql, 0,4);
			$month = substr($mysql, 4,2);
			$dd = substr($mysql, 7,2);
			$hh = substr($mysql, 10,2);
			$mm = substr($mysql, 13,2);
			$ss = substr($mysql, 16,2);
		} elseif( strlen($mysql) == 19 ) {
		// datetime
			$yyyy = substr($mysql, 0,4);
			$month = substr($mysql, 5,2);
			$dd = substr($mysql, 8,2);
			$hh = substr($mysql, 11,2);
			$mm = substr($mysql, 14,2);
			$ss = substr($mysql, 17,2);
		} else {
			return 0;
		}

		$timestamp = mktime($hh,$mm,$ss,$month,$dd,$yyyy);
		return $timestamp;
	}

	function Date($format, $mysql_date) {
		// imitates php's date function, except takes a mysql-format date as a parameter instead

		$ts = $this->TimeStamp($mysql_date);
		$format = str_replace('f', sprintf('%2d', date('g', $ts)), $format);

		return (date($format,$ts));
	}

	function ShowError($msg) {
		if( $this->display_errors ) {
			echo $msg;
		}
	}

	function ArrayToSQL_Update($array) {
		$SQL = "";
		$first = TRUE;
		while( list($key,$value) = each( $array ) )  {
			if( $first != TRUE ) $SQL .= ", ";
			if( !is_string($value) || strtolower($value)=="null" ) {
				$quot="";
			} else {
				$quot = "'";
			}
			if( in_array($key,$this->always_alpha) ) {
				$quot = "'";
			}

			if( $key == "order" ) {
				$key = "`order`";
			}
			$SQL .= $key." = ".$quot.$this->Safe($value).$quot;
			$first = FALSE;
		}
		return $SQL;
	}

	function ArrayToSQL_Insert($array) {
		$SQL_keys = "";
		$SQL_values = "";
		$first = TRUE;
		while( list($key,$value) = each( $array ) )  {
			if( $first != TRUE ) {
				$SQL_keys .= ", ";
				$SQL_values .= ", ";
			}
			if( !is_string($value) || strtolower($value)=="null" ) {
				$quot="";
			} else {
				$quot = "'";
			}
			if( in_array($key,$this->always_alpha) ) {
				$quot = "'";
			}

			if( $key == "order" ) {
				$key = "`order`";
			}
			$SQL_keys .= $key;
			$SQL_values .= $quot.$this->Safe($value).$quot;
			$first = FALSE;
		}
		return Array("keys"=>$SQL_keys,"values"=>$SQL_values);
	}

	function Log($query) {
		if( $this->logging ) {
			$logfile = "dblog.txt";

			$h = fopen($logfile, 'a');
			fwrite($h, $this->SQLDate()." ".$query."\n");
			fclose($h);
		}
	}

	//************************
	// Slash $string to make it DB safe if it is not already
	//************************
	function Safe($string) {
		if( is_numeric($string) ) {
			return $string;
		} else {
			if (get_magic_quotes_gpc()==1) {
				return $string;
			} else {
				return mysql_escape_string($string);
			}
		}
	}

	// prints the 2-d array $data as a table.
	// useful for quickly showing the result of a query
	function ShowTable($data) {
		if( count($data) == 0 ) {
			return '<p style="font-size:8pt">Empty set</p>';
		}

		$s = '';
		$s .= '<table cellpadding="3">';
		$s .= '<tr>';
		foreach( $data[0] as $head=>$row ) {
			$s .= '<td style="font-size:8pt;font-weight:bold;">'.$head.'</td>';
		}
		$s .= '</tr>';
		foreach( $data as $row ) {
			$s .= '<tr>';
			foreach( $row as $cell ) {
			$s .= '<td style="font-size:8pt">'.$cell.'</td>';
			}
			$s .= '</tr>';
		}
		$s .= '</table>';

		return $s;
	}


	function OrderUp($table, $id) {
		// swap the order indices with $id and the page above it

		// get parent_id of $id
		$page = $this->SingleQuery("SELECT * FROM $table WHERE id=$id");

		// get element above $id
		// this is the element with the same parent as id, which ALSO
		// has `order` smaller than this['order'], sorted descending
		$other = $this->SingleQuery("SELECT * FROM $table WHERE `order`<".$page['order']." AND parent_id=".$page['parent_id']." ORDER BY `order` DESC");

		if( is_array($other) ) {
			$this->Query("UPDATE $table SET `order`=".$page['order']." WHERE id=".$other['id']);
			$this->Query("UPDATE $table SET `order`=".$other['order']." WHERE id=".$page['id']);
		}
	}

	function OrderDown($table, $id) {
		// swap the order indices with $id and the page below it

		// get parent_id of $id
		$page = $this->SingleQuery("SELECT * FROM $table WHERE id=$id");

		// get element below $id
		// this is the element with the same parent as id, which ALSO
		// has `order` larger than this['order'], sorted ascending
		$other = $this->SingleQuery("SELECT * FROM $table WHERE `order`>".$page['order']." AND parent_id=".$page['parent_id']." ORDER BY `order` ASC");

		if( is_array($other) ) {
			$this->Query("UPDATE $table SET `order`=".$page['order']." WHERE id=".$other['id']);
			$this->Query("UPDATE $table SET `order`=".$other['order']." WHERE id=".$page['id']);
		}
	}

	function OrderAlpha($table, $id) {
		// order all $id's children by alphabetical order

		$children = $this->MultiQuery("SELECT * FROM $table WHERE parent_id=$id");
		usort($children, "titlesort");

		$count = 0;
		foreach( $children as $c ) {
			$this->Query("UPDATE $table SET `order`=$count WHERE id=".$c['id']);
			$count++;
		}

	}

}

function titlesort($a, $b) {
	return (($a['title'] < $b['title'])?-1:1);
}

/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998-2000 NetUSE AG
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id: db_mysql.inc,v 1.11 2002/08/07 19:33:57 layne_weathers Exp $
 *
 */

class HDB_Sql {

  /* public: connection parameters */
  var $Host     = "";
  var $Database = "";
  var $User     = "";
  var $Password = "";

  /* public: configuration parameters */
  var $Auto_Free     = 0;     ## Set to 1 for automatic mysql_free_result()
  var $Debug         = 0;     ## Set to 1 for debugging messages.
  var $Halt_On_Error = "yes"; ## "yes" (halt with message), "no" (ignore errors quietly), "report" (ignore errror, but spit a warning)
  var $DisplayErrors = true;
  var $PConnect      = 0;     ## Set to 1 to use persistent database connections
  var $Seq_Table     = "db_sequence";

  /* public: result array and current row number */
  var $Record   = array();
  var $Row;

  /* public: current error number and error text */
  var $Errno    = 0;
  var $Error    = "";

  /* public: this is an api revision, not a CVS revision. */
  var $type     = "mysql";
  var $revision = "1.2";

  /* private: link and query handles */
  var $Link_ID  = 0;
  var $Query_ID = 0;

  var $locked   = false;      ## set to true while we have a lock

  var $sql_string = "";
  /* public: constructor */
  function DB_Sql($query = "") {
      $this->query($query);
  }

  /* public: some trivial reporting */
  function link_id() {
    return $this->Link_ID;
  }

  function query_id() {
    return $this->Query_ID;
  }

  /* public: connection management */
  function connect($Database = "", $Host = "", $User = "", $Password = "") {
    /* Handle defaults */
    if ("" == $Database)
      $Database = $this->Database;
    if ("" == $Host)
      $Host     = $this->Host;
    if ("" == $User)
      $User     = $this->User;
    if ("" == $Password)
      $Password = $this->Password;

    /* establish connection, select database */
    if ( 0 == $this->Link_ID ) {

      if(!$this->PConnect) {
        $this->Link_ID = @mysql_connect($Host, $User, $Password);
      } else {
        $this->Link_ID = @mysql_pconnect($Host, $User, $Password);
      }
      if (!$this->Link_ID) {
        $this->halt("connect($Host, $User, \$Password) failed.");
        return 0;
      }

      if (!@mysql_select_db($Database,$this->Link_ID)) {
        $this->halt("cannot use database '".$Database."'");
        return 0;
      }
    }

    return $this->Link_ID;
  }

  /* public: discard the query result */
  function free() {
      @mysql_free_result($this->Query_ID);
      $this->Query_ID = 0;
  }

  /* public: perform a query */
  function query($Query_String) {
    /* No empty queries, please, since PHP4 chokes on them. */
    if ($Query_String == "")
      /* The empty query string is passed on from the constructor,
       * when calling the class without a query, e.g. in situations
       * like these: '$db = new DB_Sql_Subclass;'
       */
      return 0;

    if (!$this->connect()) {
      return 0; /* we already complained in connect() about that. */
    };

    # New query, discard previous result.
    if ($this->Query_ID) {
      $this->free();
    }

    if ($this->Debug)
      printf("Debug: query = %s<br>\n", $Query_String);

    $this->sql_string = $Query_String;

    $this->Query_ID = @mysql_query($Query_String,$this->Link_ID);
    $this->Row   = 0;
    $this->Errno = mysql_errno();
    $this->Error = mysql_error();
    if (!$this->Query_ID) {
      $this->halt("Invalid SQL: ".$Query_String);
    }

    # Will return nada if it fails. That's fine.
    return $this->Query_ID;
  }

  /* public: walk result set */
  function next_record() {
    if (!$this->Query_ID) {
      $this->halt("next_record called with no query pending.");
      return 0;
    }

    $this->Record = @mysql_fetch_array($this->Query_ID, MYSQL_ASSOC);
    $this->Row   += 1;
    $this->Errno  = mysql_errno();
    $this->Error  = mysql_error();

    $stat = is_array($this->Record);
    if (!$stat && $this->Auto_Free) {
      $this->free();
    }
    return $stat;
  }

  /* public: position in result set */
  function seek($pos = 0) {
    $status = @mysql_data_seek($this->Query_ID, $pos);
    if ($status)
      $this->Row = $pos;
    else {
      $this->halt("seek($pos) failed: result has ".$this->num_rows()." rows.");

      /* half assed attempt to save the day,
       * but do not consider this documented or even
       * desireable behaviour.
       */
      @mysql_data_seek($this->Query_ID, $this->num_rows());
      $this->Row = $this->num_rows();
      return 0;
    }

    return 1;
  }

  /* public: table locking */
  function lock($table, $mode = "write") {
    $query = "lock tables ";
    if(is_array($table)) {
      while(list($key,$value) = each($table)) {
        // text keys are "read", "read local", "write", "low priority write"
        if(is_int($key)) $key = $mode;
        if(strpos($value, ",")) {
          $query .= str_replace(",", " $key, ", $value) . " $key, ";
        } else {
          $query .= "$value $key, ";
        }
      }
      $query = substr($query, 0, -2);
    } elseif(strpos($table, ",")) {
      $query .= str_replace(",", " $mode, ", $table) . " $mode";
    } else {
      $query .= "$table $mode";
    }
    if(!$this->query($query)) {
      $this->halt("lock() failed.");
      return false;
    }
    $this->locked = true;
    return true;
  }

  function unlock() {

    // set before unlock to avoid potential loop
    $this->locked = false;

    if(!$this->query("unlock tables")) {
      $this->halt("unlock() failed.");
      return false;
    }
    return true;
  }

  /* public: evaluate the result (size, width) */
  function affected_rows() {
    return @mysql_affected_rows($this->Link_ID);
  }

  function num_rows() {
    return @mysql_num_rows($this->Query_ID);
  }

  function num_fields() {
    return @mysql_num_fields($this->Query_ID);
  }

  /* public: shorthand notation */
  function nf() {
    return $this->num_rows();
  }

  function np() {
    print $this->num_rows();
  }

  function f($Name) {
    if (isset($this->Record[$Name])) {
      return $this->Record[$Name];
    }
  }

  function p($Name) {
    if (isset($this->Record[$Name])) {
      print $this->Record[$Name];
    }
  }

  /* public: sequence numbers */
  function nextid($seq_name) {
    /* if no current lock, lock sequence table */
    if(!$this->locked) {
      if($this->lock($this->Seq_Table)) {
        $locked = true;
      } else {
        $this->halt("cannot lock ".$this->Seq_Table." - has it been created?");
        return 0;
      }
    }

    /* get sequence number and increment */
    $q = sprintf("select nextid from %s where seq_name = '%s'",
               $this->Seq_Table,
               $seq_name);
    if(!$this->query($q)) {
      $this->halt('query failed in nextid: '.$q);
      return 0;
    }

    /* No current value, make one */
    if(!$this->next_record()) {
      $currentid = 0;
      $q = sprintf("insert into %s values('%s', %s)",
                 $this->Seq_Table,
                 $seq_name,
                 $currentid);
      if(!$this->query($q)) {
        $this->halt('query failed in nextid: '.$q);
        return 0;
      }
    } else {
      $currentid = $this->f("nextid");
    }
    $nextid = $currentid + 1;
    $q = sprintf("update %s set nextid = '%s' where seq_name = '%s'",
               $this->Seq_Table,
               $nextid,
               $seq_name);
    if(!$this->query($q)) {
      $this->halt('query failed in nextid: '.$q);
      return 0;
    }

    /* if nextid() locked the sequence table, unlock it */
    if($locked) {
      $this->unlock();
    }

    return $nextid;
  }

  /* public: return table metadata */
  function metadata($table = "", $full = false) {
    $count = 0;
    $id    = 0;
    $res   = array();

    /*
     * Due to compatibility problems with Table we changed the behavior
     * of metadata();
     * depending on $full, metadata returns the following values:
     *
     * - full is false (default):
     * $result[]:
     *   [0]["table"]  table name
     *   [0]["name"]   field name
     *   [0]["type"]   field type
     *   [0]["len"]    field length
     *   [0]["flags"]  field flags
     *
     * - full is true
     * $result[]:
     *   ["num_fields"] number of metadata records
     *   [0]["table"]  table name
     *   [0]["name"]   field name
     *   [0]["type"]   field type
     *   [0]["len"]    field length
     *   [0]["flags"]  field flags
     *   ["meta"][field name]  index of field named "field name"
     *   This last one could be used if you have a field name, but no index.
     *   Test:  if (isset($result['meta']['myfield'])) { ...
     */

    // if no $table specified, assume that we are working with a query
    // result
    if ($table) {
      $this->connect();
      $id = @mysql_list_fields($this->Database, $table);
      if (!$id) {
        $this->halt("Metadata query failed.");
        return false;
      }
    } else {
      $id = $this->Query_ID;
      if (!$id) {
        $this->halt("No query specified.");
        return false;
      }
    }

    $count = @mysql_num_fields($id);

    // made this IF due to performance (one if is faster than $count if's)
    if (!$full) {
      for ($i=0; $i<$count; $i++) {
        $res[$i]["table"] = @mysql_field_table ($id, $i);
        $res[$i]["name"]  = @mysql_field_name  ($id, $i);
        $res[$i]["type"]  = @mysql_field_type  ($id, $i);
        $res[$i]["len"]   = @mysql_field_len   ($id, $i);
        $res[$i]["flags"] = @mysql_field_flags ($id, $i);
      }
    } else { // full
      $res["num_fields"]= $count;

      for ($i=0; $i<$count; $i++) {
        $res[$i]["table"] = @mysql_field_table ($id, $i);
        $res[$i]["name"]  = @mysql_field_name  ($id, $i);
        $res[$i]["type"]  = @mysql_field_type  ($id, $i);
        $res[$i]["len"]   = @mysql_field_len   ($id, $i);
        $res[$i]["flags"] = @mysql_field_flags ($id, $i);
        $res["meta"][$res[$i]["name"]] = $i;
      }
    }

    // free the result only if we were called on a table
    if ($table) {
      @mysql_free_result($id);
    }
    return $res;
  }

  /* public: find available table names */
  function table_names() {
    $this->connect();
    $h = @mysql_query("show tables", $this->Link_ID);
    $i = 0;
    while ($info = @mysql_fetch_row($h)) {
      $return[$i]["table_name"]      = $info[0];
      $return[$i]["tablespace_name"] = $this->Database;
      $return[$i]["database"]        = $this->Database;
      $i++;
    }

    @mysql_free_result($h);
    return $return;
  }

  /* private: error handling */
  function halt($msg) {
    $this->Error = @mysql_error($this->Link_ID);
    $this->Errno = @mysql_errno($this->Link_ID);

    if ($this->locked) {
      $this->unlock();
    }

    if ($this->Halt_On_Error == "no")
      return;

    $this->haltmsg($msg);

    if ($this->Halt_On_Error != "report") {
		if( is_writable('/tmp/dberrors.log') ) {
		  $fp = fopen("/tmp/dberrors.log","a");
		  fwrite($fp, date("Y-m-d H:i:s")." ".$_SERVER['REMOTE_ADDR']." ".$_SERVER['REQUEST_URI']."\n");
		  fwrite($fp, trim($this->sql_string));
		  fwrite($fp, $this->Error."\n");
		  fwrite($fp, "\n");
		  fclose($fp);
		}
      die("An error was encountered.");
    }
  }

  function haltmsg($msg) {
    if( $this->DisplayErrors ) {
      printf("<b>Database error:</b> %s<br>\n", $msg);
      printf("<b>MySQL Error</b>: %s (%s)<br>\n",
        $this->Errno,
        $this->Error);
    }
  }

}

?>
