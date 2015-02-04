<?php
/**
 * parameter class for createXxxx
 */
class CreateParameters {

	public $items = array();

	/**
	 * construct
	 */
	public function __construct($testname = null, $basepath = null, $skipparams = array()) {
		//
		require_once APPLICATION_PATH."/models/_lib/ConfigFile.php";
		$conf = new ConfigFile($testname, $basepath);
		$items = $conf->items;

		//json direct specified
		if (array_key_exists("json", $items)) {
			$items = Functions::func_json_decode($items["json"]);
			if (is_null($items)) {
				throw new Exception("json decode error, check file: {$testname}");
			}
		}

		//requests
		foreach ($items as $k => $v) {
			if ($k == "parameters") {
				continue;
			}
			if (in_array($k, $skipparams)) {
				continue;
			}
			$this->items[$k] = $v;
		}
		if (array_key_exists("parameters", $items)) {
			$ps = explode(";", $items["parameters"]);
			$this->items["parameters"] = array();
			foreach ($ps as $p) {
				$wks = explode("=", $p, 2);
				if (count($wks) != 2) {
					continue;
				}
				$k = trim($wks[0], " \t\n\r\0\x0B\"");
				$v = trim($wks[1], " \t\n\r\0\x0B\"");
				if ((strlen($k) < 1) || (strlen($v) < 1)) {
					continue;
				}
				$this->items["parameters"][$k] = $v;
			}
		}
	}

}
?>
