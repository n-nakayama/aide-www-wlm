<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiDeleteLayer extends apiIndex {
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
			$body = array(
//				"LayerId" => "e01ba0fb-48af-4040-9aff-46bdc9e5e0a3"	//"{$layerid}"
				"LayerId" => "{$layerid}"
			);

			//ops api
			$res = $this->sendRequest("POST", $request, $body);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

}

?>
