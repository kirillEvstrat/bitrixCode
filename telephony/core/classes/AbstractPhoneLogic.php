<?php
namespace Core;

abstract class AbstractPhoneLogic {
	
	static $phoneLogic;
	
	protected $arTestValueVariable = [
		'GET' => [
			'key' => KEY,
			'oktell_call_id' => 'xxxx-xxxxxxxx-xxxxx10',
			'call_start_date' => '24.08.2018 9:44:00',
			'external_number' => '375447389020',
			'inner_number' => '101',
			'free_inner_number' => '101',
			'user_group_id' => 111,
			'duration' => 50,
			'status' => "304",
			'record_url' => ' ',
			'type' => 'in',
			'call_type' => 'in',
			'id' => 15,
			'notice' => '',
			'notice_log' => '',
		]
	];
	
	protected $arSettingsVariable = [
		'GET' => [
			'key' => ['EMPTY' => false,	'TYPE' => 'string'],
			'id' => ['EMPTY' => false, 'TYPE' => 'integer'],
			'oktell_call_id' => ['EMPTY' => false, 'TYPE' => 'string'],			
			'call_start_date' => ['EMPTY' => false, 'TYPE' => ['datetime' => ['FORMAT' => 'd.m.Y H:i:s', 'INPUT_FORMAT' => 'd.m.Y G:i:s']]],			
			'inner_number' => ['EMPTY' => false, 'TYPE' => 'string'],			
			'external_number' => ['EMPTY' => false, 'TYPE' => 'string'],			
			'free_inner_number' => ['EMPTY' => true,'TYPE' => 'string'],			
			'user_group_id' => ['EMPTY' => false, 'TYPE' => 'integer'],			
			'duration' => ['EMPTY' => false, 'TYPE' => 'integer'],				
			'status' => ['EMPTY' => false, 'TYPE' => ['list' => ['VALUES' => ['200', '304', "603", "603-S"]]]],
			'call_type' => ['EMPTY' => false, 'TYPE' => ['list' => ['VALUES' => ['in', 'out']]]],	
			'record_url' => ['EMPTY' => true, 'TYPE' => 'string'],				
			'iblock_id' => ['EMPTY' => false, 'TYPE' => 'integer'],			
			'PHONE_NUMBER' => ['EMPTY' => false, 'TYPE' => 'string'],
			'CALL_ID' => ['EMPTY' => false, 'TYPE' => 'string'],
			'USER_ID' => ['EMPTY' => false, 'TYPE' => 'integer'],
			'notice' => ['EMPTY' => true, 'TYPE' => 'string'],
			'notice_log' => ['EMPTY' => true, 'TYPE' => 'string'],
		]
	];
	
	protected $arMethodVariable = [
		'in' => ['GET' => ['key', 'oktell_call_id', 'call_start_date', 'external_number', 'free_inner_number', 'user_group_id', 'notice', 'notice_log']],
		'out' => ['GET' => ['key', 'oktell_call_id', 'call_start_date', 'external_number', 'inner_number']],
		'in_call_up' => ['GET' => ['key', 'oktell_call_id', 'user_group_id', 'inner_number']],
		'finish_in' => ['GET' => ['key', 'oktell_call_id', 'external_number', 'inner_number', 'duration', 'status', 'record_url', 'notice', 'notice_log']],
		'finish_out' => ['GET' => ['key', 'oktell_call_id', 'external_number', 'inner_number', 'duration', 'status', 'record_url']],
		'sync' => ['GET' => ['key', 'id', 'oktell_call_id', 'call_start_date', 'external_number', 'inner_number', 'duration', 'status', 'record_url', 'call_type']],
		'out_from_bx' => ['GET' => ['PHONE_NUMBER', 'CALL_ID', 'USER_ID']],
		'getuser' => ['GET' => ['key']],
		
	];
	
	protected $arMethodQueue = [
		'START_METHOD' => ['in', 'out', 'sync'],
		'END_METHOD' => ['finish_in', 'finish_out', 'sync'],
		'SOME_METHODS' => ['sync'],
		'QUEUE' => [
			'in' => ['in_call_up', 'finish_in'],
			'in_call_up' => ['finish_in'],
			'out' => ['finish_out'],
			'sync' => [],
		],
	];
	
	private $DB, $Log;
	
