<?php
/**
 * HTML for cfn
 */
require_once APPLICATION_PATH.'/models/_lib/apiCreateHtml.php';

//html作成
class Html extends CreateHtml {

	/**
	 * constructor
	 */
	public function __construct($api, $viewpath = null) {
		parent::__construct($api, (empty($api) ? null : (empty($api->userinfo)) ? null : $api->userinfo->id), (empty($api)) ? null : $api->cfnname, $viewpath);
	}

	/////

	/**
	 * override: crate html array
	 * from apiCreateHtml.CreateContent() and this
	 */
	protected function createHtmlArray($res, $key, $value, $srcptn = "content1", $loop = false) {
		$id = null;
		$name = null;
		$rcid = null;
		$rcname = null;

		//オブジェクトごとのhtmlリンクを作成
		if ($loop === false) {
			$htmlapititles = $this->createHtmlApiTitles($res, $key, $value);
		}else{
			$htmlapititles = array();
		}

		//html作成
		$htmlrec = "";
		$view = $this->createViewInstance();
		$view->setSearchPattern($srcptn);
		foreach ($value as $k => $v) {
			if ($this->isObjectOrArray($v)) {
				//
				$vals = $this->createHtmlArray($res, $k, $v, "content1", true);
				$htmlval = $vals["html"];

				//次の操作リンク
				if (array_key_exists($k, $htmlapititles)) {
					$htmlrec .= $htmlapititles[$k];
				}

			}else{
				//次の操作リンク
				if (array_key_exists($key, $htmlapititles)) {
					$htmlrec .= $htmlapititles[$key];
					unset($htmlapititles[$key]);	//1回のみなので削除する
				}
				$htmlval = $v;
			}
			$htmlkey = (is_numeric($k)) ? "" : $k;

			$view->addColumnItems("key", $htmlkey);
			$view->addColumnItems("value", $htmlval);
			$htmlrec .= $view->render("index_content.phtml");
		}
		//
		$view->setSearchPattern();
		$view->addRemovePattern("content");
		$view->addColumnItems("content", $htmlrec);
		$htmlrec = $view->render("index_content.phtml");

		return array(
			"html" => $htmlrec
		);
	}

	//次操作のタイトル部のリンクを作成 ※最初のループのみ実行される
	private function createHtmlApiTitles($res, $key, $value) {
		if (($key === "servers") || ($key === "server")) {
			require_once APPLICATION_PATH."/models/".MODELS."/apiHtmlServers.php";
			$htmlapi = new HtmlApiTitle($this, $res, $key, $value);
			$htmlrecs = $htmlapi->htmlrecs;
			return $htmlrecs;
		}
		//etc resources
		$keys = array(
			"flavors", "images",
			"networks", "subnets", "subnet", "ports", "quotas",
			"routers", "router",
			"security_groups", "security_group",
			"floatingips",
			"volumes", "objectstorages",
			"alarms",
			"projects",
			"0"
		);
		if (in_array($key, $keys, true)) {
			$view = $this->createViewInstance();
			return $this->api->createHtmlApiTitles($this, $view, $res, $key, $value);

		}else{
			return array();
		}
	}

}
?>
