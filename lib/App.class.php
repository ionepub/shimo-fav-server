<?php
/**
 * 核心类
 */
class App
{
    private $module;

    /**
     * init
     */
	function __construct()
	{
		define("CORE_PATH", dirname(__FILE__));
		define("ROOT_PATH", dirname(CORE_PATH));
		define('CONF_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . "Conf");
		define('DB_MODE', 'Mysqli');

		try {
			// customize exception
			if(!file_exists(CORE_PATH . DIRECTORY_SEPARATOR . 'Exception.class.php')){
		        throw new Exception("Missing file Exception.class.php");
		    }
		    include_once CORE_PATH . DIRECTORY_SEPARATOR . "Exception.class.php";

		    // database config file
		    if(!file_exists(CONF_PATH . DIRECTORY_SEPARATOR . "config.php")){
		    	throw new MyException("config.php not found");
		    }
		    $config = include_once CONF_PATH . DIRECTORY_SEPARATOR . "config.php";
		    if(!$config || !is_array($config)){
		    	throw new MyException("config.php must return an array");
		    }

		    //base module class file
		    if(!file_exists(CORE_PATH . DIRECTORY_SEPARATOR . "Module.class.php")){
		        throw new MyException("Missing file Module.class.php");
		    }
		    include_once CORE_PATH . DIRECTORY_SEPARATOR . "Module.class.php";

		    $this->module = new Module($config);

		} catch (MyException $e) {
		    $e->error();
		} catch (Exception $e){
		    echo "Error '".$e->getMessage()."' occurred on line ".$e->getLine()." in File ".$e->getFile();
		    exit;
		}
	}

	/**
	 * 根据文件夹guid检查文件夹是否已分享
	 */
	public function checkFolderShared(){
		try {
			$guid = isset($_GET['id']) ? $this->filter($_GET['id']) : "";
			if(!$guid){
				throw new MyException("数据异常");
			}

			// 检查是否有数据
			$info = $this->module->find('document', '*', 'guid = "'. $guid .'" or parent_guid = "'. $guid .'"');

			// file_put_contents("./log.txt", date("Y-m-d H:i:s") . '\n' . $guid . '\n' . var_export($info, true));

			if($info){
				echo "SUCCESS";
			}else{
				echo "FAILED";
			}

			die;

		} catch (MyException $e) {
			$e->error();
		}
	}

	/**
	 * 批量添加文档
	 */
	public function add(){
		try {
			$data = isset($_POST['list']) ? $_POST['list'] : "";
			// file_put_contents('./log2.txt', var_export($data, true));

			if($data == ""){
				// 空文件夹
				echo json_encode(array('code'=>-1, 'result'=>array()));die;
			}

			if(!is_array($data)){
				throw new MyException("数据异常");
			}

			$folderId = isset($_POST['folderId']) ? $this->filter($_POST['folderId']) : "";

			if(!$folderId){
				throw new MyException("数据异常");
			}

			$re = array();
			foreach ($data as $key => $item) {
				$item['guid'] = $this->filter($item['guid']);
				if(!$item['guid']){
					continue;
				}
				$temp = array(
					'guid'			=>	$item['guid'],
					'parent_guid'	=>	($folderId == $item['guid']) ? '' : $folderId, // 如果是父文件夹本身，则parent_guid为空
					'name'			=>	$item['name'],
					'type'			=>	$item['type'],
					'is_folder'		=>	intval($item['is_folder']) == 1 ? 1 : 0,
				);
				$re[] = $this->module->insert('document', $temp);
			}

			// file_put_contents("./log_".$folderId.".txt", var_export($data, true) . var_export($re, true));
			echo json_encode(array('code'=>0, 'result'=>$re));

			die;
			
		} catch (MyException $e) {
			$e->error();
		}
			
	}

	/**
	 * 删除已分享的数据
	 */
	public function cancelShareFolder(){
		try {
			$data = isset($_POST['list']) ? $_POST['list'] : "";
			$folderId = isset($_POST['folderId']) ? $this->filter($_POST['folderId']) : "";
			//file_put_contents("./log_".$folderId.".txt", var_export($data, true));

			if(!$folderId){
				throw new MyException("数据异常");
			}

			if($data == ""){
				// 空文件夹
				echo json_encode(array('code'=>-1, 'result'=>array()));die;
			}

			if($data != "" && !is_array($data)){
				throw new MyException("数据异常");
			}

			$ids = array();

			foreach ($data as $key => $item) {
				$item['guid'] = $this->filter($item['guid']);
				if(!$item['guid']){
					continue;
				}
				$ids[] = "'".$item['guid']."'";
			}

			$where = 'guid in ('. implode(",", $ids) .')';
			$this->module->delete('document', $where);

			if($this->module->affectedRows() == count($ids)){
				echo json_encode(array('code'=>0, 'result'=>true));
			}else{
				echo json_encode(array('code'=>0, 'result'=>false));
			}
			
			die;

		} catch (MyException $e) {
			$e->error();
		}
	}

	/**
	 * 更新分享内容
	 */
	public function updateShareFolder(){
		try {
			$data = isset($_POST['list']) ? $_POST['list'] : "";
			$folderId = isset($_POST['folderId']) ? $this->filter($_POST['folderId']) : "";

			if(!$folderId){
				throw new MyException("数据异常");
			}

			if($data == ""){
				// 空文件夹
				echo json_encode(array('code'=>-1, 'result'=>array()));die;
			}

			if($data != "" && !is_array($data)){
				throw new MyException("数据异常");
			}

			$re = array();
			$ids = array();

			foreach ($data as $key => $item) {
				$item['guid'] = $this->filter($item['guid']);
				if(!$item['guid']){
					continue;
				}
				$ids[] = "'".$item['guid']."'";
			}

			// 先删除
			$where = 'guid in ('. implode(",", $ids) .') or parent_guid in ('. implode(",", $ids) .') ';
			$this->module->delete('document', $where);

			// 再新增
			foreach ($data as $key => $item) {
				$item['guid'] = $this->filter($item['guid']);
				if(!$item['guid']){
					continue;
				}
				$temp = array(
					'guid'			=>	$item['guid'],
					'parent_guid'	=>	($folderId == $item['guid']) ? '' : $folderId, // 如果是父文件夹本身，则parent_guid为空
					'name'			=>	$item['name'],
					'type'			=>	$item['type'],
					'is_folder'		=>	intval($item['is_folder']) == 1 ? 1 : 0,
				);
				$re[] = $this->module->insert('document', $temp);
			}

			echo json_encode(array('code'=>0, 'result'=>$re));

			die;

		} catch (MyException $e) {
			$e->error();
		}
	}

	/**
	 * 根据guid查找文件夹中所有文件
	 */
	public function findFolder(){
		try {
			$folderId = isset($_GET['folderId']) ? $this->filter($_GET['folderId']) : "";

			if(!$folderId){
				throw new MyException("数据异常");
			}

			$where = '`parent_guid` = "'. $folderId .'" OR `guid` = "'. $folderId .'"';
			$list = $this->module->findAll('document', '*', $where);
			echo json_encode($list);
			die;

		} catch (MyException $e) {
			$e->error();
		}
	}

	/**
	 * 过滤参数
	 */
	private function filter($val = ""){
		$val = htmlspecialchars(strip_tags(trim($val)));
		// 16位
		if(preg_match("/^[a-zA-Z0-9]{16}$/", $val)){
			return $val;
		}else{
			return "";
		}
	}
}