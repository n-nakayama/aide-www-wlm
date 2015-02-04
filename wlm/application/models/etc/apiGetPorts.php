<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
//require_once "apiIndex.php";
require_once "apiGetNetworks.php";

class apiGetPorts extends apiGetNetworks {
	public static $SERVICE = "network";
	public static $APIURL = "/v2.0/ports";

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

			//endpoint change
			$ep = $this->getConfig("FQDN_NET");
			$this->userinfo->replaceEndpoint(FQDN_EP, $ep);

			//neutron api
			$res = $this->sendRequest("GET", $request);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}
}
?>
