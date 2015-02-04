<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
//require_once "apiIndex.php";
require_once "apiGetNetworks.php";

class apiGetSubnets extends apiGetNetworks {
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

			//parameters
			$uriparams = array(
				"subnetid" => (isset($subnetid)) ? "/{$subnetid}" : null
			);


			//endpoint change
			$ep = $this->getConfig("FQDN_NET");
			$this->userinfo->replaceEndpoint(FQDN_EP, $ep);

			//neutron api
			$res = $this->sendRequest("GET", $request, null, $uriparams);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}


	/**
	 * htmlの階層処理(apiごとのタイトル部を作成)
	 */
	public function createHtmlApiTitles($html, $view, $res, $key, $value) {
		//オブジェクトのタイトル一覧
		$htmlrecs = array();

		$isbreak = false;
		foreach ($value as $k => $v) {
			$kk = $k;
			$val = $v;
			if (!is_numeric($kk)) {
				$isbreak = true;
				$kk = $key;
				$val = $value;
			}
			if (is_object($val)) {
				if ((isset($val->name)) && (isset($val->id))) {
					$name = $val->name;
					$id = $val->id;
				}else{
					continue;
				}
			}else if (is_array($val)) {
				if ((isset($val["name"])) && (isset($val["id"]))) {
					$name = $val["name"];
					$id = $val["id"];
				}else{
					continue;
				}
			}else{
				continue;
			}

			$stackinfo = "{$name} ({$id})";

			$params = array(
				//"subnetname" => $name,
				"subnetid" => $id,
			);

			$apilinks = array();
			//個別表示処理
			if ($key == "subnets") {
				$apilinks[] = $html->createHtmlLink($view, "apilink2", "GetSubnets", $params, "ShowDetails");

			}else if ($key == "subnet") {
				$params["act"] = "delete";
				$apilinks[] = $html->createHtmlLink($view, "apilink2", "SubnetActions", $params, "DELETE");
			}

			$htmlapilink = implode(" ", $apilinks);

			$view->setSearchPattern("header1");
			$view->addColumnItems("key", "{$stackinfo}{$htmlapilink}");
			$htmlrecs[$kk] = $view->render("index_content.phtml");

			if ($isbreak) {
				break;
			}
		}
		return $htmlrecs;
	}

}
?>
