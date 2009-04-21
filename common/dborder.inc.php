<?php

class DB_order {

	var $DB;
	var $order_column='order';

	function DB_order($db) {
		$this->DB = $db;
	}

	function ProcessOrder($table, $id, $redirect) {

		if( KeyInRequest('mode') && $_REQUEST['mode'] == "order" ) {
			if( KeyInRequest('direction') && $id ) {
				switch( $_REQUEST['direction'] ) {
					case "up":
						$this->OrderRecordUp($table,$id);
						break;
					case "down":
						$this->OrderRecordDown($table,$id);
						break;
				}
				header("Location: ".$redirect);
			}
		}

	}

	function LinkUp($id, $query_string="") {
		return $_SERVER['PHP_SELF'].'?'.$query_string.'&mode=order&id='.$id.'&direction=up';
	}
	function LinkDown($id, $query_string="") {
		return $_SERVER['PHP_SELF'].'?'.$query_string.'&mode=order&id='.$id.'&direction=down';
	}



	function GetLastOrderIndex($table_name) {

		$this->DB->Query("SELECT MAX(`".$this->order_column."`) AS max
					FROM ".$table_name);
		if( $this->DB->NumRecords() > 0 ) {
			$order = $this->DB->NextRecord();
			return $order['max'];
		} else {
			return 0;
		}
	}
	function GetFirstOrderIndex($table_name) {

		$this->DB->Query("SELECT MIN(".$this->order_column.") AS min
					FROM ".$table_name);
		if( $this->DB->NumRecords() > 0 ) {
			$order = $this->DB->NextRecord();
			return $order['min'];
		} else {
			return 0;
		}
	}


	function GetPreviousOrderID($table_name, $id) {

		// get index of this record
		$record = $this->DB->SingleQuery("SELECT `".$this->order_column."` FROM ".$table_name." WHERE id=$id");
		$record['".$this->order_column."'];

		// get previous (lower) index of record
		$previous = $this->DB->SingleQuery("SELECT id FROM ".$table_name."
			WHERE `".$this->order_column."` < ".$record[$this->order_column]."
			ORDER BY `".$this->order_column."` DESC LIMIT 1" );

		return $previous['id'];
	}

	function GetNextOrderID($table_name, $id) {

		// get index of this record
		$record = $this->DB->SingleQuery("SELECT `".$this->order_column."` FROM ".$table_name." WHERE id=$id");
		$record['".$this->order_column."'];

		// get next (higher) index of record with same parent_id
		$next = $this->DB->SingleQuery("SELECT id FROM ".$table_name."
			WHERE `".$this->order_column."` > ".$record[$this->order_column]."
			ORDER BY `".$this->order_column."` LIMIT 1" );

		return $next['id'];
	}

	function OrderRecordUp($table_name, $id) {

		$original_id = $id;
		$original_order = $this->GetOrderIndex($table_name, $original_id);
		$swap_id = $this->GetPreviousOrderID($table_name,$id);
		if( $swap_id == "" ) { return FALSE; }
		$swap_order = $this->GetOrderIndex($table_name, $swap_id);

		$this->DB->Query("UPDATE ".$table_name." SET `".$this->order_column."` = $swap_order WHERE id = $original_id");
		$this->DB->Query("UPDATE ".$table_name." SET `".$this->order_column."` = $original_order WHERE id = $swap_id");
	}

	function OrderRecordDown($table_name, $id) {

		$original_id = $id;
		$original_order = $this->GetOrderIndex($table_name, $original_id);
		$swap_id = $this->GetNextOrderID($table_name,$id);
		if( $swap_id == "" ) { return FALSE; }
		$swap_order = $this->GetOrderIndex($table_name, $swap_id);

		$this->DB->Query("UPDATE ".$table_name." SET `".$this->order_column."` = $swap_order WHERE id = $original_id");
		$this->DB->Query("UPDATE ".$table_name." SET `".$this->order_column."` = $original_order WHERE id = $swap_id");
	}

	function GetOrderIndex($table_name, $id) {

		// get index of this record
		$record = $this->DB->SingleQuery("SELECT `".$this->order_column."` FROM ".$table_name." WHERE id=$id");

		return $record[$this->order_column];
	}


}

?>