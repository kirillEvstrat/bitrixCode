<?
namespace DE;

use \Core\AbstractPhoneLogic;

class developPhoneLogic extends AbstractPhoneLogic {	
	
		
	public static function getInstance(){
		if(!is_object(self::$phoneLogic)){
			return self::$phoneLogic = new developPhoneLogic();
		}
		return self::$phoneLogic;
	}
	
	
	public function in($arFields) {		

		
		
		// массив для открытия карточек у пользователей
		$arFreeInnerNumber = explode("-", $arFields['free_inner_number']);
		
		if(empty($arFreeInnerNumber)) {
			$registerInnerNumber = 110;
		}
		//$registerInnerNumber = $arFreeInnerNumber[0];
		
		$registerInnerNumber = 110;

		$arMethod = [
			'getUserIdByInnerNumber' => [
				'PARAMS' => [
					'free_inner_number' => $arFreeInnerNumber,
					'notice' => $arFields['notice'],
				]
			],
			
			'registerCall' => [
				'PARAMS' => [
					'call_id' => $arFields['oktell_call_id'],
					'inner_number' => $registerInnerNumber ,
					'external_number' => $arFields['external_number'],
					'show' => 0,
					'call_start_date' => $arFields['call_start_date'],
					'type' => 2,					
				],				
			],		
		
			'showCall' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
					'call_id' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],					
				],				
			],
			
				
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					'call_id' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],
					//"previous_name_method" => ['PREVIOUS_PARAMS' => ['last_method' => 'name_method']],
					"previous_name_method" => "in",					
					"error_description" => ['PREVIOUS_RESULT' => ['last_method' => 'error_description']],
				]
			]		
			
		];
		return $arMethod;
	}
	
	public function in_call_up($arFields) {
		
		$arMethod = [
			'getUserIdByInnerNumber' => [
				'PARAMS' => [
					'free_inner_number' => $arFields['inner_number']
				]
			],
			
			'getUserByGruopId' => [
				'PARAMS' => [
					'user_group_id' => $arFields['user_group_id'],
					'iblock_id' => IBLOCK_ID
				]
			],
			
			'hideCall' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_RESULT' => ['getUserByGruopId' => 'USER_ID']],
					'callup_user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
					'call_id' => $arFields['oktell_call_id'],
				]
			],
			
			/*'listPhonecardHide' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'user_id']],
					'call_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
					'callup_user_id' =>  ['PREVIOUS_PARAMS' => ['hideCall' => 'callup_user_id']],
				]
			],*/
			
			'showCall' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'callup_user_id']],
					'call_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
				]
			],
			
						
			/*'listPhonecardShow' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_PARAMS' => ['showCall' => 'user_id']],
					'call_id' => ['PREVIOUS_PARAMS' => ['showCall' => 'call_id']],
				]
			],*/
			
			
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					"inner_number" => ['PREVIOUS_PARAMS' => ['getUserIdByInnerNumber' => 'free_inner_number']],
					"previous_name_method" => 'in_call_up',
					"error_description" => ['PREVIOUS_RESULT' => ['last_method' => 'error_description']],
				]
			],
			
		];
		return $arMethod;
	}
	
	public function finish_in($arFields) {
			
		$arMethod = [
			'getAllUserPhone' => [
				'PARAMS' => [
					'iblock_id' => IBLOCK_ID
				]
			],
						
			'hideCall' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_RESULT' => ['getAllUserPhone' => 'USER_ID']],
					'callup_user_id' => 0,
					'call_id' => $arFields['oktell_call_id'],
				]		
			],
			
			/*'listPhonecardHide' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'user_id']],
					'call_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
				]
			],*/
			
			'finishCall' => [
				'PARAMS' => [
					'inner_number' => $arFields['inner_number'],
					'call_id' =>  $arFields['oktell_call_id'],
					'duration' => $arFields['duration'],
					'status' => $arFields['status'],
					'record_url' => $arFields['record_url'],
					
				]
			],
			
			'updateActivity' => [
				'PARAMS' => [
					'activity_id' => ['PREVIOUS_RESULT' => ['last_method' => 'CRM_ACTIVITY_ID']],					
					'external_number' => $arFields['external_number'],
					'oktell_call_id' => ['PREVIOUS_PARAMS' => ['finishCall' => 'call_id']],
					'status' => ['PREVIOUS_PARAMS' => ['last_method' => 'status']],
					'notice' => $arFields['notice'],
				]
			],
			
			/*'getUserIdByInnerNumber' => [
				'PARAMS' => [
					'free_inner_number' => $arFields['inner_number'],					
				]
			],
			
			'updateLeadOknahome' => [
				'PARAMS' => [
					'external_number' => $arFields['external_number'],
					'inner_number' => $arFields['inner_number'],
					'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
				]
			],*/
			
			/*'updateWidget' => [
				'PARAMS' => []				
			],*/
					
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					"inner_number" => ['PREVIOUS_PARAMS' => ['finishCall' => 'inner_number']],
					"previous_name_method" => 'finish_in',
					"error_description" => ['PREVIOUS_RESULT' => ['last_method' => 'error_description']],
				]
			]			
		];
		return $arMethod;
	}
	
	
	public function out_from_bx($arFields) {		
		$arMethod = [
			'getInnerNumberByUserId' => [
				'PARAMS' => [
					'user_id' => $arFields['USER_ID']
				]
			],
			'sendOutCall' => [
				'PARAMS' => [
					'call_id' => $arFields['CALL_ID'],
					'inner_number' => ['PREVIOUS_RESULT' => ['getInnerNumberByUserId' => 'UF_PHONE_INNER']],
					'external_number' => $arFields['PHONE_NUMBER'],
				]
			]
		];
		return $arMethod;
	}

	public function out($arFields) {
		
		$arFreeInnerNumber = [$arFields['inner_number']];
		
		$arMethod = [
			'registerCall' => [
				'PARAMS' => [
					'call_id' => $arFields['oktell_call_id'],
					'inner_number' => $arFields['inner_number'],
					'external_number' => $arFields['external_number'],
					'show' => 0,
					'call_start_date' => $arFields['call_start_date'],
					'type' => 1
				],
				'ANSWER' => [
					'ECHO' => true,
					'LOG' => true,
				]
			],
			
			/*'getUserIdByInnerNumber' => [
				'PARAMS' => [
					'free_inner_number' => $arFields['inner_number'],					
				]
			],
			
			'updateLeadOknahome' => [
				'PARAMS' => [
					'external_number' => $arFields['external_number'],
					'inner_number' => $arFields['inner_number'],
					'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
				]
			],*/
			
			/*
			'getUserIdByInnerNumber' => [
				'PARAMS' => [
					'free_inner_number' => $arFreeInnerNumber,					
				]
			],	
			
			'listPhonecardShow' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
					'call_id' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],
				]
			],*/
			
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					"call_id" => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],
					"previous_name_method" => 'out',
					"error_description" => ['PREVIOUS_RESULT' => ['last_method' => 'error_description']],
				]
			]
		];
		return $arMethod;
	}

	public function sync($arFields) {
		
		$arMethod = [
			'registerCall' => [
				'PARAMS' => [
					'call_id' => $arFields['oktell_call_id'],
					'inner_number' => $arFields['inner_number'],
					'external_number' => $arFields['external_number'],
					'show' => 0,
					'call_start_date' => $arFields['call_start_date'],
					'type' => ($arFields['call_type'] == "in" ? 2 : 1),
				]
			],
			
			'finishCall' => [
				'PARAMS' => [
					'inner_number' => ['PREVIOUS_PARAMS' => ['registerCall' => 'inner_number']],
					'call_id' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],
					'duration' => $arFields['duration'],
					'status' => $arFields['status'],
					'record_url' => $arFields['record_url'],
				]
			],
			
			'updateActivity' => [
				'PARAMS' => [
					'activity_id' => ['PREVIOUS_RESULT' => ['last_method' => 'CRM_ACTIVITY_ID']],					
					'external_number' => $arFields['external_number'],
					'oktell_call_id' => ['PREVIOUS_PARAMS' => ['finishCall' => 'call_id']],
					'status' => ['PREVIOUS_PARAMS' => ['last_method' => 'status']],
				]
			],
			
			/*'getUserIdByInnerNumber' => [
				'PARAMS' => [
					'free_inner_number' => $arFields['inner_number'],					
				]
			],
			
			'updateLeadOknahome' => [
				'PARAMS' => [
					'external_number' => $arFields['external_number'],
					'inner_number' => $arFields['inner_number'],
					'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
					'call_start_date' => $arFields['call_start_date'],
				]
			],*/
			
			/*'updateWidget' => [
				'PARAMS' => []				
			],*/
			
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					"id" => $arFields['id'],
					"previous_name_method" => 'sync',
					"error_description" => ['PREVIOUS_RESULT' => ['last_method' => 'error_description']],
				]
			]
		];
		return $arMethod;
	}
	
	public function finish_out($arFields) {
				
		$arMethod = [
			'getAllUserPhone' => [
				'PARAMS' => [
					'iblock_id' => IBLOCK_ID
				],
			],
			
			'hideCall' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_RESULT' => ['getAllUserPhone' => 'USER_ID']],
					'callup_user_id' => 0,
					'call_id' => $arFields['oktell_call_id'],
				],
			],
			
			/*'listPhonecardHide' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'user_id']],
					'call_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
				]
			],*/			
			
			'finishCall' => [
				'PARAMS' => [
					'inner_number' => $arFields['inner_number'],
					'call_id' =>  ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
					'duration' => $arFields['duration'],
					'status' => $arFields['status'],
					'record_url' => $arFields['record_url'],
				]
			],
			
			/*'getUserIdByInnerNumber' => [
				'PARAMS' => [
					'free_inner_number' => $arFields['inner_number'],					
				]
			],
			
			'updateLeadOknahome' => [
				'PARAMS' => [
					'external_number' => $arFields['external_number'],
					'inner_number' => $arFields['inner_number'],
					'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
				]
			],*/
			
			/*'updateWidget' => [
				'PARAMS' => []				
			],*/
			
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					"call_id" => ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
					"previous_name_method" => 'finish_out',
					"error_description" => ['PREVIOUS_RESULT' => ['last_method' => 'error_description']],					
				],				
			]
		];
		
		return $arMethod;
	}
	
	public function getuser($arFields){
		$arMethod = [
			'getAllUserPhone' => [
				'PARAMS' => [
					'iblock_id' => IBLOCK_ID
				],
			],
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					'user_id' => ['PREVIOUS_RESULT' => ['getAllUserPhone' => 'USER_ID']],
					"previous_name_method" => 'getAllUserPhone',
					"error_description" => ['PREVIOUS_RESULT' => ['last_method' => 'error_description']],					
				],				
			]
		];
		return $arMethod;
	}
	
	
}