	public function __construct() {
		$this->DB = new DB();
		$this->Log = new Log();
	}
	
	
	abstract public function in($arFields);
	
	abstract public function in_call_up($arFields);
	
	abstract public function finish_in($arFields);
	
	abstract public function out_from_bx($arFields);
	
	abstract public function out($arFields);
	
	abstract public function sync($arFields);
	
	abstract public function finish_out($arFields);
	
	abstract public function getuser($arFields);
	
	
	
	
	
	public function getAnswerParams($nameMethod){
		$baseParams = [
			'SUCCESS' => 'result', 
			'SUCCESS_EMPTY_RESULT' => false,
			'ERROR' => 'error', 
			'ERROR_DESCRIPTION' => 'error_description'
		];
		
		$arAnsewrParams = [			
			'sendOutCall' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'answer telephony is empty'],			
			'getInnerNumberByUserId' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'inner number not found by user_id'],			
			'getUserIdByInnerNumber' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'user not found by inner number'],				
			'getUserByGruopId' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'result empty'],			
			'getAllUserPhone' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'result empty'],				
			'registerCall' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'result empty'],			
			'finishCall' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'result empty'],			
			'hideCall' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'not found call by call_id'],		
			'showCall' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'not found call by call_id'],
			'logicIf' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'result empty'],
			'updateActivity' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'crmActivity result empty'],
			'listPhonecardShow' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'listPhonecardShow result empty'],
			'listPhonecardHide' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'listPhonecardHide result empty'],
			
			'updateLeadOknahome' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'empty'],
			'updateWidget' => $baseParams + ['SUCCESS_EMPTY_RESULT_TEXT' => 'result empty'],
			'answer'=> [],			
		];
		return $arAnsewrParams[$nameMethod];
	}
	
	
	public function getArMethod($action, $arParamsMethod, $methodAnswer = []) {
		$arAction = array_keys($this->arMethodVariable);
		
		if(in_array($action, $arAction)){
			$arMethod = $this->$action($arParamsMethod);
			foreach ($arMethod as $nameMethod => &$method) {
				$method['PARAMS']['ANSWER_PRINT'] = $methodAnswer;
				//$method['PARAMS']['name_method'] = $nameMethod;				
				if($nameMethod == 'answer') {
					$method['PARAMS']['ANSWER_PRINT'][] = 'ECHO';
				}
				$method['ANSWER_PARAMS'] = $this->getAnswerParams($nameMethod);
			}			
			return $arMethod;
		}		
	}
	
	
	
	
	public function getInputMethodParams($nameMethod){
		$arGetParams = [];
		$arPostParams = [];
		$arResult = [];
		
		$arMethodParams = $this->arMethodVariable[$nameMethod];
		if(isset($arMethodParams['GET'])) {
			$arGetParams = $this->testInputMethodParams($arMethodParams['GET'], 'GET');
			$arResult['GET'] = $arGetParams;
		}
		
		if(isset($arMethodParams['POST'])) {
			$arPostParams = $this->testInputMethodParams($arMethodParams['POST'], 'POST');
			$arResult['POST'] = $arPostParams;
		}	
		return $arResult;
	}
	
	
	private function setMethodQueue($telephone_call_id, $method){
		$jsonContent = $this->DB->read();
		$telephone_call_id = "QE_".$telephone_call_id;
		
		if ($jsonContent !== false) {
			$content = json_decode($jsonContent, true);
						
			if(!isset($content[$telephone_call_id])) {
				$content[$telephone_call_id] = $method;
			}
			
			$newContent = json_encode($content);			
			if($this->DB->write($newContent)){
				return true;
			}
		}
		return false;
	}
	
	
	
	private function unsetMethodQueue($telephone_call_id, $method){
		$telephone_call_id = "QE_".$telephone_call_id;
		
		$jsonContent = $this->DB->read();
		if ($jsonContent !== false) {
			$content = json_decode($jsonContent, true);
						
			if(isset($content[$telephone_call_id])) {
				unset($content[$telephone_call_id]);
			}
			$newContent = json_encode($content);			
			if($this->DB->write($newContent)){
				return true;
			}
		}
		return false;
	}
	
	
	
	private function getPreviosMethodQueue($telephone_call_id){
		$currentMethod = "";
		
		$jsonContent = $this->DB->read();
		
		$telephone_call_id = "QE_".$telephone_call_id;
		
		if ($jsonContent !== false) {
			$content = json_decode($jsonContent, true);
						
			if(isset($content[$telephone_call_id])) {
				$currentMethod = $content[$telephone_call_id];
			}
		}
		return $currentMethod;
	}
	
	
	public function checkMethodPriority($method){
		
		$telephone_call_id = htmlspecialchars($_GET['oktell_call_id']);
						
		$previosMethod = $this->getPreviosMethodQueue($telephone_call_id);
		
		// пришел метод без очереди
		if(in_array($method, $this->arMethodQueue['SOME_METHODS'])){
			
			return true;
		// пришел стартовый метод in, out или sync создаем очередь методов
		} else if(in_array($method, $this->arMethodQueue['START_METHOD'])){
			
			if($previosMethod == ""){
				// начало очереди
				$this->setMethodQueue($telephone_call_id, $method);
				return true;
			} else {
				$err = 'повторный стартовый метод для '.$method.' для звонка c oktell_call_id = '.$telephone_call_id;
			}
		} 
		// пришел конечный метод
		else if(in_array($method, $this->arMethodQueue['END_METHOD'])){
			if($previosMethod == "") {
				$err = 'конечный метод без стартового';
			} else {
				$this->unsetMethodQueue($telephone_call_id, $method);
				return true;
			}
		} 
		// пришел промежуточный метод
		else {
			if($previosMethod != "" && isset($this->arMethodQueue['QUEUE'][$previosMethod]) && in_array($method, $this->arMethodQueue['QUEUE'][$previosMethod])){
				$this->unsetMethodQueue($telephone_call_id, $method);
				$this->setMethodQueue($telephone_call_id, $method);
				return true;
			} else {
				//print_r($this->arMethodQueue['QUEUE'][$previosMethod]);
				$err = 'после метода '.$previosMethod.' не ожидается метод '.$method;
			}
		}
		return $err;		
	}
	
	
	
	protected function testInputMethodParams($arMethodParams, $type){
		$arError = [];
		$arRes = [];
		$request = $type == 'POST' ? $_POST : $_GET;
		//echo "<pre>***"; print_r($request); echo "</pre>";
		foreach ($arMethodParams as $nameParam) {
			$settingsParam = $this->arSettingsVariable[$type][$nameParam];
			$testValue = $this->arTestValueVariable[$type][$nameParam];		
			
			if(isset($request[$nameParam]) && ($request[$nameParam] != "" || $settingsParam['EMPTY'] === true)){
				$arRes[$nameParam] = $request[$nameParam];
			}elseif(isset($testValue) && $testValue != "" && TEST_METHOD === true){
				$arRes[$nameParam] = $testValue ;
			}else {			
				$arError[$nameParam] = $nameParam. (isset($type[$nameParam]) ? ' - GET variable is empty' : ' - not GET variable');
			}
					
			if(is_array($settingsParam['TYPE']) && isset($arRes[$nameParam])) {				
				if(!$this->testValue($arRes[$nameParam], $settingsParam['TYPE'])){
					$arError[$nameParam] = $nameParam." - invalid format or value";
				}			
			}			
		}
		
		if(!empty($arError)) {
			$arResult['error'] = $arError;
		}
		if(!empty($arRes)) {
			$arResult['result'] = $arRes;
		}
		
		return $arResult;
	}
	
	protected function testValue($value, $arSettingsTypeParams) {
		//echo "<pre>xxxxx"; print_r($arSettingsTypeParams); echo "</pre>";
		$type = key($arSettingsTypeParams);		
		if ($type == 'datetime') {
			return $this->isFormatDateTime($value, $arSettingsTypeParams['datetime']['INPUT_FORMAT']);
		} else if ($type == 'list') {
			return $this->isIncludeArrayValue($value, $arSettingsTypeParams['list']['VALUES']);
		}
		return true;
	}

	// вынести в отдельный класс проверок
	private function isFormatDateTime($date, $format){
		$UNIX = strtotime($date);
		$newFormat = date($format, $UNIX);
		
		if($newFormat ==  $date) {
			return true;			
		}
		return false;
	}
	
	protected function isIncludeArrayValue($value, $arIncludeValue){
		if(in_array($value, $arIncludeValue)) {
			return true;
		}
		return false;
	}	
}