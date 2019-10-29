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

///confirmation of personal info
$arResult['WORK_DEPARTMENT']= $_SESSION['WORK_DEPARTMENT'];
$arResult['WORK_COMPANY']= $_SESSION['WORK_COMPANY'];
$arResult['NAME']= $_SESSION['NAME'];
$arResult['LAST_NAME']= $_SESSION['LAST_NAME'];
$arResult['SECOND_NAME']= $_SESSION['SECOND_NAME'];
$arResult['ID']= $_SESSION['ID'];
$arResult['LOGIN']= $_SESSION['LOGIN'];
$arResult['step']=1;
///////////////

//if user want contact to admin
if($_GET['contact_to_admin']==="1"){
    LocalRedirect("./stream/contact_to_admin.php");
}

///if user confirmed personal info
if($_POST['step']==="1"){

    $userId = (int)$_POST['USER_ID'];
    $login = $_POST['USER_LOGIN'];

    $rs = CUser::GetList(($by="ID"), ($order="desc"), Array('ID'=>$userId), ['CHECK_PERMISSOINS' => 'N', 'SELECT' => ['*', "UF_*"]]);
    if(intval($rs->SelectedRowsCount())>0) {
        $arUserField = $rs->Fetch();
        $newId = isset($_SESSION["NEW_ID"])? (int)$_SESSION["NEW_ID"]: false;

        if($newId!==$userId && $newId!==false){
            $rs = CUser::GetList(($by = "ID"), ($order = "desc"), Array('ID' => $newId));
            $newUser = $rs->Fetch();
            if($newUser['EXTERNAL_AUTH_ID']!==NULL && $newUser['EXTERNAL_AUTH_ID']!==""){ //user from ad
                //перенести данные со старого аккаунта(ЕСЛИ ТАКОВОЙ ИМЕЕТСЯ) в новый
                $user = new CUser ;
                if($newId!==(int)$arUserField["ID"] ) {
                    $params = ["NAME" => $arUserField['NAME'],
                        "LAST_NAME" => $arUserField['LAST_NAME'],
                        "LOGIN" => $arUserField['LOGIN'],
                        "PERSONAL_PROFESSION" => $arUserField['PERSONAL_PROFESSION'],
                        "PERSONAL_WWW" => $arUserField['PERSONAL_WWW'],
                        "PERSONAL_ICQ" => $arUserField['PERSONAL_ICQ'],
                        "PERSONAL_GENDER" => $arUserField['PERSONAL_GENDER'],
                        "PERSONAL_BIRTHDATE" => $arUserField['PERSONAL_BIRTHDATE'],
                        "PERSONAL_PHOTO" => $arUserField['PERSONAL_PHOTO'],
                        "PERSONAL_PHONE" => $arUserField['PERSONAL_PHONE'],
                        "PERSONAL_FAX" => $arUserField['PERSONAL_FAX'],
                        "PERSONAL_MOBILE" => $arUserField['PERSONAL_MOBILE'],
                        "PERSONAL_PAGER" => $arUserField['PERSONAL_PAGER'],
                        "PERSONAL_STREET" => $arUserField['PERSONAL_STREET'],
                        "PERSONAL_MAILBOX" => $arUserField['PERSONAL_MAILBOX'],
                        "PERSONAL_CITY" => $arUserField['PERSONAL_CITY'],
                        "PERSONAL_STATE" => $arUserField['PERSONAL_STATE'],
                        "PERSONAL_ZIP" => $arUserField['PERSONAL_ZIP'],
                        "PERSONAL_COUNTRY" => $arUserField['PERSONAL_COUNTRY'],
                        "PERSONAL_NOTES" => $arUserField['PERSONAL_NOTES'],
                        "WORK_COMPANY" => $arUserField['WORK_COMPANY'],
                        "WORK_DEPARTMENT" => $arUserField['WORK_DEPARTMENT'],
                        "WORK_POSITION" => $arUserField['WORK_POSITION'],
                        "WORK_WWW" => $arUserField['WORK_WWW'],
                        "WORK_PHONE" => $arUserField['WORK_PHONE'],
                        "WORK_FAX" => $arUserField['WORK_FAX'],
                        "WORK_PAGER" => $arUserField['WORK_PAGER'],
                        "WORK_STREET" => $arUserField['WORK_STREET'],
                        "WORK_MAILBOX" => $arUserField['WORK_MAILBOX'],
                        "WORK_CITY" => $arUserField['WORK_CITY'],
                        "WORK_STATE" => $arUserField['WORK_STATE'],
                        "WORK_ZIP" => $arUserField['WORK_ZIP'],
                        "WORK_COUNTRY" => $arUserField['WORK_COUNTRY'],
                        "WORK_PROFILE" => $arUserField['WORK_PROFILE'],
                        "WORK_LOGO" => $arUserField['WORK_LOGO'],
                        "WORK_NOTES" => $arUserField['WORK_NOTES'],
                        "ADMIN_NOTES" => $arUserField['ADMIN_NOTES'],
                        "STORED_HASH" => $arUserField['STORED_HASH'],
                        "XML_ID" => $arUserField['XML_ID'],
                        "PERSONAL_BIRTHDAY" => $arUserField['PERSONAL_BIRTHDAY'],
                        "SECOND_NAME" => $arUserField['SECOND_NAME'],
                        "UF_PHONE_INNER" => $arUserField['UF_PHONE_INNER'],
                        "UF_IM_SEARCH" => $arUserField['UF_IM_SEARCH'],
                        "UF_CONNECTOR_MD5" => $arUserField[''],
                        "UF_1C" => $arUserField['UF_1C'],
                        "UF_INN" => $arUserField['UF_INN'],
                        "UF_DISTRICT" => $arUserField['UF_DISTRICT'],
                        "UF_SKYPE" => $arUserField['UF_SKYPE'],
                        "UF_TWITTER" => $arUserField['UF_TWITTER'],
                        "UF_FACEBOOK" => $arUserField['UF_FACEBOOK'],
                        "UF_LINKEDIN" => $arUserField['UF_LINKEDIN'],
                        "UF_XING" => $arUserField['UF_XING'],
                        "UF_WEB_SITES" => $arUserField['UF_WEB_SITES'],
                        "UF_SKILLS" => $arUserField['UF_SKILLS'],
                        "UF_INTERESTS" => $arUserField['UF_INTERESTS'],
                        "UF_WORK_BINDING" => $arUserField['UF_WORK_BINDING'],
                        "UF_BXDAVEX_CALSYNC" => $arUserField['UF_BXDAVEX_CALSYNC'],
                        "UF_WARNINGS" => $arUserField['UF_WARNINGS'],
                        "UF_BANNED" => $arUserField['UF_BANNED'],
                        "UF_INSH_NUMBER" => $arUserField['UF_INSH_NUMBER'],
                        "UF_TAB_NUMBER" => $arUserField['UF_TAB_NUMBER'],
                        "UF_PERNR" => $arUserField['UF_PERNR'],
                        "UF_BD" => $arUserField['UF_BD'],
                        "UF_STARTWORK" => $arUserField['UF_STARTWORK'],
                        "UF_WORKHISTORY" => $arUserField['UF_WORKHISTORY'],
                        "UF_VIS_NUM" => $arUserField['UF_VIS_NUM'],
                        "UF_EDUCATION" => $arUserField['UF_EDUCATION'],
                        "UF_EDUCATION_LEVEL" => $arUserField['UF_EDUCATION_LEVEL'],
                        "UF_NOTIFY_STATUS" => $arUserField['UF_NOTIFY_STATUS'],
                        "UF_HOBBY" => $arUserField['UF_HOBBY'],
                        "UF_ACHIEVEMENTS" => $arUserField['UF_ACHIEVEMENTS'],
                        "UF_BDAY_EVENT_ID" => $arUserField['UF_BDAY_EVENT_ID'],
                        "UF_SHOW_BDAY" => $arUserField['UF_SHOW_BDAY'],
                        "UF_TIMEMAN" => $arUserField['UF_TIMEMAN'],
                        "UF_TM_MAX_START" => $arUserField['UF_TM_MAX_START'],
                        "UF_TM_MIN_FINISH" => $arUserField['UF_TM_MIN_FINISH'],
                        "UF_TM_MIN_DURATION" => $arUserField['UF_TM_MIN_DURATION'],
                        "UF_TM_REPORT_REQ" => $arUserField['UF_TM_REPORT_REQ'],
                        "UF_TM_REPORT_TPL" => $arUserField['UF_TM_REPORT_TPL'],
                        "UF_TM_FREE" => $arUserField['UF_TM_FREE'],
                        "UF_TM_TIME" => $arUserField['UF_TM_TIME'],
                        "UF_TM_DAY" => $arUserField['UF_TM_DAY'],
                        "UF_TM_REPORT_DATE" => $arUserField['UF_TM_REPORT_DATE'],
                        "UF_REPORT_PERIOD" => $arUserField['UF_REPORT_PERIOD'],
                        "UF_DELAY_TIME" => $arUserField['UF_DELAY_TIME'],
                        "UF_LAST_REPORT_DATE" => $arUserField['UF_LAST_REPORT_DATE'],
                        "UF_SETTING_DATE" => $arUserField['UF_SETTING_DATE'],
                        "UF_TM_ALLOWED_DELTA" => $arUserField['UF_TM_ALLOWED_DELTA']
                    ];
                    $user->Update($newId, $params, false);
                    $user->Update($arUserField['ID'], ['UF_PERNR'=> "_".$arUserField['UF_PERNR']], false);
                }
                $user->Authorize($_POST['USER_ID'], false);
                $arGroups = \CUser::GetUserGroup(intval($newId));
                $arGroups[] = intval(12);
                \CUser::SetUserGroup(intval($newId), $arGroups);
                unset($_SESSION['LOGIN']);
                unset($_SESSION['WORK_COMPANY']);
                unset($_SESSION['WORK_DEPARTMENT']);
                unset($_SESSION['NAME']);
                unset($_SESSION['LAST_NAME']);
                unset($_SESSION['SECOND_NAME']);
                unset($_SESSION['NEW_ID']);
                unset($_SESSION['ID']);
                LocalRedirect("/stream/");

            }
        }

        else{
            $arResult['step']=2;
        }
        }
    }

