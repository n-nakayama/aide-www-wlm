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
				if ((isset($val->Name)) && (isset($val->StackId))) {
					$name = $val->Name;
					$id = $val->StackId;
				}else{
					continue;
				}
			}else if (is_array($val)) {
				if ((isset($val["Name"])) && (isset($val["StackId"]))) {
					$name = $val["Name"];
					$id = $val["StackId"];
				}else{
					continue;
				}
			}else{
				continue;
			}

			$stackinfo = "{$name} ({$id})";

			$params = array(
				"stackname" => $name,
				"stackid" => $id,
			);

			$apilinks = array();
			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeStackSummary", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "StartStack", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "StopStack", $params);
//			$apilinks[] = $this->createHtmlLink($view, "apilink1", "UpdateStack", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DeleteStack", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeLayers", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "CreateLayer", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeInstances", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeElasticIps", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "RegisterElasticIp", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DeregisterElasticIp", $params);


			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeApps", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "CreateApp", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeDeployments", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DescribeCommands", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "CreateDeployment", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeServiceErrors", $params);

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
