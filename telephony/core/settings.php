<?php
error_reporting(E_ALL);
if (version_compare(phpversion(), '5.1.0', '<') == true) { die ('PHP5.1 Only'); }
date_default_timezone_set('Europe/Minsk');
// Константы:

// --------------------------- заполняем для каждого приложения --------------------------------------------


//--------------------------------------------------------------------------------------
// домен и настройки авторизации приложения Б24

define("WEB_HOOK_DOMEN", "");


define("WEB_HOOK", WEB_HOOK_DOMEN."/");

// токен исходящего web_hook
define("KEY", "");

//define("KEY", "***********");



// адрес телефонии
define('URL_TELEPHONY_DOMEN', "");

// домен и патч приложаения
define("PROTOCOL_APP", "http");
define("DOMAIN_APP", "");


// ID списка групп сотрудников
define("IBLOCK_ID", 54);
define("IBLOCK_GROUP_NUMBER", "PROPERTY_108");
define("IBLOCK_AR_GROUP_USER_ID", "PROPERTY_110");






//--------------------------------------------------------------------------------------

define("CLIENT_PATH", "/telephony/index.php");
define("URL_TELEPHONY_CALL_RECCORD", URL_TELEPHONY_DOMEN);
// вызов исходящего звонка в телефонию
define("URL_TELEPHONY", URL_TELEPHONY_DOMEN."/?type=reg_out");


// анонимные телефоны
define('ANONYMOS_CALL', ['Anonymous' => '375290000000']);

// --------------------------------------------------------------------------------------------------------

// ----- huck for out-call handler ---------------
	if (isset($_POST['data'])) {
		$_GET = $_GET + $_POST['data'] + $_POST;
		unset($_POST['POST']['data']);
	}
	
	if (isset($_POST['auth']['application_token'])) {
		$_GET['key'] = $_POST['auth']['application_token'];
		unset($_POST['auth']['application_token']);
	}
// ----- end huck -------------------------------
	
// --------- huck for sync - get oktell_call_id
	
if(isset($_GET["action"]) && $_GET["action"] == 'sync') {
	$_GET['oktell_call_id'] = substr(str_shuffle(""), 0, 20);
}	
// ----- end huck -------------------------------	



define ('DS', DIRECTORY_SEPARATOR);	
define("PATH_URL", str_replace("index.php", "", $_SERVER['PHP_SELF']));
define("FULL_PATH_URL", $_SERVER['REQUEST_URI']);

//путь до файлов сайта
$site_path = realpath(dirname(__FILE__) . DS . '..' . DS) . DS;
define ('SITE_PATH', $site_path);

//путь к папке пользовательских классов 
define ('CLASSES_LIB_DIR', SITE_PATH . "lib".DS."classes".DS);

//путь к папке системных классов 
define ('CLASSES_CORE_DIR', SITE_PATH . "core".DS."classes".DS);




spl_autoload_register(function($full_class_name) {	
	$file = str_replace('\\', DS, $full_class_name);	
	
	$class_name = substr($full_class_name, strripos($full_class_name,"\\") + 1);
	$namespace = substr($full_class_name, 0, strripos($full_class_name,"\\"));
	$namespace = strtolower($namespace);
	
	$file = $namespace.DS.$class_name.'.php';
	
	if($namespace == "core") {
		$file = $class_name.'.php';
		$file = CLASSES_CORE_DIR. $file;
	} else {
		$file = $namespace.DS.$class_name.'.php';
		$file = CLASSES_LIB_DIR . $file;
	}
		
	if(file_exists($file)) {
        include ($file);    
    } else {
		echo "Settings: error, not found file: ".$file."<br>";
	}		
});