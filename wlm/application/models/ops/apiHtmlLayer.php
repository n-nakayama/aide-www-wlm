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

		foreach ($value as $k => $v) {
			if (!is_numeric($k)) {
				continue;
			}
			if (is_object($v)) {
				if (isset($v->LayerId)) {
					$id = $v->LayerId;
					$name = (isset($v->Name)) ? $v->Name : "";
				}else{
					continue;
				}

			}else if (is_array($v)) {
				if (isset($v["LayerId"])) {
					$id = $v["LayerId"];
					$name = (isset($v["Name"])) ? $v["Name"] : null;
				}else{
					continue;
				}
			}else{
				continue;
			}

			$stackinfo = $this->api->stackname." (". $this->api->stackid .")<br>\n";
			$stackinfo .= "{$name}({$id})";

			$params = array(
				"stackname" => $this->api->stackname,
				"stackid" => $this->api->stackid,
				"layerid" => $id,
				"instanceid" => (isset($instaceid) ? $instanceid : null),
			);

			$apilinks = array();
			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeStacks", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeLayers", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "CreateLayer", $params);
//			$apilinks[] = $this->createHtmlLink($view, "apilink1", "UpdateLayer", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DeleteLayer", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeInstances", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "CreateInstance", $params);

			$htmlapilink = implode(" ", $apilinks);

			$view->setSearchPattern("header1");
			$view->addColumnItems("key", "{$stackinfo}{$htmlapilink}");
			$htmlrecs[$k] = $view->render("index_content.phtml");
		}
		return $htmlrecs;
	}

}
?>
