<?php
namespace KE;


require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
\CModule::IncludeModule('crm');
\Bitrix\Main\Loader::includeModule('bizproc');
\CModule::IncludeModule('im');
\CModule::IncludeModule("intranet");
\CModule::IncludeModule("iblock");

class Agreement{

    private static $notificationFLag = false ;

    private function writeToLog($data) {
        $log = "\n------------------------\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/log228.txt", $log, FILE_APPEND);
    }

    public function onAfterCrmInvoiceAdd(&$arFields){
        global $USER;

        $PROP = array();
        $PROP[164] = $arFields['ID'];
        $PROP[165] = $arFields['UF_DEAL_ID'];
        $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            "IBLOCK_ID"      => 39,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => "счет",
            "ACTIVE"         => "Y",            // активен
        );

        $el = new \CIBlockElement;
        $idOfEl = $el->Add($arLoadProductArray);
        $arErrorsTmp = array();

        $wfId = \CBPDocument::StartWorkflow(
            115,
            [ 'lists', 'Bitrix\Lists\BizprocDocumentLists' , $idOfEl ],
            [],
            $arErrorsTmp
        );

    }

     public  function  OnAfterIBlockElementAdd($arFields){
         global $USER;
         $invoiceId = $arFields['i'];
         $dealId = $arFields['d'];
         $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($dealId), "CHECK_PERMISSIONS"=>"N"],false,false,  ["*", "UF_*"])->Fetch();
         $date = $deal['BEGINDATE'];
         $title  = $deal['TITLE'];


         $CCrmInvoice =  new \CCrmInvoice(false);
         $invoice =  $CCrmInvoice->GetList([],["ID"=>intval($invoiceId), "CHECK_PERMISSIONS"=>"N"],false,false,  ["*"])->Fetch();
         $responsibleID = $invoice['RESPONSIBLE_ID'];

         $user = \CUser::GetByID($responsibleID)->Fetch();
        $fio = $user['LAST_NAME']." ".substr($user['NAME'], 0 , 1).". ".substr($user['SECOND_NAME'], 0 , 1).".";

        $fields = array(
             'UF_CRM_1562855359' => $title,
             'UF_CRM_1562855373' => $date,
             'UF_CRM_1564668662' => $fio

         );

         try {
             $res = $CCrmInvoice->Update($invoiceId, $fields);
         } catch (Main\DB\SqlQueryException $e) {
             //self::writeToLog($e);
         }
    }

    public   function  onBeforeCrmInvoiceSetStatus(&$arFields){
        if($arFields['STATUS_ID']=="1"){ //выставить ттн
            $bossId = self::getBoss($arFields['ASSIGNED_BY_ID']);
            $invoice =  \CCrmInvoice::GetList([],["ID"=>intval($arFields["ID"])],false,false,  ["*", "UF_*"])->Fetch();
            $dealId = $invoice['UF_DEAL_ID'];
            $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($dealId)],false,false,  ["*", "UF_*"])->Fetch();
            $document = $deal['UF_CRM_1562215555'];
            $bossId = self::getBoss($deal['ASSIGNED_BY_ID']);
            if($document==""){
                $title = "В Вашей сделке оставлено замечание по согласованию договора!";
                $dealhref = "https://{$_SERVER["SERVER_NAME"]}/crm/deal/details/{$dealId}/";
                $message= "Невозможно перевести счет в статус 'ТТН на основании счета' т.к в <a href='{$dealhref}'>сделке №{$dealId}</a>  необходимо прикрепить скан-копию договора!";
                self::sendNotify($title, $message, $deal['ASSIGNED_BY_ID'], $bossId);

                \CCrmInvoice::Update($arFields['ID'], ['STATUS_ID' => $arFields['STATUS_ID']]);
                return false;

            }

        }
    }

    public function  onAfterCrmDealUpdate(&$arFields){


        //согласовано руководителем
        if(isset($arFields['UF_CRM_1562062792']) && $arFields['UF_CRM_1562062792']!==""){
            self::agreeByBoss($arFields['ID']);
        }
        //согласовано  юристом
        if(isset($arFields['UF_CRM_1562063096']) && $arFields['UF_CRM_1562063096']!=="" ){
            self::agreeByLawyer($arFields['ID']);
        }
        //отправить в ресстр
        if($arFields['UF_CRM_1561560831']===1){
            self::returnedToRegister($arFields['ID']);
        }

        if(isset($arFields['UF_CRM_1562233863']) ){ //комментарии менеджера
            $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($arFields["ID"])],false,false,  ["*", "UF_*"])->Fetch();
            $bossAgreement = self::getListValue($deal['UF_CRM_1562062792']);
            $lawyerAgreement = self::getListValue($deal['UF_CRM_1562063096']);

            $bossId = self::getBoss($arFields['ASSIGNED_BY_ID']);
            $title = "Комментарий менеджера!";
            $dealhref = "https://{$_SERVER["SERVER_NAME"]}/crm/deal/details/{$arFields['ID']}/";
            $message= "[br]В <a href='{$dealhref}'>сделке №{$arFields['ID']}</a> менеджер оставил комментарий по факту отклонения согласования документа[br] Текст комментария: {$arFields['UF_CRM_1562233863']}";
            self::sendNotify($title, $message, $bossId, $arFields['MODIFY_BY_ID']);

            $dealType = self::getListValue($deal['UF_CRM_1561473672']);
            if($dealType  === "не наша форма"){

                self::sendNotify($title, $message, 12, $arFields['MODIFY_BY_ID']);
            }
        }

        if(isset($arFields['UF_CRM_1561699810']) ) { //загружена "не наша форма" (менеджером)
            $bossId = self::getBoss($arFields['ASSIGNED_BY_ID']);
            $title = "Загружена форма документа!";
            $dealhref = "https://{$_SERVER["SERVER_NAME"]}/crm/deal/details/{$arFields['ID']}/";
            $message= "[br]В <a href='{$dealhref}'>сделке №{$arFields['ID']}</a> загружена форма документа! (поле 'форма документа' в разделе 'согласование договора' карточки )";
            self::sendNotify($title, $message, $bossId, $arFields['ASSIGNED_BY_ID']);
        }

        if(isset($arFields['UF_CRM_1562215555']) ) { //загружен скан(помощником)
            $title = "Загружена форма документа!";
            $dealhref = "https://{$_SERVER["SERVER_NAME"]}/crm/deal/details/{$arFields['ID']}/";
            $message= "[br]В <a href='{$dealhref}'>сделке №{$arFields['ID']}</a> помощником загружен скан договора! (поле 'скан документа' в разделе 'согласование договора' карточки )";
            self::sendNotify($title, $message, $arFields['ASSIGNED_BY_ID'], $arFields['MODIFY_BY_ID']);
        }
    }

    public function onBeforeCrmDealUpdate(&$arFields){
        if(isset($arFields['UF_CRM_1562062792'])){ //согласовано с руководителем

            $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($arFields["ID"])],false,false,  ["*", "UF_*"])->Fetch();
            $oldState = $deal['UF_CRM_1562062792'];
            if((isset($oldState) && $oldState!==$arFields['UF_CRM_1562062792']) || (!isset($oldState) && $arFields['UF_CRM_1562062792']!=="" )){
                if(self::checkPermissionsBoss($arFields['MODIFY_BY_ID'], $arFields["ID"], $arFields) === false) {
                    return false;
                }
                if(self::checkRemarksBoss($arFields) === false) {
                    return false;
                }
            }
        }

        if(isset($arFields['UF_CRM_1562063096']) ){ // согласовано юристом
            $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($arFields["ID"])],false,false,  ["*", "UF_*"])->Fetch();
            $oldState = $deal['UF_CRM_1562063096'];

            if( (isset($oldState) && $oldState!==$arFields['UF_CRM_1562063096']) || (!isset($oldState) && $arFields['UF_CRM_1562063096']!=="" ) ){
                if(self::checkPermissionsUrist($arFields['MODIFY_BY_ID'], $arFields["ID"], $arFields) === false) {
                    return false;
                }
                if(self::checkRemarksUrist($arFields) === false) {
                    return false;
                }
            }
        }
        if(isset($arFields['UF_CRM_1562062355']) ){ //замечания по согласованию руководителя
            $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($arFields["ID"])],false,false,  ["*", "UF_*"])->Fetch();
            $oldState = $deal['UF_CRM_1562062355'];


            if( (isset($oldState) && $oldState!==$arFields['UF_CRM_1562062355']) || (!isset($oldState) && $arFields['UF_CRM_1562062355']!=="" ) ){

                $managerCommentsOld = $deal['UF_CRM_1562233863'];
                $managerCommentsNew = $arFields['UF_CRM_1562233863'];
                if(self::checkPermissionsBoss($arFields['MODIFY_BY_ID'], $arFields["ID"], $arFields) === false) {
                    if(isset($managerCommentsNew) && $managerCommentsNew!==$managerCommentsOld && $managerCommentsNew!==""){
                        $arFields['UF_CRM_1562062355'] = $oldState;
                    }
                    else{
                        $arFields['RESULT_MESSAGE'] = "Изменение поля доступно только для Руководителя!";
                        return false;
                    }
                }
            }
        }

        if(isset($arFields['UF_CRM_1562594222']) ){ //замечания по согласованию юриста
            $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($arFields["ID"])],false,false,  ["*", "UF_*"])->Fetch();
            $oldState = $deal['UF_CRM_1562594222'];

            if( (isset($oldState) && $oldState!==$arFields['UF_CRM_1562594222']) || (!isset($oldState) && $arFields['UF_CRM_1562594222']!=="" ) ){
                $managerCommentsOld = $deal['UF_CRM_1562233863'];
                $managerCommentsNew = $arFields['UF_CRM_1562233863'];
                if(self::checkPermissionsUrist($arFields['MODIFY_BY_ID'], $arFields["ID"], $arFields) === false  ) {
                    if( (isset($managerCommentsNew) && $managerCommentsNew!==$managerCommentsOld && $managerCommentsNew!=="")  ){

                    $arFields['UF_CRM_1562594222'] = $oldState;
                    }

                    else{
                        $arFields['RESULT_MESSAGE'] = "Изменение поля доступно только для Юриста!";
                        return false;
                    }

                }
            }
        }

    }

    private function sendNotify($title, $message, $userIdTo, $userIdFrom){
        \CIMMessenger::Add(array(
            'TITLE' => $title,
            'MESSAGE' => $message,
            'TO_USER_ID' => $userIdTo,
            'FROM_USER_ID' => $userIdFrom,
            'MESSAGE_TYPE' => 'S', # P - private chat, G - group chat, S - notification
            'NOTIFY_MODULE' => 'intranet',
            'NOTIFY_TYPE' => 2,  # 1 - confirm, 2 - notify single from, 4 - notify single
        ));
    }

    private function getBoss($dealManagerId){
        $user = new \CUser();
        $rs = $user->GetList($by="ID", $order="desc", Array("ID" =>$dealManagerId), ['CHECK_PERMISSOINS' => 'N', 'SELECT' => ['*', "UF_*"]]);
        if(intval($rs->SelectedRowsCount())>0) {
            $arManagerField = $rs->Fetch();
            $managerDepartments =$arManagerField['UF_DEPARTMENT'];
            $arManagers = \CIntranetUtils::GetDepartmentManager($managerDepartments, $arManagerField["ID"], true);
            foreach ($arManagers as $key => $value)
            {
                $arUserID = $value['ID'];
                break;
            }
        }
        return $arUserID;
    }

    private function checkPermissionsBoss($userId, $dealId, &$arFields){
        global $USER;
        $dealManagerId = \CCrmDeal::GetByID($dealId)['ASSIGNED_BY_ID'];

        $isAdmin = $USER->IsAdmin();

        $user = new \CUser();
        $rs = $user->GetList($by="ID", $order="desc", Array("ID" =>$dealManagerId), ['CHECK_PERMISSOINS' => 'N', 'SELECT' => ['*', "UF_*"]]);
        if(intval($rs->SelectedRowsCount())>0) {
            $arManagerField = $rs->Fetch();
            $managerDepartments =$arManagerField['UF_DEPARTMENT'];
            $arManagers = \CIntranetUtils::GetDepartmentManager($managerDepartments, $arManagerField["ID"], true);
            foreach ($arManagers as $key => $value)
            {
                $arUserIDs[] = $value['ID'];

            }
        }
        $rs = $user->GetList($by="ID", $order="desc", Array("ID" =>$userId), ['CHECK_PERMISSOINS' => 'N', 'SELECT' => ['*', "UF_*"]]);
        if(intval($rs->SelectedRowsCount())>0) {
            $arCurrentUserField = $rs->Fetch();
        }

        $isBoss = false;
        foreach ($arUserIDs as $key =>$id){
            if($id === $arCurrentUserField["ID"]){
                $isBoss = true;
                break;
            }
        }

        if($isBoss===false && $isAdmin===false){
            $arFields['RESULT_MESSAGE'] = "Изменение поля доступно только для руководителя отдела!";
            return false;
        }

    }

    private function checkPermissionsUrist($userId, $dealId, &$arFields){
        global $USER;
        $isAdmin = $USER->IsAdmin();
        if($userId!==12 && $isAdmin===false){
            $arFields['RESULT_MESSAGE'] = "Изменение поля доступно только для Юриста!";
            return false;
        }
    }

    private function getListValue($elID){
        $answer = "";
        $res = \CUserFieldEnum::GetList([], ["ID" => $elID]);
        if(intval($res->SelectedRowsCount())>0) {
            $answer = $res->Fetch()['VALUE'];
        }
        return $answer;

    }

    private function checkRemarksBoss(&$arFields){
            $bossAgreement = self::getListValue($arFields['UF_CRM_1562062792']);

            if($bossAgreement==="Нет"){
                if(!isset($arFields['UF_CRM_1562062355']) || $arFields['UF_CRM_1562062355']===""){
                    $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($arFields["ID"])],false,false,  ["*", "UF_*"])->Fetch();
                    $oldRemarks = $deal['UF_CRM_1562062355'];
                    if(strlen($oldRemarks)===0){
                        $arFields['RESULT_MESSAGE'] = "Для отказа в согласовании документа заполните поле 'Замечания руководителя'! ";
                        return false;
                    }
                }
            }
            return true;
    }

    private function checkRemarksUrist(&$arFields){
        $lawyerAgreement = self::getListValue($arFields['UF_CRM_1562063096']);
        if($lawyerAgreement==="Нет"){
            if(!isset($arFields['UF_CRM_1562594222']) || $arFields['UF_CRM_1562594222']===""){
                $deal =  \CCrmDeal::GetListEx([],["ID"=>intval($arFields["ID"])],false,false,  ["*", "UF_*"])->Fetch();

                $oldRemarks = $deal['UF_CRM_1562594222'];
                if(strlen($oldRemarks)===0){
                    $arFields['RESULT_MESSAGE'] = "Для отказа в согласовании документа заполните поле 'Замечания юриста'! ";
                    return false;
                }
            }
         }
        return true;
    }


    private function  agreeByBoss($ID){
        $arErrorsTmp = array();
        $wfId = \CBPDocument::StartWorkflow(
            76,
            [ 'crm', 'CCrmDocumentDeal' , "DEAL_".$ID ],
            [],
            $arErrorsTmp
        );
    }

    private function  agreeByLawyer($ID){
        $arErrorsTmp = array();
        $wfId = \CBPDocument::StartWorkflow(
            77,
            [ 'crm', 'CCrmDocumentDeal' , "DEAL_".$ID ],
            [],
            $arErrorsTmp
        );
    }

    private function  returnedToRegister($ID){
        $arErrorsTmp = array();
        $wfId = \CBPDocument::StartWorkflow(
            87,
            [ 'crm', 'CCrmDocumentDeal' , "DEAL_".$ID ],
            [],
            $arErrorsTmp
        );
    }

}