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
	public function __construct($api, $userid, $apiname, $stackinfo = array(), $viewpath = null) {
		parent::__construct($api, $userid, $apiname, $stackinfo, $viewpath);
	}

	/////

	/** override: crate html array */
	protected function createHtmlArray($res, $key, $value, $srcptn = "content1", $loop = false) {
		$id = null;
		$name = null;
		$rcid = null;
		$rcname = null;

		$htmlrec = "";
		$view = $this->createViewInstance();
		$view->setSearchPattern($srcptn);
		foreach ($value as $k => $v) {
			if ($this->isObjectOrArray($v)) {
				$vals = $this->createHtmlArray($res, $k, $v, "content1", true);
				$htmlval = $vals["html"];

				//apilinks
				if (($vals["id"] != null) && ($vals["name"] != null)) {
					//stackinfo
					$stackinfo = "{$vals['name']} {$vals['id']}<br>";

					if (($vals["rcid"] != null) && ($vals["rcname"] != null)) {
						//resourceinfo
						$rcinfo = "{$vals['rcname']} {$vals['rcid']}<br>";

						$apilinks = $this->createHtmlLinks($vals["name"], $vals["id"], $vals["rcname"]);
						$htmlapilink = implode(" ", $apilinks);

					}else{
						$rcinfo = "";

						$apilinks = $this->createHtmlLinks($vals["name"], $vals["id"]);
						$htmlapilink = implode(" ", $apilinks);

					}

					$viewmei = $this->createViewInstance();
					$viewmei->setSearchPattern("header1");
					$viewmei->addColumnItems("key", "{$stackinfo}{$rcinfo}{$htmlapilink}");
					$htmlrec .= $viewmei->render("index_content.phtml");
				}

			}else{
				$htmlval = $v;
				if ($k == "id") {
					$id = $v;

				}else if ($k == "stack_name") {
					$name = $v;

				}else if ($k == "physical_resource_id") {
					$id = $this->stackinfo["stackid"];
					$name = $this->stackinfo["stackname"];
					$rcid = $v;

				}else if ($k == "resource_name") {
					$id = $this->stackinfo["stackid"];
					$name = $this->stackinfo["stackname"];
					$rcname = $v;
				}
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
			"id" => $id,
			"name" => $name,
			"rcid" => $rcid,
			"rcname" => $rcname,
			"html" => $htmlrec
		);
	}

	private function createHtmlLinks($stackname, $stackid, $resourcename = null, $resourceid = null) {
		$params = array();
		$apilinks = array();
		$view = $this->createViewInstance();

		$params["stackname"] = $stackname;
		$params["stackid"] = $stackid;

		$apilinks[] = $this->createHtmlLink($view, "apilink1", "FindStack", $params);
		$apilinks[] = $this->createHtmlLink($view, "apilink1", "ShowStackDetails", $params);
		$apilinks[] = $this->createHtmlLink($view, "apilink1", "GetStackTemplate", $params);
		$apilinks[] = $this->createHtmlLink($view, "apilink1", "ListResources", $params);
		$apilinks[] = $this->createHtmlLink($view, "apilink1", "FindStackResources", $params);
		$apilinks[] = $this->createHtmlLink($view, "apilink1", "ListStackEvents", $params);
		$apilinks[] = $this->createHtmlLink($view, "apilink1", "FindStackEvents", $params);

		if ($resourcename != null) {
			$params["resourcename"] = $resourcename;

			$apilinks[] = $this->createHtmlLink($view, "apilink1", "ShowResourceData", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "ListResourceEvents", $params);
		}else{

			$apilinks[] = $this->createHtmlLink($view, "apilink2", "UpdateStack", $params);
			$apilinks[] = $this->createHtmlLink($view, "apilink1", "DeleteStack", $params);
		}

		return $apilinks;
	}

}
?>
