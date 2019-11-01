<?php

namespace Core;

class DB {
	
	private $linkFile, $nameFile;
	
	public function __construct(){
		$this->nameFile = "db";
		$this->linkFile = CLASSES_CORE_DIR.$this->nameFile;		
	}
	
	public function read(){
		if(!is_file($this->linkFile)){
			return "";
		}
		$content = file_get_contents($this->linkFile);
		if($content === false){
			return "";
		}
		return $content;
	}
	
	public function write($content){
		$res = file_put_contents($this->linkFile, $content, LOCK_EX);
		if($res !== false) {
			return true;
		}
		return false;
	}
	
	public function delete(){
		return unlink($this->linkFile);		
	}
}
