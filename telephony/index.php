<?php

//подключаем настройки + автозагрузчик
require_once ("core" . DIRECTORY_SEPARATOR . "settings.php");


$AR_ACTIONS = ["in", "out", "sync", "in_call_up", "finish_in", "finish_out", "out_from_bx", 'getuser'];

//$subMethodAnswer = ['ECHO', 'LOG'];

$subMethodAnswer = ['LOG'];

//define('TEST_METHOD', true);
define('TEST_METHOD', false);





$_GET['notice'] = "";
$_GET['notice_log'] = "";
// если звонок завершился на приветствии, для метода in не приходит 
if((!isset($_GET['free_inner_number']) || empty($_GET['free_inner_number'])) && (isset($_GET['action']) && $_GET['action'] == 'in')){
	$_GET['free_inner_number'] = '110';
	$_GET['notice_log'] = 'free_inner_number - not GET variable, free_inner_number set 110';
	$_GET['notice'] = 'завершился на приветствии';
//
}	

// 000 приходит если звонок завершился на приветствии, для метода finish
if((isset($_GET['inner_number']) && ($_GET['inner_number'] == '000')) && (isset($_GET['action']) && $_GET['action'] == 'finish_in')){
	$_GET['notice_log'] = 'inner_number = 000, set inner_number = 110';
	$_GET['notice'] = 'завершился на приветствии';
	$_GET['inner_number'] = '110';
}




$ACTION = $_GET["action"];
$TELEPHONY = new \DE\Telephony($ACTION);




// проверка авторизационного ключа, ключ берем из исходящего вебхука
if((!isset($_GET["key"]) || $_GET["key"] != KEY)) {
	$json = $TELEPHONY->addLogFromArray(["key" => 'invalid key'], $subMethodAnswer);
	echo $json;
	die();
}

// проверка коректности ACTION
if(!in_array($ACTION, $AR_ACTIONS)) {
	$json = $TELEPHONY->addLogFromArray(["action" => 'invalid action'], $subMethodAnswer);
	echo $json;
	die();
}

// проверка очередности запроса для одного звонка
$answer = \DE\developPhoneLogic::getInstance()->checkMethodPriority($ACTION);
if($answer !== true){
	$json = $TELEPHONY->addLogFromArray(["queue" => $answer], $subMethodAnswer);
	echo $json;
	die();
}

// проверка параметров запроса
$arParamsMethod = \DE\developPhoneLogic::getInstance()->getInputMethodParams($ACTION)['GET'];

if (isset($arParamsMethod['error'])) {
	$answerJSON = $TELEPHONY->addLogFromArray($arParamsMethod['error'], $subMethodAnswer);
	echo $answerJSON;
	die();
}



// выполнение метода
$arMethod = \DE\developPhoneLogic::getInstance()->getArMethod($ACTION, $arParamsMethod['result'], $subMethodAnswer);


$previousArParams = [];
$previousArResult = [];
$last_name_method = "";
$error = false;


$TELEPHONY->addCompositeMethod($arMethod, $previousArParams, $previousArResult, $last_name_method, $error);