<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiGetDebug extends apiIndex {
	public static $SERVICE = "Object Storage";
	public static $APIURL = "/debug";

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
			$ep = $this->getConfig("FQDN_OBJ");
			$this->userinfo->replaceEndpoint(FQDN_EP, $ep);

			//objectstorage api
			$res = $this->sendRequest("GET", $request);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}


}
?>
