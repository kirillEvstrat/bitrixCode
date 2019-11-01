<?php

namespace DE;

use \Core\AbstractPhoneLogic;

class PhoneLogic extends AbstractPhoneLogic {	
	
		
	public static function getInstance(){
		if(!is_object(self::$phoneLogic)){
			return self::$phoneLogic = new PhoneLogic();
		}
		return self::$phoneLogic;
	}
	
	
	public function in($arFields) {		

		//echo "<pre>*--"; print_r($arFields); echo "</pre>";
		$arFreeInnerNumber = explode("-", $arFields['free_inner_number']);
		$registerInnerNumber = $arFreeInnerNumber[0];

		$arMethod = [
			'registerCall' => [
				'PARAMS' => [
					'call_id' => $arFields['oktell_call_id'],
					'inner_number' => $registerInnerNumber ,
					'external_number' => $arFields['external_number'],
					'show' => 0,
					'call_start_date' => $arFields['datetime'],
					'type' => 2,									
				],				
			],

			'getUserIdByInnerNumber' => [
				'PARAMS' => [
					'free_inner_number' => $arFreeInnerNumber,					
				]
			],

			
			'logicIf' => [
				'PARAMS' => [
					'a' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],
					'logical' => '==',
					'b' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],
				],
				
				'TRUE' => [
					'showCall' => [
						'PARAMS' => [
							'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
							'call_id' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],					
						],				
					],
				],
				
				'FALSE' => [
					'showCall' => [
						'PARAMS' => [
							'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
							'call_id' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],					
						],				
					],
				],
			],
			
			/*'showCall' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_RESULT' => ['getUserIdByInnerNumber' => 'ID']],
					'call_id' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],					
				]					
			],*/
			
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					"call_id" => ['PREVIOUS_PARAMS' => ['showCall' => 'call_id']],
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
			
			'showCall' => [
				'PARAMS' => [
					'user_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'callup_user_id']],
					'call_id' => ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
				]
			],
			
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
			
			'finishCall' => [
				'PARAMS' => [
					'inner_number' => $arFields['inner_number'],
					'external_number' => $arFields['external_number'],
					'call_id' =>  ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
					'duration' => $arFields['duration'],
					'status' => $arFields['status'],
					'record_url' => $arFields['record_url'],
				]
			],
					
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
				
		$arMethod = [
			'registerCall' => [
				'PARAMS' => [
					'call_id' => $arFields['oktell_call_id'],
					'inner_number' => $arFields['inner_number'],
					'external_number' => $arFields['external_number'],
					'show' => 1,
					'call_start_date' => $arFields['datetime'],
					'type' => 1
				],
				'ANSWER' => [
					'ECHO' => true,
					'LOG' => true,
				]
			],
			
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
					'call_start_date' => $arFields['datetime'],
					'type' => $arFields['type']
				]
			],
			
			'finishCall' => [
				'PARAMS' => [
					'inner_number' => ['PREVIOUS_PARAMS' => ['registerCall' => 'inner_number']],
					'external_number' => ['PREVIOUS_PARAMS' => ['registerCall' => 'external_number']],
					'call_id' => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],
					'duration' => $arFields['duration'],
					'status' => $arFields['status'],
					'record_url' => $arFields['record_url'],
				]
			],
			
			'answer' => [
				'PARAMS' => [
					"status" => ['PREVIOUS_RESULT' => ['last_method' => 'METHOD_STATUS']],
					"call_id" => ['PREVIOUS_RESULT' => ['registerCall' => 'CALL_ID']],
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
			
			'finishCall' => [
				'PARAMS' => [
					'inner_number' => $arFields['inner_number'],
					'external_number' => $arFields['external_number'],
					'call_id' =>  ['PREVIOUS_PARAMS' => ['hideCall' => 'call_id']],
					'duration' => $arFields['duration'],
					'status' => $arFields['status'],
					'record_url' => $arFields['record_url'],
				]
			],	
			
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
}
