<?php

class changeRegion extends CBitrixComponent {
	
	public function executeComponent() {
		
		$this->arResult['ENTITY'] = $this->arParams['ENTITY'];
        $this->arResult['TAX_ID'] = $this->getCurrentTax();
		$this->arResult['departments'] = $this->getVATCatalog();
		foreach ($this->arResult['departments'] as $i =>$department){
		    if($department["ID"]=== $this->arResult['TAX_ID']){
                $this->arResult['TAX_VALUE'] = $department['RATE'];
            }
        }
        $this->arResult['AJAX_LINK'] = $this->getAjaxLink();
        $this ->addFields();

        $this->includeComponentTemplate();
	}

    private function addFields(){

        global $USER_FIELD_MANAGER;

        //echo "<pre>res="; print_r($USER_FIELD_MANAGER); echo "</pre>";

        $entity_id = "CRM_".strtoupper($this->arResult['ENTITY']);

        $CCrmFields = new CCrmFields($USER_FIELD_MANAGER, $entity_id);

        if($CCrmFields->GetByName('UF_CRM_TAX_DEPARTMENT'))
            return;

        $arField = ['USER_TYPE_ID' => 'string',
                'ENTITY_ID' => $entity_id,
                'SORT' => 9999,
                'MULTIPLE' => 'N',
                'MANDATORY' => 'N',
                'SHOW_FILTER' => 'N',
                'SHOW_IN_LIST' => 'N',
                'LIST_FILTER_LABEL' => [
                    'en' => '__tax',
                    'ru' => '__tax'
                ],
                'LIST_COLUMN_LABEL' => [
                    'en' => '__tax',
                    'ru' => '__tax'
                ],
                'EDIT_FORM_LABEL' => [
                    'en' => '__tax',
                    'ru' => '__tax'
                ],
                'SETTINGS' => [
                    'DEFAULT_VALUE' => 'N'
                ],
                'FIELD_NAME' => 'UF_CRM_TAX_DEPARTMENT'

            ];


        $res = $CCrmFields->AddField($arField);


    }

    private function getCurrentTax(){
        \Bitrix\Main\Loader::includeModule('crm');
        $ar= CCrmDeal::GetList([], ["ID"=>$this->arParams['ENTITY_ID']], ["UF_*"]);
        $deal = $ar->fetch();
        return $deal['UF_CRM_1560259756'];


    }


	private function getVATCatalog(){
        \Bitrix\Main\Loader::includeModule('catalog');
        $VATCatalog = [];
        $test = CCatalogVat::GetList([], [], false, false, ['*']);
        while ($elem = $test->fetch()){

            $VATCatalog[] = $elem;
        }
        return $VATCatalog;
    }
	

	private function getAjaxLink(){				
		$pathURL = str_replace ($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__));		
		return $pathURL.'/ajax.php';
	}
	
	

	
}