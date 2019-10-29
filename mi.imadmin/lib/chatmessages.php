<?php


namespace Mi\ImAdmin;


use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ChatMessages
{

    private function writeToLog($data)
    {
        $log = "\n------------------------\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log10.txt', $log, FILE_APPEND);
        return true;
    }

    public function getMessages($chatId, $period, $files = false)
    {
        global $DB;

        $id = intval($id);

        $time = $this->getTime($period);

        $query = "SELECT M.*, M.DATE_CREATE, M.AUTHOR_ID, R.MESSAGE_TYPE            
            FROM b_im_message M			
			INNER JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID
			WHERE M.CHAT_ID = '" . $chatId . "' AND M.DATE_CREATE < '" . $time . "' 
			GROUP BY M.ID";

        $resultDb = $DB->Query($query, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

        $arMessage = [];
        while ($message = $resultDb->Fetch()) {
            $time = strtotime($message['DATE_CREATE']);
            $message['DATE_CREATE_PREP'] = date("d.m.Y H:i:s", $time);
            $message['LAST_SAVE_TIME'] = $time;
            $arMessage[] = $message;
        }
        return $arMessage;
    }


    public function getTime($savePeriod = "last_week")
    {

        switch ($savePeriod) {
            case "last_week":
                $time = strtotime("-1 week");
                break;

            case "last_2_week":
                $time = strtotime("-2 week");
                break;
            case "last_day":
                $time = strtotime("-1 day");
                break;

            case "date":
                $time = strtotime($savePeriod);
                break;

            case "all":
                $time = strtotime(date("Y-m-d"));
                break;

        }

        $dateTime = date("Y-m-d", $time) . " 24:59:59";
        return $dateTime;
    }

    public function deleteMesasage($id, $authorId, $dataMessage)
    {
        if (\CIMMessage::Delete($id, $authorId)) {

            $arFields = [
                "MESSAGE" => "Это сообщение было очищено автоматически",
                "MESSAGE_OUT" => "Сообщение от " . $dataMessage . " было очищено автоматически"
            ];

            \Bitrix\IM\Model\MessageTable::update($id, $arFields);
        }
    }

    public static function deleteAllChatMessage($chatId, $period = "last_week")
    {
        $ChatMessages = new \Mi\ImAdmin\ChatMessages;
        $arMeesages = $ChatMessages->getMessages($chatId, $period);
        foreach ($arMeesages as $message) {
            //echo "<pre>"; print_r($message); echo "</pre>";
            if ($message['AUTHOR_ID'] > 0) {
                //echo "<pre>"; print_r($message['ID']); echo "</pre>";
                $ChatMessages->deleteMesasage($message['ID'], $message['AUTHOR_ID'], $message['DATE_CREATE_PREP']);
            }
        }
    }

    public static function deleteAllGroupChatMessageByUser($userId, $period = "last_week")
    {
        $arParams = ['USER_ID' => $userId, 'GET_LIST' => 'Y'];
        $arResult = \CIMChat::GetChatData($arParams);
        $arChatId = [];

        if (isset($arResult['chat']) && is_array($arResult['chat'])) {

            foreach ($arResult['chat'] as $chat) {
                if($chat['message_type'] !== 'P'){ // KE УБРАЛ ПРОВЕРКУ НА ПРИВАТНЫЙ ЧАТ, УДАЛЯТЬ ВСЕ.
                    $arChatId[] = $chat['id'];
                }
            }
        }

        $ChatMessages = new \Mi\ImAdmin\ChatMessages;
        foreach ($arChatId as $chatId) {
            $arMeesages = $ChatMessages->getMessages($chatId, $period);
            foreach ($arMeesages as $message) {
                if ($message['AUTHOR_ID'] > 0) {
                    //echo "<pre>"; print_r([$message['ID']]); echo "</pre>";
                    $ChatMessages->deleteMesasage($message['ID'], $message['AUTHOR_ID'], $message['DATE_CREATE_PREP']);
                }
            }
        }

    }

    public static function deleteGroupChat($last_user = "")
    {
        $US = new \CUser;
        if (!is_object($GLOBALS['USER'])) {
            $GLOBALS['USER'] = $US;
            global $USER;
            $USER = $US;
        }


        $by = "id";
        $order = "desc";
        $arFilter = [];

        $db = $US::GetList($by, $order, [], ['SELECT' => ["UF_CHAT_DEL", "UF_CHAT_GROUP_DATE"]]);

        while ($arUser = $db->Fetch()) {
            $startTime = time();
            if ($last_user == 1) {
                $last_user = $arUser['ID'];
            }

            if ($last_user == "" || $last_user > $arUser['ID']) {
                if ($arUser["UF_CHAT_DEL"] == 1068) {
                    $period = 'last_week';
                } else if ($arUser["UF_CHAT_DEL"] == 1070) {
                    $period = 'last_2_week';
                } else if ($arUser["UF_CHAT_DEL"] == 1069) {
                    $period = 'all';
                } else {
                    $period = "";
                }
                $last_user = $arUser['ID'];

                $dateChat = explode(" ", $arUser["UF_CHAT_GROUP_DATE"])[0];

                if (!empty($period) && $dateChat != date('d.m.Y')) {
                    \Mi\ImAdmin\ChatMessages::deleteAllGroupChatMessageByUser($arUser['ID'], $period);
                    $endTime = time() + 1;
                    $fields = ['UF_CHAT_GROUP_DATE' => date('d.m.Y H:i:s') . " (" . ($endTime - $startTime) . ")"];
                    $US->Update($arUser['ID'], $fields);

                    break;
                }
            }
        }
        return "\Mi\ImAdmin\ChatMessages::deleteGroupChat(" . $last_user . ");";
    }
}