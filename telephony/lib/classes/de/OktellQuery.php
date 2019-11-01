<?php

namespace DE;

class OktellQuery {
	private $url;
	
	public function __construct($url) {
		$this->url = $url;
	}
	
	public function query($arParams) {
		$url = $this->url;
		$curlOptions[CURLOPT_HEADER] = false;
		$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
		$curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
		$curlOptions[CURLOPT_FOLLOWLOCATION] = true;
		$curlOptions[CURLOPT_RETURNTRANSFER] = true;
		
		if (!empty($arParams)) {
			$url .= strpos($url, "?") > 0 ? "&" : "?";
			$url .= http_build_query($arParams);
		}
		
		$curl = curl_init($url);
		curl_setopt_array($curl, $curlOptions);
		$result = curl_exec($curl);		
		return $result;			
	}	
}
