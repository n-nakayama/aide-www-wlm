<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiGetImages extends apiIndex {
	public static $SERVICE = "image";
	public static $APIURL = "/v2/images%imageid%";

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

			//param
			$uriparams = array(
				"imageid" => ((isset($imageid)) ? "/{$imageid}" : null)
			);

			//endpoint change
			$ep = $this->getConfig("FQDN_IMG");
			$this->userinfo->replaceEndpoint(FQDN_EP, $ep);

			//images api
			$res = $this->sendRequest("GET", $request, null, $uriparams);
			return $res;

		}catch(Exception $ex){
			return $this->createExceptionOfApplication($ex, get_class());
		}
	}

	/**
	 * title部のhtmlを作成
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
//				"imagename" => $name,
				"imageid" => $id,
			);

			$apilinks = array();
			//個別表示処理
			if ($key == "images") {
				$apilinks[] = $html->createHtmlLink($view, "apilink2", "GetImages", $params, "Details");
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
