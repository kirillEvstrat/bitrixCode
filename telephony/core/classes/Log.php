<?php
namespace Core;

class Log {
	
	private $yearDir;
	private $monthDir;
	private $logsPathDir;
	
	
	/** 
	 * конструктор класса логов, принимает путь от корня хоста к папке размещения логов,
	 * создает папки если нет ,для хранения логов 
	 * 
	 * @param $logsPathDir string
	 * 
	 * @return void
	 */
	
	public function __construct($logsPathDir = "log") {
		$this->yearDir = date('Y');
		$this->monthDir = date('m.Y');
		$this->logsPathDir = SITE_PATH.$logsPathDir;
		
		if(!$this->isDirExist($this->logsPathDir)) {			
			if(!$this->addDir($this->logsPathDir)) {
				// exeption error
			}
		} else {
			// exeption error
		}
		
		
	}
	
	
	/** 
	 * записывает POST переменные в файл логов, если файла нет, создает его
	 * 	
	 * 
	 * @return bool
	 */
	
	public function addPostDataLog(): bool {
				
		$pastData = $_POST;
		
		$string = json_encode($pastData);
				
		if($this->isDirExist($this->logsPathDir)) {			
			$link = $this->addLogsDir($this->logsPathDir);
			
			$prefix_log = date("d.m.Y");
			
			$linkFile = $link."/".$prefix_log . "_post_data_log.txt";			
			$dateTime = date("d.m.Y - G:i:s");
			
			$addDataFile = $dateTime." ".$string;		
			
			return $this->writeFile($linkFile, $addDataFile);
		}
		return false;
	}
	
	
	/** 
	 * записывает GET переменные в файл логов, если файла нет, создает его
	 * 
	 *  
	 * @return bool
	 */
	
	public function addGetDataLog(): bool {
		
		$pastData = $_GET;
		if(isset($_GET['key'])) {
			unset($_GET['key']);
		}
		$pastData = $_GET;
		$string = json_encode($pastData);
				
		if($this->isDirExist($this->logsPathDir)) {			
			$link = $this->addLogsDir($this->logsPathDir);
			
			$prefix_log = date("d.m.Y");
			
			$linkFile = $link."/".$prefix_log . "_get_data_log.txt";			
			$dateTime = date("d.m.Y - G:i:s");
			
			$addDataFile = $dateTime." ".$string;		
			
			return $this->writeFile($linkFile, $addDataFile);
		}
		return false;		
	}
	
	/** 
	 * записывает строку в файл логов, если файла нет, создает его
	 * 
	 * @param $string string
	 * 
	 * @return bool
	 */
	
	public function addAccessLog($string): bool {
		
		if($this->isDirExist($this->logsPathDir)) {			
			$link = $this->addLogsDir($this->logsPathDir);
			
			$prefix_log = date("d.m.Y");
			
			$linkFile = $link."/".$prefix_log . "_acces_log.txt";			
			$dateTime = date("d.m.Y - G:i:s");
			
			$addDataFile = $dateTime." ".$string;		
			
			return $this->writeFile($linkFile, $addDataFile);
		}
		return false;		
	}
	
	/** 
	 * записывает строку в файл логов, если файла нет, создает его
	 * 
	 * @param $string string
	 * 
	 * @return bool
	 */
	
	public function addErrorLog($string): bool {
		if($this->isDirExist($this->logsPathDir)) {
			$link = $this->addLogsDir($this->logsPathDir);
			
			$prefix_log = date("d.m.Y");
			
			$linkFile = $link."/".$prefix_log . "_error_log.txt";			
			$dateTime = date("d.m.Y - G:i:s");
			
			$addDataFile = $dateTime." ".$string;
			
			return $this->writeFile($linkFile, $addDataFile);
		}
		return false;
	}
	
	
	/** 
	 * записывает строку в файл логов, если файла нет, создает его
	 * 
	 * @param $string string
	 * 
	 * @return bool
	 */
	
	public function addTelephonyLog($string): bool {
		if($this->isDirExist($this->logsPathDir)) {
			$link = $this->addLogsDir($this->logsPathDir);
			
			$prefix_log = date("d.m.Y");			
			$linkFile = $link."/".$prefix_log . "_telephony.txt";			
			return $this->writeFile($linkFile, $string);
		}
		return false;
	}
	
	
	/** 
	 * записывает строку в файл ошибок логов, если файла нет, создает его
	 * 
	 * @param $string string
	 * 
	 * @return bool
	 */
	
	public function addTelephonyErrLog($string): bool {
		if($this->isDirExist($this->logsPathDir)) {
			$link = $this->addLogsDir($this->logsPathDir);
			
			$prefix_log = date("d.m.Y");			
			$linkFile = $link."/".$prefix_log . "_err_telephony.txt";			
			return $this->writeFile($linkFile, $string);
		}
		return false;
	}
		
	
	private function writeFile($linkFile, $addDataFile):bool {
		
		$handle = fopen($linkFile, "a");
				
		if (flock($handle, LOCK_EX)) {
			fwrite($handle, $addDataFile);
			fwrite($handle, "\n");
			flock($handle, LOCK_UN);
			fclose($handle);
			return true;
		}
		return false;
	}
	
	private function isDirExist($linkDir) : bool {
		$link = $this->prepareLink($linkDir);
		if(is_dir($link)) {
			return true;
		} else {
			return false;
		}
	}
	
	private function addDir($linkDir) : bool {
		$link = $this->prepareLink($linkDir);	
		return mkdir($link, 0777, true);		
	}
	
	private function prepareLink($link){
		return str_replace("//", "/", $link);
	}
	
	private function addLogsDir($logsPathDir) : string {
		if(!$this->isDirExist($logsPathDir)) {
			// exeption error
		}
		
		
		$yearLinkDir = $logsPathDir."/".$this->yearDir;
				
		if(!$this->isDirExist($yearLinkDir)){
			if(!$this->addDir($yearLinkDir)){
				// exeption error
			}
		}
				
		$monthLinkDir = $yearLinkDir."/".$this->monthDir;
				
		if(!$this->isDirExist($monthLinkDir)){
			if(!$this->addDir($monthLinkDir)){
				// exeption error
			}
		}
		
		return $monthLinkDir;
	}	

}