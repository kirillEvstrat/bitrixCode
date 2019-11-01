<?php
namespace KE;

\CModule::IncludeModule("iblock");


class ExportIBlock
{


    private function writeToLog($data) {
        $log = "\n------------------------\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/test.log', $log, FILE_APPEND);
        return true;
    }



## Создает CSV файл из переданных в массиве данных.
## @param array  $create_data  Массив данных из которых нужно созать CSV файл.
## @param string $file         Путь до файла 'path/to/test.csv'. Если не указать, то просто вернет результат.
## @return string/false        CSV строку или false, если не удалось создать файл.
## ver 2
    private function kama_create_csv_file( $create_data, $file = null, $col_delimiter = ';', $row_delimiter = "\r\n" ){

        if( ! is_array($create_data) )
            return false;

        if( $file && ! is_dir( dirname($file) ) )
            return false;

        // строка, которая будет записана в csv файл
        $CSV_str = '';

        // перебираем все данные
        foreach( $create_data as $row ){
            $cols = array();

            foreach( $row as $col_val ){
                // строки должны быть в кавычках ""
                // кавычки " внутри строк нужно предварить такой же кавычкой "
                if( $col_val && preg_match('/[",;\r\n]/', $col_val) ){
                    // поправим перенос строки
                    if( $row_delimiter === "\r\n" ){
                        $col_val = str_replace( "\r\n", '\n', $col_val );
                        $col_val = str_replace( "\r", '', $col_val );
                    }
                    elseif( $row_delimiter === "\n" ){
                        $col_val = str_replace( "\n", '\r', $col_val );
                        $col_val = str_replace( "\r\r", '\r', $col_val );
                    }

                    $col_val = str_replace( '"', '""', $col_val ); // предваряем "
                    $col_val = '"'. $col_val .'"'; // обрамляем в "
                }

                $cols[] = $col_val; // добавляем колонку в данные
            }

            $CSV_str .= implode( $col_delimiter, $cols ) . $row_delimiter; // добавляем строку в данные
        }

        $CSV_str = rtrim( $CSV_str, $row_delimiter );

        // задаем кодировку windows-1251 для строки
        if( $file ){
            $CSV_str = iconv( "UTF-8", "cp1251",  $CSV_str );
            // создаем csv файл и записываем в него строку
            $done = file_put_contents( $file, $CSV_str );
            return $done ? $CSV_str : false;
        }

        return $CSV_str;

    }

    private function prepareDataForCvs($IBlockID){
        $arRes = \CIBlockElement::GetList(["xml_id"=>"asc"],['IBLOCK_ID'=>$IBlockID], false, false, ['*', "UF_*"]);
        $i = 0;
        $dataAll = [];
        $headersAr = [];
        while($el = $arRes->Fetch()){
            $temp = [];
            foreach ($el as $key => $value){
                if($i===0){
                    $headersAr[]=$key;
                }
                else {
                    $temp[] = $value;
                }
            }
            $i++;
            $dataAll[]= $temp;
        }

        $dataAll = array_merge(array($headersAr), $dataAll);
        return $dataAll;
    }

    private function createXML($IBlockID, $fileDir, $fileName){
        $obExport = new \CIBlockCMLExport;

        $NS = [
            "IBLOCK_ID" => $IBlockID,
            "STEP" =>1,
            "SECTIONS_FILTER"=> "active",
            "ELEMENTS_FILTER"=>"active",
        ];
        $fp = fopen($fileDir.$fileName.".xml", "ab");
        if($obExport->Init($fp, $NS["IBLOCK_ID"], $NS["next_step"], true, $fileDir, $fileName))
        {
            $obExport->StartExport();
            $obExport->StartExportMetadata();
            $obExport->ExportProperties($_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"]);
            $result = $obExport->ExportSections(
                $_SESSION["BX_CML2_EXPORT"]["SECTION_MAP"],
                $start_time,
                $INTERVAL,
                $NS["SECTIONS_FILTER"],
                $_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"]
            );
            $obExport->EndExportMetadata();
            $obExport->StartExportCatalog();
            $result = $obExport->ExportElements(
                $_SESSION["BX_CML2_EXPORT"]["PROPERTY_MAP"],
                $_SESSION["BX_CML2_EXPORT"]["SECTION_MAP"],
                $start_time,
                $INTERVAL,
                0,
                $NS["ELEMENTS_FILTER"]
            );
            $obExport->EndExportCatalog();
            $obExport->ExportProductSets();
            $obExport->EndExport();
            return true;
        }
        else{
            return false;
        }
    }

    private function saveFilesOnDisk($fileIDs){
        try {
            $dbDisk = \Bitrix\Disk\Storage::getList(array("filter"=>array("ENTITY_ID" => "shared_files_s1", "ENTITY_TYPE" => 'Bitrix\Disk\ProxyType\Common')));
            if ($arDisk = $dbDisk->Fetch()) {

                $storage = \Bitrix\Disk\Storage::loadById($arDisk["ID"]);

            }
        }
        catch (\Exception $e) {
            var_export($e);
        }
        $folder = \Bitrix\Disk\SpecificFolder::getFolder($storage, "RESOURCE");
        foreach($fileIDs as $k => $fileId) {

            $fileArray = \CFile::getById($fileId)->fetch();
            try {
                $file = $folder->addFile(array(
                    'NAME' => $fileArray['FILE_NAME'],
                    'FILE_ID' => $fileId,
                    'CONTENT_PROVIDER' => null,
                    'SIZE' => $fileArray['FILE_SIZE'],
                    'CREATED_BY' => 1,
                    'UPDATE_TIME' => null,
                ), array(), true);

            } catch (\Exception $e) {
                var_export($e);
            }
        }
    }

    public function export(){
        $fileIDs = [];

        $dataAll  = self::prepareDataForCvs(69);

        $fileName = (new \DateTime())->format("d_m_Y__h_i_s");
        $pathtofileCSV = "/upload/disk/{$fileName}.csv";
        $pathtofileXML = "/upload/disk/{$fileName}.xml";
        $fileDir = $_SERVER['DOCUMENT_ROOT']."/upload/disk/";
        $resCSV = self::kama_create_csv_file($dataAll, $fileDir.$fileName.".csv");
        $resXML = self::createXML(69, $fileDir, $fileName);

        $arFile = \CFile::MakeFileArray($pathtofileCSV);
        $arFile['MODULE_ID'] = 'disk';
        $fid = \CFile::SaveFile($arFile, 'disk');
        $fileIDs[] = $fid;

        $arFile = \CFile::MakeFileArray($pathtofileXML);
        $arFile['MODULE_ID'] = 'disk';
        $fid = \CFile::SaveFile($arFile, 'disk');
        $fileIDs[] = $fid;

        self::saveFilesOnDisk($fileIDs);

        return "\KE\ExportIBlock::export();";
    }


}




