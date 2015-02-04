<?php
/**
 * ops
 */
require_once APPLICATION_PATH."/models/_lib/apiAbstractIndex.php";

class apiIndex extends apiAbstractIndex {

	protected static $APIVERSION = "OpsWorks_20130218.";

	/**
	 * constructot
	 */
	public function __construct($baseurl, $users = null) {
		parent::__construct($baseurl, $users);
	}

	/**
	 * execute
	 */
	public function exec($request = array()) {

print "DEBUG: apiIndex.php:WLM OpsWorks<br>\n";

		//ログイン処理
		$userinfo = $this->login($request);		//apiAbstractIndex
		if (empty($userinfo)) {
			return null;					//初期表示
		}
		if (!$userinfo->isLogined) {	//ログインエラー
			return $userinfo->iam->res;
		}

		//api実行の判断
		extract($request);
		if (!isset($ops)) {
			return $this->logined($request);	//ログインOK(ログイン処理のみ)
		}

		//api実行(api実行の前処理があればここに記述)
		$this->isCfn = true;
		$this->cfnname = $ops;
		//
		//request params
		$this->stackname = (isset($stackname)) ? $stackname : null;
		$this->stackid = (isset($stackid)) ? $stackid : null;

		return $this->isCfn;

	}


	//////////サブクラス個別

	/**
	 * override: send request
	 */
	protected function sendRequest($method = "GET", $request = array(), $body = null, $uriitems = array(), $isdebug = false) {
		//params
		extract($request);
		$this->stackname = isset($stackname) ? $stackname : null;
		$this->stackid = isset($stackid) ? $stackid : null;

		$uri = $this->userinfo->getEndpoint();
		$this->requestUri = $uri;

		//リクエストヘッダーの作成
		$config = array(
			"headers" => array(
//				"Content-Type" => "application/json",
				"Content-Type" => "application/x-amz-json-1.1",
				"X-Auth-Token" => $this->userinfo->getTokenId(),
			)
		);
		//opsworksのapi名
		if (isset($ops)) {
			$config["headers"]["X-Amz-Target"] = self::$APIVERSION."{$ops}";
//			$config["headers"]["X-Fj-Target"] = self::$APIVERSION."{$ops}";
		}
		//header追加/修正
		if (isset($headers)) {
			foreach ($headers as $k => $v) {
				$config["headers"][$k] = $v;
			}
		}
//print_r($config);
		//リクエストの実行
		$http = new HTTPRequest($uri, $config);
//		$res = $http->send($method, $request, $body);	//del141027 a.ide
		$res = $http->send($method, null, $body);
		if ($isdebug) {
			//debug
			$res = new HTTPResponse();

			$res->body = "HttpResponse(): debugmode. httprequest:<br>\n";
			$res->body .= print_r($http, true);
			$res->body .= "<br>\n";

			return $res;
		}

		return $res;
	}

}
?>
