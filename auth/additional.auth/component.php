<?
session_start();
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CModule::IncludeModule("iblock");



$arParams['USER_ID'] = $_SESSION['TWO_STEP_ID'];

if($_GET['send_string']==='1' && $_POST['TYPE']!=="SEND_CONTROL_STRING"){
    $arResult['control_string']=true;


    $rs = CUser::GetList(($by="ID"), ($order="desc"), Array('ID'=>$arParams['USER_ID']), ['CHECK_PERMISSOINS' => 'N', 'SELECT' => ['*', "UF_*"]]);
    $arUserField = $rs->Fetch();
    $email = $arUserField['EMAIL'];
    if(strlen($email)>0 && strpos($email, "noemail@beloil.by")===false){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 5; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $PROP = array();
        $PROP[220] = $randomString;  // контрольная строка
        $PROP[221] = $email;        // емэил
        $PROP[222] = "USER_".$arUserField['ID'];
        $arLoadProductArray = Array(
            //"MODIFIED_BY"    => 1, // элемент изменен текущим пользователем
            "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
            "IBLOCK_ID"      => 49,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => "Элемент",
            "ACTIVE"         => "Y",            // активен
            //"PREVIEW_TEXT"   => "текст для списка элементов",
            //DETAIL_TEXT"    => "текст для детального просмотра"
        );
        $el = new CIBlockElement;
        $idOfEl = $el->Add($arLoadProductArray);
        \Bitrix\Main\Loader::includeModule('bizproc');
        $arErrorsTmp = array();

        $arWorkflowParameters[\CBPDocument::PARAM_TAGRET_USER] = "user_1";
        $arWorkflowParameters[\CBPDocument::PARAM_DOCUMENT_EVENT_TYPE] = \CBPDocumentEventType::Manual;
        $wfId = \CBPDocument::StartWorkflow(
            56,
            [ 'lists', 'Bitrix\Lists\BizprocDocumentLists' , $idOfEl ],
            [],
            $arErrorsTmp
        );



        $user = new CUser;
        $user->Update($arParams['USER_ID'], $fields=['UF_CONTROL_STRING' => $randomString]);
    }
    else{
        $arResult['ERRORS'][] = "К Вашей учетной записи привязан некорректный Email, обратитесь к администратору!";
    }


}

if($_POST["TYPE"]==='SEND_NAME'){
    //var_dump($_POST);
    $user = new CUser ;
    $rs = CUser::GetList(($by="ID"), ($order="desc"), Array('ID'=>$arParams['USER_ID']), ['CHECK_PERMISSOINS' => 'N', 'SELECT' => ['*', "UF_*"]]);
    if(intval($rs->SelectedRowsCount())>0){
    $arUserField = $rs->Fetch();

        if( strtolower($arUserField['NAME'])===strtolower($_POST['USER_NAME']) &&
            strtolower($arUserField['SECOND_NAME'])===strtolower($_POST['USER_SECOND_NAME']) &&
            strtolower($arUserField['LAST_NAME'])===strtolower($_POST['USER_SURNAME'])){
            //если введен необязательный номер соц страхования, то проверяем его
            if(isset($_POST['USER_NUMBER'])&& isset($arUserField['UF_INSH_NUMBER']) && strlen($arUserField['UF_INSH_NUMBER'])>0 && strlen($_POST['USER_NUMBER'])>0){
                if($_POST['USER_NUMBER']===$arUserField['UF_INSH_NUMBER']){
                    $user->Authorize($arParams['USER_ID'], false);
                    LocalRedirect("/stream/");
                }
                else{
                    $arResult['ERRORS'][] = "Неверно введены личные данные!";
                }
            }
            else{
                $user->Authorize($arParams['USER_ID'], false);
                LocalRedirect("/stream/");
            }
        }
        else{
            $arResult['ERRORS'][] = "Неверно введены личные данные!";
        }
    }
     else{
        $arResult['ERRORS'][] = "Некорректная попытка авторизации";
    }
}

if($_POST['TYPE']==="SEND_CONTROL_STRING"){

    $user = new CUser;
    $arUser = $user->GetByID($arParams['USER_ID'])->fetch();
    $controlString = $arUser['UF_CONTROL_STRING'];
    if($controlString === $_POST['CONTROL_STRING']){
        $user->Authorize($arParams['USER_ID'], false);
        LocalRedirect("/stream/");
    }
    else{
        $arResult['ERRORS'][] = "Введена неверная котрольная строка!";
        $arResult['control_string']=true;
    }

}
////////////////////////


$this->IncludeComponentTemplate();