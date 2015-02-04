<?php
/**
 * Identity class for Abstract
 * a.ide
 */
require_once "httpV2/HTTPRequest.inc.php";
require_once "Functions.php";

//Identity service
define("URL_IAM", "%FQDN%");

abstract class apiIdentityAbstract {
	public $res = null;

	public $islogined = false;

	protected $body = null;

	protected $isobject = true;	//body type(object or array(<=php5.1))

	/**
	 * constructer
	 */
	protected function __construct($userinfo, $fqdn = null, $auth = array()) {
		try{
			//create http request
			$url = str_replace("%FQDN%", $fqdn, URL_IAM);
			$config = array(
				"headers" => array(
					"Content-Type" => "application/json",
					"Connection" => "Close",
//					"Host" => "identity.pstg.cloud.global.fujitsu.com"	//インシデント回避 141029 a.ide
				)
			);

			//proxy
			$proxy = $userinfo->getProxy();
			if (!empty($proxy)) {
				$config["proxy"] = $proxy;
			}

			//curlオプション
			$curlopts = $userinfo->getCurlopts();
			if (!empty($curlopts)) {
				$config["curloptions"] = $curlopts;
			}

			//send
			$http = new HTTPRequest($url, $config, 80);	//timtout for ST issued, a,ide
			$res = $http->send("POST", null, $auth);

			//recive http response
			$this->islogined = !$res->iserr;
			$this->body = $res->body;
			$this->res = $res;

			$this->isobject = is_object($this->body);

		}catch(Exception $ex) {
			throw $ex;
		}
	}

}
?>
