<?php

	/**
	* base module class
	*/
	class Module
	{
		private $db;

		private $tablepre;

		function Module($config = array()){
			if(!defined("DB_MODE")){
                define("DB_MODE", "Mysqli");
            }
            if(!in_array(DB_MODE, array("Mysql", "Mysqli"))){
                throw new MyException("Cannot use this DB_MODE: ".DB_MODE);
            }

            if(!file_exists(CORE_PATH . "/" . DB_MODE . ".class.php")){
                throw new MyException("Missing file ". DB_MODE . ".class.php");
            }

            include_once CORE_PATH . "/" . DB_MODE . ".class.php";

            $this->db = new Database($config);

            $this->tablepre = $config['tablepre'];
		}

		/**
		 * 获取表前缀
		 */
		function tablepre(){
			return $this->tablepre;
		}

		/**
		 * query 
		 * @param $sql 需要执行的语句
		 */
		function query($sql=""){
			if(!$sql) return false;
			return $this->db->query($sql);
		}

		/**
		 * find one record 获取一条记录（单表）
		 * @param $table 表名
		 * @param $field
		 * @param $where
		 * @param $order 排序
		 * @return array || false
		 */
		function find($table='', $field='*', $where='', $order=''){
			if(!$table){
				return false;
			}
			if($field == ""){
				$field = "*";
			}
			$sql = "SELECT ". $field ." FROM ".$this->tablepre. $table;
			if($where != ""){
				$sql .= " WHERE ".$where." ";
			}
			if($order != ""){
				$sql .= " ORDER BY ".$order." ";
			}
			$sql .= " LIMIT 0,1 ";

			$result = $this->query($sql);

			$resultArr = $this->getArray($result);

			if(!empty($resultArr) && isset($resultArr[0])){
				$resultArr = $resultArr[0];
			}
			return $resultArr;
		}

		/**
		 * find all record 获取多条记录（单表）
		 * @param $table 表名
		 * @param $field
		 * @param $where
		 * @param $order 排序 eg: id desc
		 * @param $limit      eg: 0,10
		 * @return array || false
		 */
		function findAll($table='', $field='*', $where='', $order='', $limit=''){
			if(!$table){
				return false;
			}
			if($field == ""){
				$field = "*";
			}
			$sql = "SELECT ". $field ." FROM ".$this->tablepre. $table;
			if($where != ""){
				$sql .= " WHERE ".$where." ";
			}
			if($order != ""){
				$sql .= " ORDER BY ".$order." ";
			}
			if($limit != ""){
				if(strpos($limit, ",") === false){
					if(intval($limit) > 0){
						$limit = "0," . intval($limit);
						$sql .= " LIMIT ".$limit." ";
					}
				}else{
					$sql .= " LIMIT ".$limit." ";
				}
			}

			$result = $this->query($sql);

			$resultArr = $this->getArray($result);

			return $resultArr;
		}

		/**
		 * find one record 获取一条记录
		 * @param $sql
		 * @return array || false
		 */
		function getOne($sql = ""){
			if($sql == ""){
				return false;
			}
			$result = $this->query($sql);

			$resultArr = $this->getArray($result);

			if(!empty($resultArr) && isset($resultArr[0])){
				$resultArr = $resultArr[0];
			}
			return $resultArr;
		}

		/**
		 * find all record 获取多条记录
		 * @param $sql
		 * @return array || false
		 */
		function getAll($sql = ""){
			if($sql == ""){
				return false;
			}
			$result = $this->query($sql);

			$resultArr = $this->getArray($result);

			return $resultArr;
		}

		/**
		 * 对结果集返回数组
		 * @param $result 查询结果集
		 * @return array
		 */
		function getArray($result=false){
			if(!$result){
				return array();
			}
			$resultArr = array();
			if(DB_MODE == "Mysqli"){
				while ($row = $result->fetch_assoc() ) {
					$resultArr[] = $row;
				}
			}
			if(DB_MODE == "Mysql"){
				while ($row = mysql_fetch_assoc($result) ) {
					$resultArr[] = $row;
				}
			}
			return $resultArr;
		}

		/**
		 * insert 插入一条数据
		 * @param $table
		 * @param array $data 待插入数据数组, 如果数组没有设置键名，则按所有值设置
		 * @return bool
		 */
		function insert($table='', $data=array()){
			if(!$table || !is_array($data) || empty($data)){
				return false;
			}
			$sql = "INSERT INTO ".$this->tablepre.$table;
			//检查key是否合法
			$keys = array_keys($data);
			foreach ($keys as $kk => $key){
				if(is_numeric($key)){
					unset($keys[$kk]);
				}
			}
			$use_key = count($keys)==0 ? false : true;
			$sql_key = "";
			$sql_val = "";
			foreach ($data as $k => $v) {
				if($use_key){
					$sql_key .= ",".$k;
				}
				if(is_numeric($v)){
					$sql_val .= $v.",";
				}else{
					$sql_val .= "'" .$v ."',";
				}
			}
			if($sql_key){
				$sql_key = trim($sql_key, ",");
				$sql .= " (". $sql_key .")";
			}
			$sql_val = trim($sql_val, ",");
			$sql = $sql." VALUES (". $sql_val .") ";

			return $this->query($sql);
		}

		/**
		 * 获取插入数据的id
		 */
		function insert_id(){
			return $this->db->insertId();
		}

		/**
		 * update 更新记录
		 * @param $table
		 * @param array $data 待插入数据数组
		 * @param $where 查询条件
		 * @return bool
		 */
		function update($table='', $data=array(), $where=''){
			if(!$table || !is_array($data) || empty($data) || !$where){
				return false;
			}
			//检查key是否合法
			$keys = array_keys($data);
			foreach ($keys as $kk => $key){
				if(is_numeric($key)){
					unset($keys[$kk]);
				}
			}
			if(count($keys)==0){
				return false; //数组没有设置键名
			}
			$sql_val = "";
			foreach ($data as $k => $v) {
				if(is_numeric($v)){
					$sql_val .= $k . "=" . $v . ",";
				}else{
					$sql_val .= $k . "='" .$v ."',";
				}
			}
			$sql_val = trim($sql_val, ",");
			$sql = "UPDATE ".$this->tablepre.$table." SET ". $sql_val ." WHERE ".$where;

			return $this->query($sql);
		}

		/**
		 * 影响行数
		 */
		function affectedRows(){
			return $this->db->affectedRows();
		}

		/**
		 * delete 删除记录
		 * @param $table
		 * @param $where 查询条件
		 * @return bool
		 */
		function delete($table='', $where=''){
			if(!$table || !$where){
				return false;
			}
			$sql = "DELETE FROM ".$this->tablepre.$table." WHERE ".$where;
			return $this->query($sql);
		}
	}

?>