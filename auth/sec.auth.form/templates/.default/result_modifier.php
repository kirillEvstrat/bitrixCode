<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
\CModule::IncludeModule("iblock");

$arResult["UF_DEPARTMENT"] = "";
$arResult["COMPANY"] = "";

//Получаем компанию
function GetCompany($ID, $company){
    $dbRs = \CIBlockSection::GetList(
        array('DEPTH_LEVEL'=>'ASC'),
        array("CHECK_PERMISSIONS" => "N", "IBLOCK_ID" => "5", "ID" => $ID),// 5 - это Инфоблок "Оргструктура"
        false,
        array("ID", "IBLOCK_SECTION_ID", "NAME")
    );
    while($arRs = $dbRs->Fetch())
    {
        //echo '<pre>';print_r($arRs);echo '</pre>';
        $company = $arRs;
        //DEPTH_LEVEL = 1 -- Это Беларусьнефть
        //DEPTH_LEVEL = 2 -- Это преприятия, которые входят в Беларусьнефть
        //если структура поменяется, надо будет немного переделать.
        //возможно будет DEPTH_LEVEL = 3 и DEPTH_LEVEL = 4, тогда придется добавить дополнительные проверки
        if($arRs["DEPTH_LEVEL"] != 2){
            $company = GetCompany($arRs['IBLOCK_SECTION_ID'], $company);
        }
    }
    return $company;
}

$arUser = \CUser::GetList(($by="id"), ($order="asc"), array("ID" => $arResult["ID"]), array("SELECT" => array("UF_DEPARTMENT")))->Fetch();

//echo '<pre>';print_r($arUser);echo '</pre>';
if(!empty($arUser["UF_DEPARTMENT"])){
    foreach ($arUser["UF_DEPARTMENT"] as $value){
        $rsDepartment = \CIBlockSection::GetList(
            array('DEPTH_LEVEL'=>'ASC'),
            array("CHECK_PERMISSIONS" => "N", "IBLOCK_ID" => "5", "ID" => $value),
            false,
            array("ID", "IBLOCK_SECTION_ID", "NAME")
        );
        while($arDepartment = $rsDepartment->Fetch())
        {
            $arResult["UF_DEPARTMENT"] = $arDepartment["NAME"];
        }

        $company = array();
        $companyID = array();
        $company = GetCompany($value, $companyID);
        count($arUser["UF_DEPARTMENT"]) == 1 ? $arResult["COMPANY"] = htmlspecialchars($company["NAME"]) : $arResult["COMPANY"] .= htmlspecialchars($company["NAME"]).' | ';

    }
}
//echo '<pre>';print_r($arResult["COMPANY"]);echo '</pre>';