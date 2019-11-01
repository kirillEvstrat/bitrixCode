<?php
namespace DE;

use \Core\DB;
use \Core\BxQuery;
use \Core\OktellQuery;
use \Core\Log;


class Telephony {
	const ACTIVITY = "A_";
	private $BxQuery, $OktellQuery, $webHook, $action, $logHeaderString = "";
	
	public function __construct($ACTION) {		
		$this->webHook = WEB_HOOK;	
		
		$this->BxQuery = new BxQuery();
		
		$this->OktellQuery = new OktellQuery(URL_TELEPHONY);
		$this->DB = new DB();
		$this->Log = new Log();
		
		$this->PhoneLogic = new developPhoneLogic();
		
		$this->logHeaderString = $this->constructLogHeaderString($ACTION);
		$this->action = $ACTION;
		
	}
	
	public function addLogFromArray($arError, array $arAnswerType){
		
		if(in_array('LOG', $arAnswerType)) {
			$errString = implode(", ", $arError);
			$string = "\tstatus : ERR\terror_description : ".$errString;	
			$this->logHeaderString .= $string;
			
			
			$this->hideKey($this->logHeaderString);

			$this->Log->addTelephonyLog($this->logHeaderString);
			$this->Log->addTelephonyErrLog($this->logHeaderString);
		}
		
		$answer = [
			'status' => 'ERR',
			'name_method' => $this->action,
			'error_description' =>  implode(", ",($arError))
		];
		return json_encode($answer);
	}
	
	function hideKey(&$string){
		$string = str_replace(KEY, "***********", $string);
		//$string = str_replace(URL_TELEPHONY_CALL_RECCORD, "***********", $string);
	}
	
