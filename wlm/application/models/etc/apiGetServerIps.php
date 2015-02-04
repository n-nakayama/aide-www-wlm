<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiGetServerIps extends apiIndex {
	public static $SERVICE = "compute";
	public static $APIURL = "/servers/%serverid%/ips";

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
				"servername" => (isset($servername) ? $servername : ""),
				"serverid" => (isset($serverid) ? $serverid : "")
			);

			//endpoint change
			$ep = $this->getConfig("FQDN_COM");
			$this->userinfo->replaceEndpoint(FQDN_EP, $ep);

			//neutron api
			$res = $this->sendRequest("GET", $request, null, $urlitems);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}
}
?>
