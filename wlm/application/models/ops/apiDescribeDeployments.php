<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiDescribeDeployments extends apiIndex {
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
			if (isset($appid)) {
				$body = array(
					"AppId" => "$appid"
				);

			}else if (isset($deploymentid)) {
				$body = array(
					"DeploymentIds" => array(
						"$deploymentid"
					)
				);

			}else if (isset($stackid)) {
				$body = array(
					"StackId" => $stackid
				);
			}

			//test
//			$body = array(
//				"AppIds" => array(
//					"51c9f8ba-125f-4dc8-b5db-fb2c023f9af0"
//				),
//				"StackId" => "$stackid"
//			);

			//ops api
			$res = $this->sendRequest("POST", $request, $body);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

}
?>
