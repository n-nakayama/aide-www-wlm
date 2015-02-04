<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiCreateLayer extends apiIndex {
	public static $APIURL = "";

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

			//api parameters
			if ((isset($submit)) && (isset($testfile))) {
				//upload testfile
				$basepath = APPLICATION_PATH.UPLOAD_DIR;
				$files = Functions::uploadFile($basepath);
				if ($files === false) {
					throw new Exception("no files upload.");
				}
				$testfile = $files[0]["name"];
				$basepath = $files[0]["path"];

				//api parameter
				$urlitems = array();
				require_once APPLICATION_PATH."/models/".MODELS."/apiParameter.php";
				$params = new CreateParameters($testfile, $basepath);
				$body = $params->items;

				//
				$body["StackId"] = str_replace("%stackid%", $stackid, $body["StackId"]);


			}else if (isset($testfile)) {
				$basepath = APPLICATION_PATH."/models/".MODELS."/tests/";
				unset($request["testfile"]);

			}else{
				//submitなし。アップロードフォームの表示
				$res = new HTTPResponse();
				$res->body = $this->createForm($request);
				return $res;
			}

			//ops api
			$res = $this->sendRequest("POST", $request, $body, $urlitems);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

	/** template select form */
	private function createForm($request) {
		extract($request);

		$view = parent::createViewInstance("/".MODELS);

		$view->setSearchPattern("stackinfo");
		$view->addColumnItems("stackname", $stackname);
		$view->addColumnItems("stackid", $stackid);
		$htmlrec = $view->render("index_contentform.phtml");

		$view->setSearchPattern("formptn1");
		//action
		$view->addColumnItems("baseurl", apiIndex::$BASE_URL);
		$view->addColumnItems("userid", $this->userinfo->id);
		$view->addColumnItems("MODELS", MODELS);
		$view->addColumnItems("apiname", $this->cfnname);
		$view->addColumnItems("stackname", $stackname);
		$view->addColumnItems("stackid", $stackid);
		$view->addColumnItems("formmessage", "");
		$view->addColumnItems("stackinfo", $htmlrec);


		return $view->render("index_contentform.phtml");
	}

}
?>
