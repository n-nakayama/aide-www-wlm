<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiDescribeCommands extends apiIndex {
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
			if (isset($commandid)) {
				$body = array(
					"CommandIds" => array(
						"$commandid"
					)
				);

			}else if (isset($deploymentid)) {
				$body = array(
					"DeploymentId" => "$deploymentid"
				);

			}else if (isset($instanceid)) {
				$body = array(
					"InstanceId" => $instanceid
				);
			}

			//test
//			$body = array(
//				"InstanceId" => "$instanceid"
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
