<?php
/**
 * HTML for ops
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

		$stackname = $this->api->stackname;
		$stackid = $this->api->stackid;

		foreach ($value as $k => $v) {
			if (!is_numeric($k)) {
				continue;
			}
			if (is_object($v)) {
				if (isset($v->AppId)) {
					$id = $v->AppId;
					$name = $v->Name;
				}else{
					continue;
				}
			}else if (is_array($v)) {
				if (isset($v["AppId"])) {
					$id = $v["AppId"];
					$name = $v["Name"];
				}else{
					continue;
				}
			}else{
				continue;
			}

			$stackinfo = "{$stackname} ({$stackid})<br>\n";
			$stackinfo .= "{$name} ({$id})";

			$params = array(
				"stackname" => $this->api->stackname,
				"stackid" => $this->api->stackid,
				"appid" => $id,
			);

			$apilinks = array();
			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeStacks", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeApps", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "CreateApp", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DeleteApp", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeDeployments", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "CreateDeployment", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeServiceErrors", $params);

			$htmlapilink = implode(" ", $apilinks);

			$view->setSearchPattern("header1");
			$view->addColumnItems("key", "{$stackinfo}{$htmlapilink}");
			$htmlrecs[$k] = $view->render("index_content.phtml");
		}
		return $htmlrecs;
	}

}
?>