// if user already entered password /////////////
    if($_POST['step']==="2"){

        $pass1=$_POST["password"];
        $pass2=$_POST["password-confirm"];
        if($pass1 !== $pass2){
            //confirm error
            $arResult["ERRORS"][] = "Пароли должны совпадать!";
            $arResult["repeat"] = true;
        }

        //update  and auth users who entered correct password
        else{
            $arPolicy = \CUser::GetGroupPolicy($_POST["USER_ID"]);
            $errors = \CUser::CheckPasswordAgainstPolicy($pass1, $arPolicy);
            $arResult["ERRORS"] = $errors;
            $arResult["repeat"] = true;

            if(!$errors){
                unset($_SESSION['LOGIN']);
                unset($_SESSION['WORK_COMPANY']);
                unset($_SESSION['WORK_DEPARTMENT']);
                unset($_SESSION['NAME']);
                unset($_SESSION['LAST_NAME']);
                unset($_SESSION['SECOND_NAME']);
                unset($_SESSION['ID']);
                unset($_SESSION['NEW_ID']);

                $user = new  CUser;
                $fields = ['PASSWORD'=>$pass1, 'CONFIRM_PASSWORD'=>$pass2];
                $user -> update($_POST['USER_ID'], $fields, false);
                //$arAuthResult = $user->Login($_POST["USER_LOGIN"], $pass1);
                //$user->Authorize($_POST['USER_ID'], false);
                $arGroups = \CUser::GetUserGroup(intval($_POST["USER_ID"]));
                $arGroups[] = intval(12); //группа работники
                \CUser::SetUserGroup(intval($_POST['USER_ID']), $arGroups);

                LocalRedirect("./");
            }

        }
    }
////////////////////////
$this->IncludeComponentTemplate();