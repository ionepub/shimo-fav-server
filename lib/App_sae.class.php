<?php
/**
 * 核心类(SAE & KVDB)
 * document: http://apidoc.sinaapp.com/class-SaeKV.html
 *           http://apidoc.sinaapp.com/source-class-SaeKV.html
 */
class App
{
	private $module;
	private $db;

	/**
     * init
     */
	function __construct()
	{
		define("CORE_PATH", dirname(__FILE__));
		define("ROOT_PATH", dirname(CORE_PATH));

		try {
			// customize exception
			if(!file_exists(CORE_PATH . DIRECTORY_SEPARATOR . 'Exception.class.php')){
		        throw new Exception("Missing file Exception.class.php");
		    }
		    include_once CORE_PATH . DIRECTORY_SEPARATOR . "Exception.class.php";

		    if(APP_MODE !== "SAE"){
		    	throw new MyException("Access Deny");
		    }

		    // init sae kvdb
		    $this->db = new SaeKV();

		    // 初始化SaeKV对象
			$initRe = $this->db->init(); //访问授权应用的数据
			if(!$initRe){
				throw new MyException("kvdb init failed");
			}

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
			// $info = $this->module->find('document', '*', 'guid = "'. $guid .'" or parent_guid = "'. $guid .'"');
			$info = $this->db->get($guid);

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
			$tree = array(); // 父子关系
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
				// $re[] = $this->module->insert('document', $temp);
				$temp = json_encode($temp);
				$re[] = $this->db->add($item['guid'], $temp);

				$tree[] = $item['guid'];
			}

			// 添加关系树记录
			$this->db->add('pid_' . $folderId, json_encode($tree));

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
			$folderId = isset($_POST['folderId']) ? $this->filter($_POST['folderId']) : "";
			//file_put_contents("./log_".$folderId.".txt", var_export($data, true));

			if(!$folderId){
				throw new MyException("数据异常");
			}

			$tree = $this->db->get('pid_' . $folderId);

			$affectedRows = 0;

			if(!$tree){
				$tree = array();

				$data = isset($_POST['list']) ? $_POST['list'] : "";

				if($data == ""){
					// 空文件夹
					echo json_encode(array('code'=>-1, 'result'=>array()));die;
				}

				if($data != "" && !is_array($data)){
					throw new MyException("数据异常");
				}

				foreach ($data as $key => $item) {
					$item['guid'] = $this->filter($item['guid']);
					if(!$item['guid']){
						continue;
					}
					$tree[] = "'".$item['guid']."'";

					$re = $this->db->delete($item['guid']);
					$affectedRows += intval($re);
				}
			}else{
				$tree = json_decode($tree, true);

				foreach ($tree as $key => $item) {
					$re = $this->db->delete($item);
					$affectedRows += intval($re);
				}
			}

			// 删除关系树
			$this->db->delete('pid_' . $folderId);

			if($affectedRows == count($tree)){
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
			$tree = array(); // 父子关系

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

				$temp = json_encode($temp);
				if(!$this->db->get($item['guid'])){
					// 不存在 新增
					$re[] = $this->db->add($item['guid'], $temp);
				}else{
					// 存在 更新
					$re[] = $this->db->replace($item['guid'], $temp);
				}

				$tree[] = $item['guid'];
			}

			// 更新关系树记录
			$this->db->set('pid_' . $folderId, json_encode($tree));

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

			$list = array();

			$tree = $this->db->get('pid_' . $folderId);
			if($tree){
				$tree = json_decode($tree, true);
				// 批量获取数据
				$temp = $this->db->mget($tree);
				foreach ($temp as $key => $item) {
					$list[] = json_decode($item, true);
				}

				if(!in_array($folderId, $tree)){
					// 补当前文件夹数据
					$temp = $this->db->get($folderId);
					$list[] = json_decode($temp, true);
				}
			}

			echo json_encode($list);
			die;

		} catch (MyException $e) {
			$e->error();
		}
	}

	public function test(){
		// 循环获取所有key-values
		$ret = $this->db->pkrget('', 100);
		while (true) {
			echo "<pre>";
			print_r($ret);
			echo "</pre>";
			end($ret);
			$start_key = key($ret);
			$i = count($ret);
			if ($i < 100) break;
			$ret = $this->db->pkrget('', 100, $start_key);
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