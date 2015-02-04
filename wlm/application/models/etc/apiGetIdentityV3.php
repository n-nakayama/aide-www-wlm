<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiGetIdentityV3 extends apiIndex {
	public static $SERVICE = "identityv3";
	public static $APIURL = "/%act%%userid%";

	/**
	 * constructot
	 */
	public function __construct($index) {
		$this->init($index, self::$APIURL, self::$SERVICE);	//apiAbstractIndex
	}

	/**
	 * execute service
	 */
	public function exec($request = array()) {
		try{
			extract($request);
			//parameters
			$uriparams = array(
				"act" => (isset($act) ? $act : null)
			);
			$tid = $this->userinfo->getTenantId();
			$uid = $this->userinfo->getUserId();
			if ($uid !== false) {
				if ($act == "users") {
					$uriparams["userid"] = "/{$uid}";
				}else{
					$uriparams["userid"] = "/{$uid}/{$act}";
				}
				$uriparams["act"] = "users";
			}
			//endpoint change
			//app.iniにFQDN_IDNが定義されてる場合、endpointを書き換える
			$ep = $this->getConfig("FQDN_IDN");
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
				"networkname" => $name,
				"networkid" => $id,
			);

			$apilinks = array();
			//個別表示処理
			//if ($key == "networks") {
			//	$apilinks[] = $html->createHtmlLink($view, "apilink2", "GetNetworkDetails", $params);
			//}

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
