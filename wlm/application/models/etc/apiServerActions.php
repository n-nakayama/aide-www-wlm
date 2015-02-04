<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiServerActions extends apiIndex {
	public static $SERVICE = "compute";
	public static $APIURL = "/servers/%serverid%/action";

	/**
	 * constructot
	 */
	public function __construct($index) {
		$this->init($index, self::$APIURL, self::$SERVICE);
	}

	/**
	 * execute service
	 */
	public function exec($request = array()) {
		try{
			extract($request);
			//api parameter (uri)
			$urlitems = array(
				"servername" => (isset($servername) ? $servername : null),
				"serverid" => (isset($serverid) ? $serverid : null),
			);
			//api parameter (body)
			if (isset($act)) {
				if ($act == "soft") {
					$body = array(
						"reboot" => array(
							"type" => "$act"
						)
					);
				}else{
					$body = array(
						"$act" => null
					);
				}
			}

			//endpoint change
			$ep = $this->getConfig("FQDN_COM");
			$this->userinfo->replaceEndpoint(FQDN_EP, $ep);

			//neutron api
			$res = $this->sendRequest("POST", $request, $body, $urlitems);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}
}
?>
