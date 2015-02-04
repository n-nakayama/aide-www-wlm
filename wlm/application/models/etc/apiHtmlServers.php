<?php
/**
 * HTML for Servers(compute)
 * create api link on title
 */
require_once APPLICATION_PATH.'/models/_lib/apiCreateHtml.php';

//html作成
class HtmlApiTitle extends CreateHtml {

	public $htmlrecs = null;

	/**
	 * constructor
	 */
	public function __construct($html, $res, $key, $value) {
		parent::__construct($html->api, $html->userid, $html->apiname, $html->viewpath);
		$this->htmlrecs = $this->createHtmlApiTitles($res, $key, $value);
	}

	/**
	 * override: crate html array (dummy)
	 */
	protected function createHtmlArray($res, $key, $value, $srcptn = "content1", $loop = false) {
		return null;
	}

	/**
	 * htmlの階層処理(apiごとのタイトル部を作成)
	 */
	private function createHtmlApiTitles($res, $key, $value) {
		//オブジェクトのタイトル一覧
		$htmlrecs = array();
		$view = $this->createViewInstance();

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
				"servername" => $name,
				"serverid" => $id,
			);

			$apilinks = array();
			$apilinks[] = $this->createHtmlLink($view, "apilink2", "GetServerDetails", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "GetServerIps", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "GetServerDiagnostics", $params);

			$params["act"] = "os-start";
			$apilinks[] = $this->createHtmlLink($view, "apilink2", "ServerActions", $params, "StartServer");
			$params["act"] = "os-stop";
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "ServerActions", $params, "StopServer");
			$params["act"] = "soft";
			//$apilinks[] = $this->createHtmlLink($view, "apilink1", "RebootServer", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "ServerActions", $params, "RebootServer");

			//個別表示処理
			if ($key == "server") {
				$params["act"] = "pause";
				$apilinks[] = $this->createHtmlLink($view, "apilink1", "ServerActions", $params, "PauseServer");
				$params["act"] = "unpause";
				$apilinks[] = $this->createHtmlLink($view, "apilink1", "ServerActions", $params, "UnpauseServer");
				$params["act"] = "suspend";
				$apilinks[] = $this->createHtmlLink($view, "apilink1", "ServerActions", $params, "SuspendServer");
				$params["act"] = "resume";
				$apilinks[] = $this->createHtmlLink($view, "apilink1", "ServerActions", $params, "ResumeServer");
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
