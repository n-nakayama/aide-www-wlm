<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";


class apiCreateStack extends apiIndex {
	public static $APIURL = "/stacks";

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
			//
			if ((isset($submit)) && (isset($testfile))) {
				//upload testfile
				$basepath = APPLICATION_PATH.UPLOAD_DIR;
				$files = Functions::uploadFile($basepath);
				if ($files === false) {
					throw new Exception("no files upload.");
				}
				$testfile = null;
				foreach ($files as $file) {
					if ($file["isTestfile"]) {
						$testfile = $file["name"];
						$basepath = $file["path"];
						break;
					}else if ($file["isJsonfile"]) {
						$testfile = $file["name"];
						$basepath = $file["path"];
						break;
					}
				}
				if ($testfile == null) {
					throw new Exception("no testfile.");
				}

			}else if (isset($testfile)) {
				$basepath = APPLICATION_PATH."/models/".MODELS."/templates/";
				unset($request["testfile"]);

			}else{
				//submitなし。アップロードフォームの表示
				$res = new HTTPResponse();
				$res->body = $this->createForm();
				return $res;
			}

			//api parameter
			$urlitems = array();
			$params = new CreateStackParameters($testfile, $basepath);

			//cfn api
			$res = $this->sendRequest("POST", $request, $params->items, $urlitems);

			//response codes
			$oks = array( 201 );
			$res->resetErrCode($oks);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

	/** template select form */
	private function createForm() {
		$view = parent::createViewInstance("/".MODELS);

		$view->setSearchPattern("stackmsg_create");
		$htmlrec = $view->render("index_contentform.phtml");

		$view->setSearchPattern("formptn1");
		//action
		$view->addColumnItems("baseurl", apiIndex::$BASE_URL);
		$view->addColumnItems("userid", $this->userinfo->id);
		$view->addColumnItems("apiname", $this->cfnname);
		$view->addColumnItems("stackname", "");
		$view->addColumnItems("stackid", "");
		$view->addColumnItems("formmessage", "");
		$view->addColumnItems("stackinfo", $htmlrec);

		return $view->render("index_contentform.phtml");
	}

}

class CreateStackParameters {

	public $items = array();

	/**
	 * construct
	 */
	public function __construct($testfile = null, $basepath = null, $skipparams = array()) {
		//
		require_once APPLICATION_PATH."/models/_lib/ConfigFile.php";
		$conf = new ConfigFile($testfile, $basepath);
		$items = $conf->items;

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
			$item = $items["parameters"];
			if (!is_string($item)) {
				$this->items["parameters"] = $item;
			}else{
				if (strpos($item, "\n") === false) {
					$ps = explode(";", $item);
				}else{
					$ps = explode("\n", $item);
				}
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

}
?>
