<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');
\CModule::IncludeModule('crm');
if(isset($_POST['action'])){
	if($_POST['action'] == 'set_region_id'){

	    $regionId =$_POST['regionId'];
        $entity_id = $_POST['entity_id'];
        $CCrmDeal = new CCrmDeal(false);
		$arFields = [
			'UF_CRM_1560259756' =>$regionId
		];
		$res = $CCrmDeal->Update($entity_id, $arFields);			
		
		$JSON = json_encode([$arFields, $_POST]);
		echo $JSON;
		
	} 
}

