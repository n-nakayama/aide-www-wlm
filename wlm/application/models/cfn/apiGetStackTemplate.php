<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiGetStackTemplate extends apiIndex {
	public static $APIURL = "/stacks/%stackname%/%stackid%/template";

	/**
	 * constructot
	 */
	public function __construct($index) {
		$this->init($index, self::$APIURL);
	}

	/**
	 * execute service
	 */
	public function exec($request = array()) {
		try{
			extract($request);
			//api parameter
			$urlitems = array(
				"stackname" => (isset($stackname) ? $stackname : ""),
				"stackid" => (isset($stackid) ? $stackid : ""),
			);
			//cfn api
			$res = $this->sendRequest("GET", $request, null, $urlitems);

			//create templatefile
			$filename = "getStackTemplate_{$stackname}.template";
			$this->saveTemplateFile($filename, $res->body);

			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

	//////////

	/** template to file for download */
	private function saveTemplateFile($filename, $body) {
		try{
			$basepath = APPLICATION_PATH.UPLOAD_DIR;
			$f = @fopen("{$basepath}{$filename}", "w");
			if (!$f) {
//				throw new Exception("not create template file. {$basepath}{$inifilename} (1)");
				return false;
			}
			$str = Functions::func_json_encode($body);
			$str = str_replace("{", "{\r\n", $str);
			$str = str_replace("}", "}\r\n", $str);
			fputs($f, $str);
			fclose($f);
			return true;

		}catch(Exception $ex) {
			throw new Exception("apiGetStackTemplate.getTemplateFile(). ". $ex->getMessage());
		}

	}


}
?>
