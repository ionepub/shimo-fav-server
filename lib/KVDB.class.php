<?php

	/**
	* SAE KVDB class helper
	*/
	class Database
	{
		private $db;
		// connect to kvdb
		function Database($config = array()){
			$this->db = new SaeKV();
			// 初始化SaeKV对象
			$ret = $kv->init(); //访问授权应用的数据

			// $this->db = new mysqli($config['dbhost'], $config['username'], $config['dbpassword'], $config['dbname']);
			// if(mysqli_connect_errno()){
			// 	throw new MyException("Cannot connect mysqli: " . mysqli_connect_error());
			// }

			// $this->db->query("set names ".$config['dbcharset']);
		}

		//执行sql
		function query($sql=""){
			// if(!$sql) return false;
			// return $this->db->query($sql);
		}

		/**
		 * 影响行数
		 */
		function affectedRows(){
			// return $this->db->affected_rows;
		}

		/**
		 * 
		 */
		function insertId(){
			// return $this->db->insert_id;
		}
	}

?>