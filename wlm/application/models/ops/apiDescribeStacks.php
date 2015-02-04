<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiDescribeStacks extends apiIndex {
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

			//api parameter
			$body = array(
				"StackIds" => array(
//					"c9e76500-d4ad-44a6-a380-92e6c2332a13"
				)
			);

			//ops api
			$res = $this->sendRequest("POST", $request, $body);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

}
?>
