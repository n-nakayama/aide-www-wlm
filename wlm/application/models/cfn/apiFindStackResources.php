<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiFindStackResources extends apiIndex {
	public static $APIURL = "/stacks/%stackname%/resources";

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

			//response codes
			$oks = array( 302 );
			$res->resetErrCode($oks);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}
}
?>
