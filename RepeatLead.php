<?php


namespace KE;
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
\CModule::IncludeModule('crm');
\CModule::IncludeModule('im');
\CModule::IncludeModule("intranet");


class RepeatLead
{
    private function writeToLog($data) {
        $log = "\n------------------------\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents($_SERVER["DOCUMENT_ROOT"]."/log228.txt", $log, FILE_APPEND);
    }

    public function  onBeforeCrmLeadAdd(&$arFields){
        if($arFields['ORIGINATOR_ID'] === "email-tracker"){
            $arFields['SOURCE_ID'] = "3";
        }
        if($arFields['IS_RETURN_CUSTOMER']==="Y"){
            $arFields['IS_RETURN_CUSTOMER'] = "N";
        }
    }

    public function OnActivityAdd(&$arFields){
        $CCrmActivity = new \CCrmActivity;
        $activity = $CCrmActivity->GetByID($arFields);
        if($activity['PROVIDER_TYPE_ID']==="EMAIL" || $activity["PROVIDER_ID"] === "CRM_EMAIL"){
            $allActivities = $CCrmActivity::GetList([], ["OWNER_ID"=>$activity['OWNER_ID'], 'PROVIDER_TYPE_ID'=>"EMAIL"], false, false, ["*", "UF_*"]);
            $emailCount = 0;
            while ($act = $allActivities ->Fetch()){
                $emailCount++;
            }
            // если сообщение прикрепилось в не новый лид - создать новый
            if( ($activity['OWNER_TYPE_ID'] == "1" && $emailCount>1) || $activity['OWNER_TYPE_ID'] !== "1"){
                $companyID = "";
                $contactID = "";
                $assignedByID = "";
                $CCRMLead = new \CCrmLead;

                if($activity['OWNER_TYPE_ID'] === "1"){ //lead
                    $lead = $CCRMLead->GetByID($activity['OWNER_ID']);
                    self::writeToLog($lead);
                    $companyID = $lead['COMPANY_ID'];
                    $contactID = $lead['CONTACT_ID'];
                    $assignedByID =$lead['ASSIGNED_BY_ID'];
                }
                elseif ($activity['OWNER_TYPE_ID'] === "2"){ //deal
                    $deal = \CCrmDeal::GetByID($activity['OWNER_ID']);
                    self::writeToLog($deal);
                    $companyID = $deal['COMPANY_ID'];
                    $contactID = $deal['CONTACT_ID'];
                    $assignedByID =$deal['ASSIGNED_BY_ID'];
                }
                elseif ($activity['OWNER_TYPE_ID'] === "3"){ //contact
                    $contact = \CCrmContact::GetByID($activity['OWNER_ID']);
                    self::writeToLog($contact);
                    $companyID = $contact['COMPANY_ID'];
                    $contactID = $contact['CONTACT_ID'];
                    $assignedByID =$contact['ASSIGNED_BY_ID'];
                }
                elseif ($activity['OWNER_TYPE_ID'] === "4"){ //company
                    $company = \CCrmCompany::GetByID($activity['OWNER_ID']);
                    self::writeToLog($company);
                    $companyID = $company['COMPANY_ID'];
                    $contactID = $company['CONTACT_ID'];
                    $assignedByID =$company['ASSIGNED_BY_ID'];
                }

                if($assignedByID === "" || $assignedByID === "0" || $assignedByID === "1"){
                    $assignedByID = "14";
                }

                $fields = [
                    "TITLE" => $activity['SUBJECT'],
                    "COMMENTS" => $activity['SUBJECT'],
                    "ASSIGNED_BY_ID" => $assignedByID,
                    'SOURCE_ID' => "3",
                    'NAME' => $activity["SETTINGS"]["EMAIL_META"]["from"],
                    'FM' => ["EMAIL" => ["n0"=>["VALUE"=>$activity["SETTINGS"]["EMAIL_META"]["from"], "VALUE_TYPE" => "WORK"]]],
                    'CONTACT_ID' =>$contactID,
                    'COMPANY_ID' =>$companyID

                ];
                self::writeToLog($fields);
                $leadId = $CCRMLead->Add($fields);
                self::writeToLog($leadId);
                $newActivityFields = $activity;
                $newActivityFields["OWNER_ID"]= $leadId;
                $newActivityFields["OWNER_TYPE_ID"]= "1";
                $newActivityFields["ID"]= "";
                $newActivityId = $CCrmActivity->Add($newActivityFields);
                //self::writeToLog($newActivityId);
            }
        }
     }
}