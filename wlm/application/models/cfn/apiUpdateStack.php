<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";
require_once "apiCreateStack.php";	//use CreateStackParametersクラス

class apiUpdateStack extends apiIndex {
	public static $APIURL = "/stacks/%stackname%/%stackid%";

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
				if (count($files) > 0) {
					$testfile = $files[0];
				}else{
					throw new Exception("no files upload.");
				}
				$testfile = null;
				foreach ($files as $file) {
					if ($file["isTestfile"]) {
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
				$res->body = $this->createForm($request);
				return $res;
			}

			//api parameter
			$urlitems = array(
				"stackname" => $stackname,
				"stackid" => $stackid,
			);
			$skipparams = array("stack_name", "disable_rollback");
			$params = new CreateStackParameters($testfile, $basepath, $skipparams);
			//cfn api
			$res = $this->sendRequest("PUT", $request, $params->items, $urlitems);

			//response codes
			$oks = array( 202 );
			$res->resetErrCode($oks);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

	private function createForm($request) {
		extract($request);

		$view = parent::createViewInstance("/".MODELS);
		//
		$view->setSearchPattern("stackinfo");
		$view->addColumnItems("stackname", $stackname);
		$view->addColumnItems("stackid", $stackid);
		$htmlrec = $view->render("index_contentform.phtml");

		$view->setSearchPattern("formptn1");
		//action
		$view->addColumnItems("baseurl", apiIndex::$BASE_URL);
		$view->addColumnItems("userid", $this->userinfo->id);
		$view->addColumnItems("apiname", $this->cfnname);
		$view->addColumnItems("stackname", $stackname);
		$view->addColumnItems("stackid", $stackid);
		$view->addColumnItems("formmessage", "");
		$view->addColumnItems("stackinfo", $htmlrec);

		return $view->render("index_contentform.phtml");
	}

}

?>