	/*
	private function updateLeadOknahome ($arFields, $arAnswerParams){
		$method = "telephony.externalCall.searchCrmEntities";		
		$url = $this->webHook.$method;
		
		$arParams = [
			"PHONE_NUMBER" => $arFields['external_number'],			
		];
		
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($userJson, true);
		
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);
		}
		
			
		
		$arResult = [];
		
		foreach ($res['result'] as $crmEntity) {
			
			if($crmEntity['CRM_ENTITY_TYPE'] == 'CONTACT' || $crmEntity['CRM_ENTITY_TYPE'] == 'COMPANY'){
				$arResult['ID'] = 0;			
			}
			if($crmEntity['CRM_ENTITY_TYPE'] == 'LEAD'){
				$arResult['ID'] = $crmEntity['CRM_ENTITY_ID'];
				$arResult['ASSIGNED_BY_ID'] = $crmEntity['ASSIGNED_BY_ID'];
				$arResult['ASSIGNED_PHONE_INNER'] = $crmEntity['ASSIGNED_BY']['USER_PHONE_INNER'];
			}
		}
		
		if($arResult['ID'] > 0 && $arResult['ASSIGNED_PHONE_INNER'] != $arFields['inner_number']){
			
			$method = "crm.lead.get";		
			$url = $this->webHook.$method;
			
			$arFieldsLead = [
				'id' => $arResult['ID'],
			];			
			
			$userJson = $this->BxQuery->RestApiQuery($url, $arFieldsLead);
			$arLead = json_decode($userJson, true)['result'];
			
			if ($arLead['STATUS_ID'] == 'NEW') {
				$method = "crm.lead.update";
				$url = $this->webHook . $method;
				
				if (is_array($arFields['user_id'])) {
					$arFields['user_id'] = $arFields['user_id'][0];
				}
				
				if(isset($arFields['call_start_date']) && !empty($arFields['call_start_date'])){
					$timeUnix = strtotime($arFields['call_start_date']);
				} else {
					$timeUnix = time();
				}
				$date = date("d.m.Y H:i:s", $timeUnix);			
		

				$arFields = [
					'ID' => $arResult['ID'],
					'fields' => [
						"ASSIGNED_BY_ID" => $arFields['user_id'],
						'UF_CRM_1510666563' => $date,
						'UF_CRM_1513256627' => $date,
						'SOURCE_ID' => '3',
					],
					'params' => [
						"REGISTER_SONET_EVENT" => "N",
					]
				];
				//echo "<pre>";	print_r($arFields);	echo "</pre>";


				$userJson = $this->BxQuery->RestApiQuery($url, $arFields);
			}

			//echo "<pre>"; print_r($userJson); echo "</pre>";
		}
		
		$result['result'] = $arResult;
		$result['result']['METHOD_STATUS'] = $res['METHOD_STATUS'];
		//echo "<pre>"; print_r($result); echo "</pre>";
		return json_encode($result);
	}*/

			
	private function registerCall($arFields, $arAnswerParams){
		
		$method = "telephony.externalCall.register";		
		$url = $this->webHook.$method;
		
		$extrnalNumber = $this->isCallAnonymous($arFields['external_number']);
		
		$arParams = [
			"USER_PHONE_INNER" => $arFields['inner_number'],
			"PHONE_NUMBER" =>  $extrnalNumber,
			"TYPE" =>  $arFields['type'], // входящий
			"SHOW" => $arFields['show'], // не показываем карточку звонка			
			"CALL_START_DATE" => $arFields['call_start_date'],
			"CRM_CREATE" => 1
		];
		
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($userJson, true);
		
					
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);
		}		
		
		$arResult = $res['result']; 
		$arResult['METHOD_STATUS'] = $res['METHOD_STATUS'];
		
		$telephone_call_id = $arFields['call_id'];
		
		$call_id = $arResult['CALL_ID'];
		//echo "<pre>Res"; print_r($arResult); echo "</pre>";
		$arResult['CALL_ID'] = $telephone_call_id;
		
		//echo "<pre>regCall"; print_r($call_id); echo "</pre>";
		
		$res = $this->writeCallId($call_id, $telephone_call_id);
		
		//echo "<pre>ress"; print_r($res); echo "</pre>";
		
		// звонок идет по кругу
		/*if($res !== false && $res !==true){
			$arResult['previous_call_id'] = $res;
			
			$arDB = $this->getAllDbCallId();
			$prevUserId = array_search($res, $arDB);
			$arResult['previous_user_id'] = [$prevUserId];
			//echo "<pre>******"; print_r($prevUserId); echo "</pre>";
			
		} else {
			$arResult['previous_call_id'] = "";
		}
		
		// запсиь для списка телефонных карточек
		if(isset($arFields['user_id'])) {
			$userId = $this->prepereUserIdFromArray($arFields['user_id']);
			$res = $this->writeCallId($call_id, $userId);
		}*/
		
		//echo "<pre>******"; print_r($arFields); echo "</pre>";
		
		$result = ['result' => $arResult];		
		//echo "<pre>"; print_r($result); echo "</pre>";
		return json_encode($result);
	}
	
	private function prepereUserIdFromArray($arUserId){
		return isset($arUserId[0]) ? $arUserId[0] : "";
	}
	
	private function isCallAnonymous($extrnalNumber){
		if(isset(ANONYMOS_CALL[$extrnalNumber])) {
			return ANONYMOS_CALL[$extrnalNumber];
		}
		return $extrnalNumber;
	}
	
	
	private function finishCall($arFields, $arAnswerParams){
		
		$method = "telephony.externalCall.finish";		
		$url = $this->webHook.$method;
		
		$call_id = $this->getCallId($arFields['call_id']);
		
		$arParams = [
			"CALL_ID" => $call_id,
			"USER_PHONE_INNER" => $arFields['inner_number'],
			"STATUS_CODE" => $arFields['status'],
			"RECORD_URL" => URL_TELEPHONY_CALL_RECCORD."/".$arFields['record_url'],
			"DURATION" =>  $arFields['duration'],
			"ADD_TO_CHAT" => 0
		];
		
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$result = json_decode($userJson, true);
		
		//echo "<pre>"; print_r($result) ;echo "</pre>";
		
		if(!$this->isResultSuccess(__METHOD__, $result, $arAnswerParams)){			
			return json_encode(['result' => $result]);
		}
		$arResult = $result['result'];
		$arResult['METHOD_STATUS'] = $result['METHOD_STATUS'];
		
		$this->deleteCallId($arFields['call_id']);
		
		return json_encode(['result' => $arResult]);
		
	}
	
	
	private function getUserIdByInnerNumber($arFields, $arAnswerParams) {
		$method = "user.get";		
		$url = $this->webHook.$method;
		
		/*if(!isset($arFields['free_inner_number']) || empty($arFields['free_inner_number'])){
			$arFields['free_inner_number'] = '110';
		}*/
		$arParams = [
			'FILTER' => ['UF_PHONE_INNER' => $arFields['free_inner_number']]
		];
		
		
		
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($userJson, true);		
		
		
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);
		}
		
		$arUser = $res['result'];	
		
		$arId = [];
		foreach ($arUser as $user) {
			$arId[] = $user['ID'];
		}
		
		$result = [
			'result' => ['ID' => $arId, 'METHOD_STATUS' => $res['METHOD_STATUS']]
		];
		return json_encode($result);
	}
	
	private function getInnerNumberByUserId($arFields, $arAnswerParams) {
		$method = "user.get";		
		$url = $this->webHook.$method;
		
		$arParams = [
			'FILTER' => ['ID' => $arFields['user_id']]
			//'FILTER' => ['ID' => 888888]
		];
		
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$arUser = json_decode($userJson, true);
		
				
		if(!$this->isResultSuccess(__METHOD__, $arUser, $arAnswerParams)){
			return json_encode(['result' => $arUser]);
		}	
		
		$arUser['result']['UF_PHONE_INNER'] = $arUser['result'][0]['UF_PHONE_INNER'];
		unset($arUser['result'][0]);

		$result = ['result' => $arUser];	
		
		return json_encode($result);
	}
	
	private function listPhonecardShow ($arFields, $arAnswerParams){
		$method = "list.phonecard.show";		
		$url = $this->webHook.$method;
		$arParams = [
			'USER_ID' => $arFields['user_id'],
			'CALL_ID' => $arFields['call_id'],
		];
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($userJson, true);
				
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);
		}
		$result = ['result' => $res];
		return json_encode($result);
	}
	
	
	private function listPhonecardHide ($arFields, $arAnswerParams){
		$method = "list.phonecard.hide";		
		$url = $this->webHook.$method;
		
		if(isset($arFields['callup_user_id']) && !empty($arFields['callup_user_id'])){
			$arFields['user_id'] = array_values(array_diff($arFields['user_id'], $arFields['callup_user_id']));			
		}
		
		$arParams = [
			'USER_ID' => $arFields['user_id'],
			'CALL_ID' => $arFields['call_id'],			
		];
		
		//echo "<pre>"; print_r($arParams) ;echo "</pre>";
		
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($userJson, true);
				
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);
		}
		$result = ['result' => $res];
		return json_encode($result);
	}
	
	private function updateWidget($arFields, $arAnswerParams) {
		$method = "crm.activitywidget.update";
		$url = $this->webHook.$method;
		
		$arParams = [];
		
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($userJson, true);
				
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);
		}
		$result = ['result' => $res];
		return json_encode($result);
	}
	
	private function showCall($arFields, $arAnswerParams){
		$method = "telephony.externalcall.show";		
		$url = $this->webHook.$method;
		
		$call_id = $this->getCallId($arFields['call_id']);
		
		$arParams = [
			'USER_ID' => $arFields['user_id'],
			'CALL_ID' => $call_id			
		];
		
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($userJson, true);		
				
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);
		}
		
		$result = ['result' => $res];		
				
		
		return json_encode($result);
	}
	
	private function hideCall($arFields, $arAnswerParams) {
		
		$method = "telephony.externalcall.hide";		
		$url = $this->webHook.$method;
		
		$callup_user = isset($arFields['callup_user_id'][0]) ? $arFields['callup_user_id'][0] : -1;
		
		$arUserId = [];
		foreach ($arFields['user_id'] as $user_id) {
			if($user_id != $callup_user){
				$arUserId[] = $user_id;
			}
		}
		$call_id = (isset($arFields['previous_call_id']) && !empty($arFields['previous_call_id'])) 
				? $arFields['previous_call_id'] : $this->getCallId($arFields['call_id']);
				
		$arParams = [
			'USER_ID' => $arUserId,
			'CALL_ID' => $call_id			
		];
		//echo "<pre>hideCall"; print_r($arParams); echo "</pre>";
		$userJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($userJson, true);		
				
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);			
		}	
		
		$result = ['result' => $res];
		
		//echo "<pre>hideCall"; print_r($result); echo "</pre>";
		return json_encode($result);
	}
	
	private function answer($arFields){
		
		//echo "<pre>****"; print_r($arFields); echo "</pre>";
		$log = in_array('LOG', ($arFields['ANSWER_PRINT']));
		$echo = in_array('ECHO', ($arFields['ANSWER_PRINT']));
		
		
		if(isset($arFields['previous_name_method']) && !empty($arFields['previous_name_method'])) {
			$arFields['name_method'] = $arFields['previous_name_method'];
			unset($arFields['previous_name_method']);
			$log = false;
		}
		
		unset($arFields['ANSWER_PRINT']);
		//echo "<pre>"; print_r($arFields); echo "</pre>";
		if($arFields['status'] == 'ERR'){
			$arFields = [
				'status' => 'ERR',
				'name_method' => $arFields['name_method'],
				'error_description' => $arFields['error_description'],
			];			
		} else {
			unset($arFields['error_description']);
		}
		$JSON = json_encode($arFields);
		if($log){
			$string = $this->constructLogString($arFields);
			$this->hideKey($string);
			$this->Log->addTelephonyLog($string);
		}
		
		if($echo){
			echo $JSON;
		}		
	}
	
	private function constructLogHeaderString($ACTION){
		
		$url = $_SERVER['REQUEST_URI'];
		$host = explode(":", $_SERVER['HTTP_HOST'])[0];
		$protocol = $_SERVER['SERVER_PORT'] == 80 ? "http" : "https";
			
		$link = $protocol."://".$host.$url;
					
		$string = "\n\n".date("d.m.Y H:i:s")."    method : ".$ACTION."    url : ".$link."\n\n";
		
		$aryGet = $_GET;
		foreach ($aryGet as $key => $value) {
			$string .= "\tGET : ".$key." = ".$value."\n";
		}
			
		return  $string;		
	}
	
	
	private function constructLogString($arFields){
		
		$string = $this->logHeaderString;		
		$this->logHeaderString = "";
		
		$status = $arFields['status'];
		$method = $arFields['name_method'];
		$err_description = (isset($arFields['error_description']) && !empty($arFields['error_description'])) 
				? "\terror_description : ".$arFields['error_description'] : "";
		
		$string .= "\tsubMethod : ".$method."\tstatus : ".$status.$err_description;
		//echo "<pre>"; print_r($string); echo "</pre>";
		return $string;
	}
	
	private function getUserByGruopId($arFields, $arAnswerParams){
		
		
		$iblock_id = $arFields['iblock_id'];
		$resJson = $this->getUserPhoneGroup(['iblock_id' => $iblock_id], $arAnswerParams);
		$res = json_decode($resJson, true);
		
		if(isset($res['result']['error'])) {
			return $resJson;
		}
		
		$group_id = $arFields['user_group_id'];
		
		$result = [];
		if(isset($res['result'][$group_id])) {
			$result = $res['result'][$group_id];
		}		
		
		
		return json_encode(['result' => ['USER_ID' => $result, 'METHOD_STATUS' => 'ok']]);		
	}
	
	private function getAllUserPhone($arFields, $arAnswerParams){
		$res = $this->getUserPhoneGroup($arFields, $arAnswerParams);
		
		$arUser = json_decode($res, true);
		
		if(isset($arUser['result']['error'])) {
			return $res;
		}
		
		if (isset($arUser['result'])) {
			$arResult = [];
			
			foreach ($arUser['result'] as $ar) {
				$arResult = array_merge($arResult, $ar);
			}
			
			$result = ['result' => ['USER_ID' => $arResult, 'METHOD_STATUS' => 'ok']];
			
			return json_encode($result);
		}
	}

	private function getUserPhoneGroup($arFields, $arAnswerParams){
		
		
		
		$iblock_id = $arFields['iblock_id'];
		$method = "lists.element.get";		
		$url = $this->webHook.$method;
		
		$arParams = [
			'IBLOCK_TYPE_ID' => 'lists',
			'IBLOCK_ID' => $iblock_id			
		];
		$resultJson = $this->BxQuery->RestApiQuery($url, $arParams);
		
		$res = json_decode($resultJson, true);
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){		
			return json_encode(['result' => $res]);
		}
	
		$arGroup = [];
		if(isset($res['result'])){
			foreach ($res['result'] as $group) {
				$arGroup[end($group[IBLOCK_GROUP_NUMBER])] = array_values($group[IBLOCK_AR_GROUP_USER_ID]);
			}			
		}
		
		//$arGroup = PHONE_GROUPS;
		
		return json_encode(['result' => $arGroup]);		
	}
	
	private function sendOutCall($arField, $arAnswerParams){
		$call_id = $arField['CALL_ID'];
		$resJSON = $this->OktellQuery->query($arField);		
		
		$resJSON = json_encode(['status' => 'ok', 'call_id_oktell' => "zzzzzzzzzzzzzzzzz2222222222"]);
			
		$res = json_decode($resJSON, true);
		
		
		if(isset($res['status']) && $res['status'] = 'OK' && isset($res['call_id_oktell']) && !empty($res['call_id_oktell']) && $res['status'] = 'OK'){
			$res['METHOD_STATUS'] = 'OK';
		} else {
			$res = ['error' => true, 'error_description' => 'not answer telephony'];
		}
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){		
			return json_encode(['result' => $res]);
		}
		
		$this->writeCallId($call_id, $res['result']['call_id_oktell']);
		
		return json_encode(['result' => $res]);
		
	}
	
		
	private function updateActivity($arField, $arAnswerParams) {
		$method = "crm.activity.update";
		$url = $this->webHook.$method;
		
		//echo "<pre>"; print_r($arField); echo "</pre>";
		
		$status = $arField['status'];
		$activity_id = $arField['activity_id'];
		$oktell_call_id = $arField['oktell_call_id'];
		
		$notice = $arField['notice'];
		if(!empty($notice)){
			$notice = " ".$notice;
		}
		
		$subject = $this->buildActivitySubject($activity_id, $status, $notice);
		$fields = ["SUBJECT" => $subject, 'PROVIDER_GROUP_ID' => $oktell_call_id];
		
		//  missed call
		if($status == "304"){
			
			$this->writeMissedCall($activity_id, $arField['external_number']);			
			$fields = array_merge($fields, ['PRIORITY' => 3, "COMPLETED" => "Y"]);
		} else if($status == "200") {
			$arActivity = $this->getMissedCallByPhone($arField['external_number']);
			foreach ($arActivity as $act_id_prep) {
				$act_id = str_replace(self::ACTIVITY, "", $act_id_prep);
				$this->unsetMissedCallActivity($act_id);
				$this->deleteMissedCallByActivity($act_id);
			}			
		}
		// missed call
		
		$arParams = array(
			"ID" => $activity_id,
			"fields" => $fields,
			"params" => ["REGISTER_SONET_EVENT" => "N"],					
		);
		
		$resultJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($resultJson, true);
		
		if(!$this->isResultSuccess(__METHOD__, $res, $arAnswerParams)){			
			return json_encode(['result' => $res]);
		}
		
		$result = ['result' => $res];			
		
		return json_encode($result);
		//echo "<pre>"; print_r($res); echo "</pre>";
	}
	
	private function unsetMissedCallActivity($activity_id) {
		$method = "crm.activity.update";
		$url = $this->webHook.$method;
		
		$fields = ['PRIORITY' => 2,  "COMPLETED" => "Y"];
		
		$arParams = array(
			"ID" => $activity_id,
			"fields" => $fields,
			"params" => ["REGISTER_SONET_EVENT" => "N"],					
		);
		
		//echo "<pre>"; print_r($arParams); echo "</pre>";
		
		$resultJson = $this->BxQuery->RestApiQuery($url, $arParams);
		$res = json_decode($resultJson, true);
	}
	
	
	private function buildActivitySubject($id, $status, $notise = ""){
		$method = "crm.activity.get";
		$url = $this->webHook.$method;
		
		if($notise != "");
		
		$arParams = ['id' => $id];
		$resultJson = $this->BxQuery->RestApiQuery($url, $arParams);
		
		$res = json_decode($resultJson, true);
		
		switch ($status) {
			case "200":
				$sub = " - Успешный звонок".$notise;
				break;
			
			case "304":
				$sub = " - Пропущеный звонок".$notise;
				break;
			
			case "603":
				$sub = " - Сбросили/Занято".$notise; 
				break;
			
			case "603-S":
				$sub = " - Не подняли трубку".$notise; 
				break;

			default:
				$sub = "";
				break;
		}
		
		if(isset($res['result']['SUBJECT'])){
			$subject = $res['result']['SUBJECT'];
			
			$subject = str_replace("Исходящий на ", "ИСХ ".$sub." ", $subject);
			$subject = str_replace("Входящий от ", "ВХ ".$sub." ", $subject);
			
			if(strpos($subject, ANONYMOS_CALL['Anonymous']) !== false){
				$subject = "ВХ ".$sub.", тел. не определен";
			}
			
		} else {
			$subject = "";
		}
		
		//echo "<pre>"; print_r($res); echo "</pre>";
		
		
		//echo "<pre>"; print_r($subject); echo "</pre>";
		return $subject;
	}
	
	private function logicIf ($arField, $arAnswerParams){
		
		$a = $arField['a'];
		$b = $arField['b'];
		$logical = $arField['logical'];
		
		
		switch ($logical) {
			case "==":
				$res = ['status' => 'ok', 'METHOD_STATUS' => 'OK', 'logical result' => ($a == $b ? 'TRUE' : 'FALSE')];
				break;

			case "!=":
				$res = ['status' => 'ok', 'METHOD_STATUS' => 'OK', 'logical result' => ($a != $b ? 'TRUE' : 'FALSE')];								
				break;			
			
			case "<":
				$res = ['status' => 'ok', 'METHOD_STATUS' => 'OK', 'logical result' => ($a < $b ? 'TRUE' : 'FALSE')];				
				break;
			
			case ">":
				$res = ['status' => 'ok', 'METHOD_STATUS' => 'OK', 'logical result' => ($a > $b ? 'TRUE' : 'FALSE')];				
				break;
			default:
				$res = ['error' => true, 'METHOD_STATUS' => 'ERR', 'error_description' => "$logical - there is no such logical operand"];				
				break;
			
		}
		
		$this->isResultSuccess(__METHOD__, $res, $arAnswerParams);
		return json_encode(['result' => $res]);		
		
	}
	
		
	// вынести в класс PhoneLogic
	public function addCompositeMethod($arMethod, &$previousArParams, &$previousArResult, &$last_name_method, &$error){
		
		foreach ($arMethod as $nameMethod => $method) {
			usleep(500000);
			if($error === true && $nameMethod !== "answer") {
				continue;
			}
			
			$method['PARAMS']['name_method'] = $nameMethod;
			
			$methodParams = $method['PARAMS'];
			$arAnswerParams = $method['ANSWER_PARAMS'];
			
			foreach ($methodParams as &$value) {
				$this->prepareCompositeValue($previousArParams, $previousArResult, $value);			
			}
								
			$arResult = json_decode($this->$nameMethod($methodParams, $arAnswerParams), true);
						
			if(isset($arResult['result']['error'])) {
				$error = true;
			}
			
			$this->setLastResult($previousArResult, $arResult['result'], $nameMethod);
			$this->setLastParams($previousArParams, $methodParams, $nameMethod);
			
			//echo "<pre>".$nameMethod."--"; print_r($error); echo "</pre>";
		
			if(isset($methodParams['ANSWER_PRINT']) && !empty(array_intersect($methodParams['ANSWER_PRINT'], ['ECHO', 'LOG']))){
				$this->printMethodResult($nameMethod, $arResult, $methodParams['ANSWER_PRINT']);				
			}
			
						
			if($nameMethod == "logicIf") {
								
				$arResultIf = json_decode($this->$nameMethod($methodParams, $arAnswerParams), true);
								
				if(isset($arResultIf['result']['error'])) {
					$error = true;
				} else {
					$logikKey = $arResultIf['result']['logical result'];
					foreach ($method[$logikKey] as $nameM => &$M) {
						$M['ANSWER_PARAMS'] = $this->PhoneLogic->getAnswerParams($nameM);
						$M['PARAMS']['ANSWER_PRINT'] = $method['PARAMS']['ANSWER_PRINT'];
						$arMet = [$nameM => $M];						
						$this->addCompositeMethod($arMet, $previousArParams, $previousArResult, $last_name_method, $error);					
					}
				}				
			}
		}
	}			
	
	
	// вынести в класс PhoneLogic
	private function setLastResult(&$arResult, $result, $nameMethod){
		$arResult['last_method'] = $result;
		$arResult[$nameMethod] = $result;
	}
	
	// вынести в класс PhoneLogic
	private function setLastParams(&$arParams, $params, $nameMethod){
		$arParams['last_method'] = $params;
		$arParams[$nameMethod] = $params;
	}
	
	private function isResultSuccess($method, &$result, $arAnswerParams) {
		$err_constant = 'ERR';
		$ok_constant = 'OK';

		$rey_error = $arAnswerParams['ERROR'];
		$key_success = $arAnswerParams['SUCCESS'];
		$key_error_description = $arAnswerParams['ERROR_DESCRIPTION'];
		
		$emptyResult = true;

		if (isset($arAnswerParams['SUCCESS_EMPTY_RESULT']) && $arAnswerParams['SUCCESS_EMPTY_RESULT'] !== true) {
			$emptyResult = false;
			
		}
		if (isset($result[$key_success]) && $emptyResult === false && empty($result[$key_success])) {
			$result[$rey_error] = true;
			$result[$key_error_description] = $arAnswerParams['SUCCESS_EMPTY_RESULT_TEXT'];
		}

		if (isset($result[$key_success]) && !isset($result[$rey_error])) {		
			$result['METHOD_STATUS'] = $ok_constant;	 
			
			//echo "<pre>"; print_r($result); echo "<pre>";			
			return true;
		} else if (isset($result[$rey_error])) {
			
			$result['error'] = true;
			$result['METHOD_STATUS'] = $err_constant;
			if (!isset($result[$key_error_description])) {
				$result[$key_error_description] = '';
			}			
			return false;
		}
	}

	
	
	private function printMethodResult($nameMethod, $arResult, $arSourceOutput) {
		if($nameMethod == 'answer') {
			return;
		}
		$arFields = ['status' => $arResult['result']['METHOD_STATUS'], 'name_method' => $nameMethod];
		
		if (isset($arResult['result']['error_description'])) {
			$arFields['error_description'] = $arResult['result']['error_description'];
		}
		$arFields['ANSWER_PRINT'] = $arSourceOutput;
		$this->answer($arFields);
	}

	private function prepareCompositeValue($previousArParams, $previousArResult, &$value) {
		if (isset($value['PREVIOUS_RESULT'])) {

			$prevResMethod = array_keys($value['PREVIOUS_RESULT'])[0];
			if (isset($previousArResult[$prevResMethod][$value['PREVIOUS_RESULT'][$prevResMethod]])) {
				$value = $previousArResult[$prevResMethod][$value['PREVIOUS_RESULT'][$prevResMethod]];
			} else {
				$value = "";
			}
		}
		if (isset($value['PREVIOUS_PARAMS'])) {
			$prevParMethod = array_keys($value['PREVIOUS_PARAMS'])[0];

			if (isset($previousArParams[$prevParMethod][$value['PREVIOUS_PARAMS'][$prevParMethod]])) {
				$value = $previousArParams[$prevParMethod][$value['PREVIOUS_PARAMS'][$prevParMethod]];
			} else {
				$value = "";
			}
		}
	}
	
	private function getAllDbCallId(){
		$jsonContent = $this->DB->read();
		if ($jsonContent !== false) {
			$content = json_decode($jsonContent, true);
			return $content;
		}
		return [];
	}
	
	private function writeMissedCall($activity_id, $external_number){
		$activity_id = self::ACTIVITY.$activity_id;
		return $this->writeCallId($external_number, $activity_id);
	}
	
	private function deleteMissedCallByActivity($activity_id){
		$activity_id = self::ACTIVITY.$activity_id;
		return $this->deleteCallId($activity_id);
	}
	
	private function getMissedCallByPhone($phone){
		$jsonContent = $this->DB->read();
		$content = json_decode($jsonContent, true);
		
		$arKey = array_keys($content, $phone);
		return $arKey;
	}
	
	private function writeCallId($call_id, $telephone_call_id) {
				
		$jsonContent = $this->DB->read();
		
		if ($jsonContent !== false) {
			$content = json_decode($jsonContent, true);

			if (is_array($content)) {
				$arOldConent = $content;				
			}
			$result = true;
			if(isset($arOldConent[$telephone_call_id])) {
				$result = $arOldConent[$telephone_call_id];
			}
			$arOldConent[$telephone_call_id] = $call_id;
			
			
			$newContent = json_encode($arOldConent);			
			if($this->DB->write($newContent)){
				return $result;
			}
			
		}
		return false;
	}
	
	private function getCallId($telephone_call_id){
		
		$jsonContent = $this->DB->read();
		
		if ($jsonContent !== false) {
			$content = json_decode($jsonContent, true);

			if (isset($content[$telephone_call_id])) {
				return $content[$telephone_call_id];		
			}
		}
		return false;
		
	}
	
	private function deleteCallId($telephone_call_id){
		
		$jsonContent = $this->DB->read();
		
		if ($jsonContent !== false) {
			$content = json_decode($jsonContent, true);

			if (isset($content[$telephone_call_id])) {
				$call_id = $content[$telephone_call_id];
				$key = array_search($call_id , $content);				
				if (isset($content[$key])) {
					unset($content[$key]);
				}				
				unset($content[$telephone_call_id]);
				if(is_array($content) && !empty($content)){
					return $this->DB->write(json_encode($content));
				} else {
					return $this->DB->delete();
				}				
			}
		}
		return false;
		
	}
	
}

