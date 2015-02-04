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
				$name = (isset($val->resource_name)) ? $val->resource_name : null;
				$id = (isset($val->physical_resource_id)) ? $val->physical_resource_id : null;

			}else if (is_array($val)) {
				$name = (isset($val["resource_name"])) ? $val["resource_name"] : null;
				$id = (isset($val["physical_resource_id"])) ? $val["physical_resource_id"] : null;

			}else{
				continue;
			}

			$stackinfo = $this->api->stackname ."(". $this->api->stackid .")<br>\n";
			$stackinfo .= "{$name} ({$id})\n";

			$params = array(
				"stackname" => $this->api->stackname,
				"stackid" => $this->api->stackid,
				"resourcename" => $name,
				"resourceid" => $id
			);

			$apilinks = array();

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "ListStackEvents", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "FindStackEvents", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "ListResourceEvents", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "ListResources", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "FindStackResources", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "ShowResourceData", $params);

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
