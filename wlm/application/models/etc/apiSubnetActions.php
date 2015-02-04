<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiSubnetActions extends apiIndex {
	public static $SERVICE = "network";
	public static $APIURL = "/v2.0/subnets%subnetid%";

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
			$uriparams = array(
				"subnetid" => (isset($subnetid)) ? "/{$subnetid}" : null
			);

			//api parameter (body)
			$method = "GET";
			$body = null;
			if (isset($act)) {
				if ($act == "delete") {
					$method = "DELETE";
				}
			}

			//endpoint change
			$ep = $this->getConfig("FQDN_COM");
			$this->userinfo->replaceEndpoint(FQDN_EP, $ep);

			//neutron api
			$res = $this->sendRequest($method, $request, $body, $uriparams);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}
}
?>
