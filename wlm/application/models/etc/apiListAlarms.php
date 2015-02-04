<?php
//require_once APPLICATION_PATH."/models/BizBase.php";
require_once "apiIndex.php";

class apiListAlarms extends apiIndex {
	public static $SERVICE = "metering";
	public static $APIURL = "/v2/alarms%act%%alarmid%";

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

			$uriitems = array(
				"act" => (isset($act)) ? "/{$act}" : null,
				"alarmid" => (isset($alarmid)) ? "/{$alarmid}" : null
			);

			//endpoint change
			$ep = $this->getConfig("FQDN_ALM");
			$this->userinfo->replaceEndpoint(FQDN_EP, $ep);

			//neutron api
			$res = $this->sendRequest("GET", $request, null, $uriitems);
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
				if ((isset($val->name)) && (isset($val->alarm_id))) {
					$name = $val->name;
					$id = $val->alarm_id;
				}else{
					continue;
				}
			}else if (is_array($val)) {
				if ((isset($val["name"])) && (isset($val["alarm_id"]))) {
					$name = $val["name"];
					$id = $val["alarm_id"];
				}else{
					continue;
				}
			}else{
				continue;
			}

			$stackinfo = "{$name} ({$id})";

			$params = array(
				//"alarmname" => $name,
				"alarmid" => $id,
			);

			$apilinks = array();
			//個別表示処理
			if ($key == "") {
				$apilinks[] = $html->createHtmlLink($view, "apilink2", "ShowAlarm", $params);
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
