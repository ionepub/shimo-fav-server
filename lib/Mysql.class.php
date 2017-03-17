<?php

	/**
	* Mysqli class helper
	*/
	class Database
	{
		private $db;
		// connect to mysqli
		function Database($config = array()){
			$this->db = mysql_connect($config['dbhost'],$config['username'],$config['dbpassword']);
			if(!$this->db){
				throw new MyException("Cannot connect mysql: " . mysql_error());
			}
			$select_db = mysql_select_db($config['dbname'],$this->db);
			if(!$select_db){
				throw new MyException('Cannot select database: '.mysql_error());
			}
			mysql_query("set names ".$config['dbcharset'], $this->db);
		}

		//执行sql
		function query($sql=""){
			if(!$sql) return false;
			return mysql_query($sql, $this->db);
		}

		/**
		 * 影响行数
		 */
		function affectedRows(){
			return mysql_affected_rows($this->db);
		}

		/**
		 * 
		 */
		function insertId(){
			return mysql_insert_id($this->db);
		}
	}

?>