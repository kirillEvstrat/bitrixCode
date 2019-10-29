<?
session_start();
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * Parameters:
 *	AUTH_RESULT - Authorization result message
 *	NOT_SHOW_LINKS - Whether to show links to register page && password restoration (Y/N)
 */

if(strlen($_POST['problem'])>0){
    $problem = $_POST['problem'] === "1" ? "Неверные личные данные" : "Восстановление пароля";
    $text = strip_tags($_POST['text']);
    $contact = strip_tags($_POST['contact-info']);
    if(CModule::IncludeModule("im")){
            $arMessageFields = array(
                "TO_USER_ID"     => 1, // получатель
                "FROM_USER_ID"   => 0, // отправитель (может быть >0)
                "NOTIFY_TYPE"    => IM_NOTIFY_SYSTEM, // тип уведомления
                "NOTIFY_MODULE"  => "main", // модуль запросивший отправку уведомления
                "NOTIFY_TAG"     => "", // символьный тэг для группировки (будет выведено только одно сообщение), если это не требуется - не задаем параметр
                // текст уведомления на сайте (доступен html и бб-коды)
                "NOTIFY_MESSAGE" => "<b>Проблема при авторизации: {$problem}</b>[br]<b> Текст  обращения: </b> {$text}[br]<b>Контактные данные сотруника: </b>{$contact}",
            );
            CIMNotify::Add($arMessageFields);
    }
    $arResult['send']=true;

}
$arResult['back_url'] = $_SERVER["HTTP_REFERER"];

$this->IncludeComponentTemplate();
