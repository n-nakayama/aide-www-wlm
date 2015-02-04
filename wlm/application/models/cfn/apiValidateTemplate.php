<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiValidateTemplate extends apiIndex {

	static $TEMPLATE_URL_SAMPLE = "https://s3-ap-northeast-1.amazonaws.com/cloudformation-templates-ap-northeast-1/Windows_Single_Server_SharePoint_Foundation.template";
	static $TEMPLATE_FILE = "/models/cfn/templates/Windows_Single_Server_SharePoint_Foundation.template";

	/**
	 * constructot
	 */
	public function __construct($index) {
		$this->init($index, "/validate");
	}

	/**
	 * execute service
	 */
	public function exec($request = array()) {
		try{
			extract($request);

			$body = array();
			if (isset($templateurl)) {
				$body["template_url"] = self::$TEMPLATE_URL_SAMPLE;
			}
			if (isset($template)) {
				if (strlen($template) == 2) {
					$cfn = Zend_Registry::getInstance()->configuration->get("cfn");
					$fname = APPLICATION_PATH.$cfn->templates->$template;
				}else{
					$fname = APPLICATION_PATH.self::$TEMPLATE_FILE;
				}
				//get template file
				$body["template"] = $this->getTemplateSample($fname);
			}
			if (count($body) == 0) {
				if (isset($submit) && (isset($testfile))) {
					//upload testfile
					$basepath = APPLICATION_PATH.UPLOAD_DIR;
					$files = Functions::uploadFile($basepath);
					if ($files === false) {
						throw new Exception("no files upload.");
					}
					$testfile = null;	//templatefile
					foreach ($files as $file) {
						if (($file["isJsonfile"]) || ($file["isYamlfile"]) || ($file["isTemplate"])) {
							$testfile = $file["name"];
							$basepath = $file["path"];
							break;
						}
					}
					if ($testfile == null) {
						throw new Exception("no templatefile.");
					}
					$body["template"] = $this->getTemplateSample("{$basepath}/{$testfile}");

				}else{
					//submitなし。アップロードフォームの表示
					$res = new HTTPResponse();
					$res->body = $this->createForm();
					return $res;
				}
			}
			//cfn api
			$res = $this->sendRequest("POST", null, $body);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

	/** template select form */
	private function createForm() {
		$view = parent::createViewInstance("/".MODELS);

		$view->setSearchPattern("stackmsg_validate");
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


	/////

	private function getTemplateSample($fname = null) {
		$record = null;
		if (empty($fname)) {
			throw new Exception("unknown template file.");
		}
		$f = @fopen($fname, "r");
		if (!$f) {
			throw new Exception("no such file. {$fname}");
		}
		while (($rec = fgets($f)) !== false) {
			if (substr($rec, 0, 1) == "#") {
//				continue;
			}
			$record .= "{$rec}";
		}
		fclose($f);
		return $record;
	}


}
?>
