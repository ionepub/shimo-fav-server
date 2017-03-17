<?php
error_reporting(0);

$referer = $_SERVER['HTTP_ORIGIN'];

if(preg_match("/chrome-extension:\/\/[a-z]{32}$/", $referer)){
	header('Access-Control-Allow-Origin:' . $referer);
}

// $referer = 'chrome-extension://pomeehplgpmjmokdggcjepcglmcnkdoc';
// header('Access-Control-Allow-Origin:' . $referer);

// 是否sae环境
if(function_exists('saeAutoLoader')){// 自动识别SAE环境
	define('APP_MODE', 'SAE');
}else{
	define('APP_MODE', 'COMMON');
}

if(APP_MODE == "SAE"){
	include_once "./lib/App_sae.class.php";
}else{
	include_once "./lib/App.class.php";
}

$app = new App();

$op = isset($_GET['op']) ? $_GET['op'] : "";

// for test
/*if($op == 'test'){
	$app->test();
	die;
}*/

if(!in_array($op, array('add', 'view', 'check', 'cancel', 'update'))){
	die("Access deny");
}

if($op == "add"){
	$app->add();
}

if($op == 'view'){
	$app->findFolder();
}

if($op == 'check'){
	$app->checkFolderShared();
}

if($op == 'cancel'){
	$app->cancelShareFolder();
}

if($op == 'update'){
	$app->updateShareFolder();
}