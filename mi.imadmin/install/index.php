<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

if (class_exists('mi_imadmin')) {
    return;
}

class mi_imadmin extends CModule
{
    /** @var string */
    public $MODULE_ID;
    
	/** @var string */
    public $MODULE_VERSION;
   
	/** @var string */
    public $MODULE_VERSION_DATE;
    
	/** @var string */
    public $MODULE_NAME;
    
	/** @var string */
    public $MODULE_DESCRIPTION;
    
	/** @var string */
    public $MODULE_GROUP_RIGHTS;
   
	/** @var string */
    public $PARTNER_NAME;
    
	/** @var string */
    public $PARTNER_URI;
    
	
	public function __construct() {
		$arModuleVersion = [];

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = Loc::getMessage('MI.INTEGRATION_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MI.INTEGRATION_MODULE_DESCRIPTION');		
		
        $this->MODULE_ID = 'mi.imadmin';       
        
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = "Mitgroup";
        $this->PARTNER_URI = "http://mitgroup.ru/";
    }
   
	
	public function doInstall() {
        ModuleManager::registerModule($this->MODULE_ID);
		//$this->installDB();
        $this->installEvents();
		$this->installFiles();
    }
    
	
	public function doUninstall(){
        //$this->uninstallDB();
		$this->uninstallEvents();
		$this->uninstallFiles();
        ModuleManager::unregisterModule($this->MODULE_ID);
    }
	
    
	/*
	public function installDB(){
		
        if (Loader::includeModule($this->MODULE_ID)) {
			TreeTable::getEntity()->createDbTable();
			TlgcpTable::getEntity()->createDbTable();
			AutoTable::getEntity()->createDbTable();
        }
    }   
	
	public function uninstallDB() {
        if (Loader::includeModule($this->MODULE_ID)) {
            $connectionAW = Application::getInstance()->getConnection("aw");
			$connectionAW->dropTable(TreeTable::getTableName());
			$connectionAW->dropTable(TlgcpTable::getTableName());
			$connectionAW->dropTable(AutoTable::getTableName());
        }
    }
	*/
	
	public function installEvents(){
		$eventManager = EventManager::getInstance();		
		//$eventManager->registerEventHandlerCompatible("main", "OnUserTypeBuildList", "mi.crmfields", "Mi\CrmFields\CrmPriorityType", "GetUserTypeDescription");
		/*$eventManager->registerEventHandlerCompatible("crm", "OnBeforeCrmCompanyUpdate", "mi.crm", "\MI\Crm\NewCrmCard", "companyCrmCardCheckPermsField");		
		$eventManager->registerEventHandlerCompatible("crm", "OnBeforeCrmContactAdd", "mi.crm", "\MI\Crm\NewCrmCard", "contactCrmCardCheckPermsField");
		$eventManager->registerEventHandlerCompatible("crm", "OnBeforeCrmContactUpdate", "mi.crm", "\MI\Crm\NewCrmCard", "contactCrmCardCheckPermsField");	*/				
		return true;
	}	
	
	public function uninstallEvents(){
		$eventManager = EventManager::getInstance();		
		//$eventManager->unRegisterEventHandler("main", "OnUserTypeBuildList", "mi.crmfields", "Mi\CrmFields\CrmPriorityType", "GetUserTypeDescription");
		/*$eventManager->unRegisterEventHandler("crm", "OnBeforeCrmCompanyUpdate", "mi.crm", "\MI\Crm\NewCrmCard", "companyCrmCardCheckPermsField");		
		$eventManager->unRegisterEventHandler("crm", "OnBeforeCrmContactAdd", "mi.crm", "\MI\Crm\NewCrmCard", "contactCrmCardCheckPermsField");
		$eventManager->unRegisterEventHandler("crm", "OnBeforeCrmContactUpdate", "mi.crm", "\MI\Crm\NewCrmCard", "contactCrmCardCheckPermsField");
		*/			
		return true;
	}
	
	
	
	function installFiles(){
		/*CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/components', 
			$_SERVER['DOCUMENT_ROOT'].'/local/components', true, true);
		
		CopyDirFiles(
			$_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$this->MODULE_ID.'/install/js', 
			$_SERVER['DOCUMENT_ROOT'].'/local/js/'.$this->MODULE_ID, true, true);*/	
		
		return true;
	}	
	

	public function uninstallFiles(){
		
		return true;
	}
}

