<?php
namespace Core;

class BxQuery {
	
	public function RestApiQuery($url, $arParams) {
		
		$curlOptions[CURLOPT_POSTFIELDS] = http_build_query($arParams);
		$curlOptions[CURLOPT_HEADER] = false;
		$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
		$curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
		$curlOptions[CURLOPT_FOLLOWLOCATION] = true;
		$curlOptions[CURLOPT_RETURNTRANSFER] = true;	

		$curl = curl_init($url);
		curl_setopt_array($curl, $curlOptions);
		$result = curl_exec($curl);		
		return $result;			
	}	
}