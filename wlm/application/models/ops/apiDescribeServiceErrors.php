<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiDescribeServiceErrors extends apiIndex {
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
			$body = array();
			if (isset($serviceerrid)) {
				$body["ServiceErrorIds"] = array(
					"$serviceerrid"
				);

			}else if (isset($instanceid)) {
				$body["InstanceId"] = "$instanceid";	//"bde87e16-4c0b-43ae-b863-7c147a0504bc"

			}else if (isset($stackid)) {
				$body["StackId"] = $stackid;
			}

			//ops api
			$res = $this->sendRequest("POST", $request, $body);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

}
?>
