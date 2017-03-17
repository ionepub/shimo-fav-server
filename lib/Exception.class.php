<?php
	/**
	 * 自定义异常类
	 */
	class MyException extends Exception
	{
		public function error(){
			echo "Error '".$this->getMessage()."' occurred on line ".$this->getLine()." in File ".$this->getFile();
			exit;
		}
		
	}
?>