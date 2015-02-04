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
				if (isset($v->InstanceId)) {
					$instanceid = $v->InstanceId;
					$layerid = (isset($v->LayerId)) ? $v->LayerId : null;
					$hostname = (isset($v->Hostname)) ? $v->Hostname : "instanceId ";
				}else{
					continue;
				}
			}else if (is_array($v)) {
				if (isset($v["InstanceId"])) {
					$instanceid = $v["InstanceId"];
					$layerid = (isset($v["LayerId"])) ? $v["LayerId"] : null;
					$hostname = (isset($v["Hostname"])) ? $v["Hostname"] : "instanceId ";
				}else{
					continue;
				}
			}else{
				continue;
			}

			$stackinfo = "{$stackname} ({$stackid})<br>\n";
			$stackinfo .= "{$hostname} ({$instanceid})";

			$params = array(
				"stackname" => $this->api->stackname,
				"stackid" => $this->api->stackid,
				"layerid" => $layerid,
				"instanceid" => $instanceid,
			);

			$apilinks = array();
			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeStacks", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DescribeLayers", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeInstances", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink2", "RebootInstance", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "StartInstance", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "StopInstance", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "CreateInstance", $params);
//			$apilinks[] = $this->createHtmlLink($view, "apilink1", "UpdateInstance", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DeleteInstance", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeElasticIps", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "AssociateElasticIp", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DeassociateElasticIp", $params);

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "DescribeDeployments", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DescribeCommands", $params);
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
