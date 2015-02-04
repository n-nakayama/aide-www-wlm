<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
//require_once "apiIndex.php";
require_once "apiGetNetworks.php";

class apiGetSecurityGroups extends apiGetNetworks {
	public static $SERVICE = "network";
	public static $APIURL = "/v2.0/security-groups%securitygroupid%";

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
				"securitygroupid" => (isset($secid)) ? "/{$secid}" : null
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
				"secid" => $id,
			);

			$apilinks = array();
			//個別表示処理
			if ($key == "security_groups") {
				$apilinks[] = $html->createHtmlLink($view, "apilink2", "GetSecurityGroups", $params, "ShowDetails");

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
