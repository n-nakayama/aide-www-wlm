<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
//require_once "apiIndex.php";
require_once "apiGetNetworks.php";

class apiGetFloatingIps extends apiGetNetworks {
	public static $SERVICE = "network";
	public static $APIURL = "/v2.0/floatingips";

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
				if ((isset($val->floating_ip_address)) && (isset($val->id))) {
					$name = $val->floating_ip_address;
					$id = $val->id;
				}else{
					continue;
				}
			}else if (is_array($val)) {
				if ((isset($val["floating_ip_address"])) && (isset($val["id"]))) {
					$name = $val["floating_ip_address"];
					$id = $val["id"];
				}else{
					continue;
				}
			}else{
				continue;
			}

			$stackinfo = "{$name} ({$id})";

			$params = array(
				"networkname" => $name,
				"networkid" => $id,
			);

			$apilinks = array();

			$htmlapilink = implode(" ", $apilinks);
			//個別表示処理(※確認中)
			//if ($key == "floatingips") {
			//	$apilinks[] = $html->createHtmlLink($view, "apilink2", "GetFloatingIps", $params, "add");
			//	$htmlapilink = implode(" ", $apilinks);
			//}

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
