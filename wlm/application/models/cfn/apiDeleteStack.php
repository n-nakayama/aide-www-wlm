<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiDeleteStack extends apiIndex {
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
			//api parameter
			$urlitems = array(
				"stackname" => $stackname,
				"stackid" => $stackid,
			);
			//cfn api
			$res = $this->sendRequest("DELETE", $request, null, $urlitems, false);

			//response codes
			$oks = array( 204 );
			$res->resetErrCode($oks);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}
}

?>
