<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiDescribeLayers extends apiIndex {
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
			if (isset($layerid)) {
				$body["LayerIds"] = array(
					"$layerid"
				);
			}
			if (isset($stackid)) {
				$body["StackId"] = "{$stackid}";

			}else if (count($body) == 0) {
				$body = null;
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
