<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiRouterActions extends apiIndex {
	public static $SERVICE = "network";
	public static $APIURL = "/v2.0/routers%routerid%%removeinterface%";

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
				"routerid" => (isset($routerid)) ? "/{$routerid}" : null,
				"removeinterface" => null
			);

			//api parameter (body)
			$method = "GET";
			$body = null;

			if (isset($act)) {
				if ($act == "rmifs") {
					$method = "PUT";
					$uriparams["removeinterface"] = "/remove_router_interface";
					$body = array(
						"subnet_id" => $subnetid
//						"subnet_id" => "624792e5-b474-4392-ae73-65404c4b26bc"
//						"subnet_id" => "cd3ac17a-257d-47c0-810b-dfb36d59f3fc"
					);

				}else if ($act == "delete") {
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
