<?php
/**
 * config file (name=value or json format)
 */
class ConfigFile {

	public $items = array();

	/**
	 * construct
	 */
	public function __construct($inifilename, $basepath = null) {
		try{
			//file type
			$r = substr_compare($inifilename, ".json", -5, 5, true);
			$isjson = ($r == 0);

			//
			$f = @fopen("{$basepath}{$inifilename}", "r");
			if (!$f) {
				throw new Exception("no such file. {$basepath}{$inifilename} (1)");
			}
			$items = array();
			$multikey = null;
			$multivalue = null;
			while (($rec = fgets($f)) !== false) {
				//json file
				if ($isjson) {
					if (substr($rec, 0, 1) == "#") {
						continue;
					}
					//先に全部読み込み
					$multivalue .= $rec;
					continue;
				}

				//other file
				if (strlen($rec) == 0) {
					continue;
				}
				if (substr($rec, 0, 1) == "#") {
					continue;
				}

				//multilines
				if ($multikey != null) {
					if (trim($rec) == "END;") {
						$items[$multikey] = $multivalue;
						$multikey = null;
						continue;
					}
					$multivalue .= $rec;
					continue;
				}

				//
				$wks = explode("=", $rec, 2);
				if (count($wks) != 2) {
					continue;
				}
				$k = trim($wks[0], " \t\n\r\0\x0B\"");
				$v = trim($wks[1], " \t\n\r\0\x0B\"");
				if ((strlen($k) < 1) || (strlen($v) < 1)) {
					continue;
				}
				//multilines start
				if ($v == "<<<START") {
					$multikey = $k;
					continue;
				}
				//import file
				$p = stripos($v, "FILE=");
				if ($p !== false) {
					$wks = explode("FILE=", $v);
					if (count($wks) == 2) {
						$v = $this->getFileRecord("{$basepath}". substr($v, $p + 5));
					}
				}

				$items[$k] = $v;
			}
			fclose($f);

			//jsonファイル以外の場合はここで終わり
			if (!$isjson) {
				$this->items = $items;
				return;
			}

			//jsonファイルの場合の"FILE="チェック
			$items = Functions::func_json_decode($multivalue);
			if (empty($items)) {
				throw new Exception("json format error apiParamterFile={$inifilename}");
			}
			foreach ($items as $k => $v) {
				if (!is_string($v)) {
					$this->items[$k] = $v;
					continue;
				}
				$wks = explode("FILE=", $v);
				if (count($wks) != 2) {
					$this->items[$k] = $v;
					continue;
				}
				//import file (template(json) or yaml)
				$this->items[$k] = $this->getJsonStringFromFile("{$basepath}{$wks[1]}");
			}

		}catch(Exception $ex){
			throw new Exception("ConfigFile.php: ". $ex->getMessage());
		}
	}

	private function getJsonStringFromFile($filename) {
		$records = $this->getFileRecord($filename);

		//check json or yaml
		$r = substr_compare($filename, ".json", -5, 5, true);
		if ($r == 0) {
			//check jsonformat
			$json = Functions::func_json_decode($records);
			if (empty($json)) {
				throw new Exception("json format error file={$filename}");
			}
		}
		return $records;
	}

	private function getFileRecord($filename) {
		try{
			$f = @fopen($filename, "r");
			if (!$f) {
				throw new Exception("no such file. {$filename} (2)");
			}
			$record = null;
			while (($rec = fgets($f)) !== false) {
				if (substr($rec, 0, 1) == "#") {
					continue;
				}
				$record .= $rec;
			}
			fclose($f);
			return $record;

		}catch(Exception $ex){
			throw $ex;
		}

	}

}
?>
