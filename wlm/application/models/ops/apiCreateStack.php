<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiCreateStack extends apiIndex {
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
			//$stacks = array("StackIds" => array( "aaa", "null" ));
			//$body = Functions::func_json_encode($stacks);

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


			}else if (isset($testfile)) {
				$basepath = APPLICATION_PATH."/models/".MODELS."/tests/";
				unset($request["testfile"]);

			}else{
				//submitなし。アップロードフォームの表示
				$res = new HTTPResponse();
				$res->body = $this->createForm();
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
	private function createForm() {
		$view = parent::createViewInstance("/".MODELS);

		$view->setSearchPattern("stackmsg_create");
		$htmlrec = $view->render("index_contentform.phtml");

		$view->setSearchPattern("formptn1");
		//action
		$view->addColumnItems("baseurl", apiIndex::$BASE_URL);
		$view->addColumnItems("userid", $this->userinfo->id);
		$view->addColumnItems("MODELS", MODELS);
		$view->addColumnItems("apiname", $this->cfnname);
		$view->addColumnItems("stackname", "");
		$view->addColumnItems("stackid", "");
		$view->addColumnItems("formmessage", "");
		$view->addColumnItems("stackinfo", $htmlrec);


		return $view->render("index_contentform.phtml");
	}

}
?>
