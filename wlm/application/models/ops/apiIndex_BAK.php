<?php
/**
 * ops
 */
require_once APPLICATION_PATH."/models/BizBase.php";
require_once APPLICATION_PATH."/models/_lib/Functions.php";

class apiIndex extends BizBase {
	public static $BASE_URL = null;

	static $SAMPLE_RESPONSE = false;//apiのダミー結果

	protected static $APIVERSION = "OpsWorks_20130218.";

	/** 共通情報 */
	public $isErr = false;
	public $message = null;

	public $users = null;	//ZendConfig object
	public $requestUri = null;

	/** ユーザ情報 */
	public $userinfo = null;//apiUserInfo object

	public $isCfn = false;
	public $cfnname = null;

	public $service = null;

	/** api parameter */
	public $stackname = null;
	public $stackid = null;

	/**
	 * constructot
	 */
	public function __construct($baseurl, $users = null) {
		apiIndex::$BASE_URL = $baseurl;
		$this->users = $users;
	}

	/**
	 * execute
	 */
	public function exec($request = array()) {

print "DEBUG: apiIndex.php:WLM OpsWorks<br>\n";

		if (count($request) < 4) {
			return;	//初期表示
		}

		//userinfoの作成
		extract($request);
		if (!(isset($uid) && !empty($uid))) {
			return null;	//初期表示
		}

		//ログイン処理
 		require_once APPLICATION_PATH."/models/_lib/apiUserInfo.php";
		$this->userinfo = new apiUserInfo($uid, $this->users);
		if (!$this->userinfo->isLogined) {
			//ログインエラー
			return $this->userinfo->iam->res;
		}

		//BizServiceのクラス名を設定(apiの実行)
		if (isset($ops)) {
			$this->isCfn = true;
			$this->cfnname = $ops;
			//
			//request params
			$this->stackname = (isset($stackname)) ? $stackname : null;
			$this->stackid = (isset($stackid)) ? $stackid : null;

			return $this->isCfn;
		}

		//ログインOK
		//endpoint表示用にresオブジェクトの再作成
		$this->cfnname = "Login OK: " .$this->userinfo->id;
		$res = $this->userinfo->iam->getServiceCatalogs();
		return $res;
	}


	//////////サブクラス共通

	/**
	 * サブクラスからの初期化処理
	 */
	protected function Init($idx, $uri = null) {
		$this->users = $idx->users;
		$this->userinfo = $idx->userinfo;
		$this->isCfn = $idx->isCfn;
		$this->cfnname = $idx->cfnname;
		//endpoint置換
		$this->userinfo->replaceEndpoint("%apiuri%", trim($uri, "/"));
		//
		$this->stackname = $idx->stackname;
		$this->stackid = $idx->stackid;
	}

	/** send request */
	protected function sendRequest($method = "GET", $request = array(), $body = null, $uriitems = array(), $isdebug = false) {
		//params
		extract($request);
		$this->stackname = isset($stackname) ? $stackname : null;
		$this->stackid = isset($stackid) ? $stackid : null;

		//uriの再作成(uriへパラメータ埋め込み) (only CFN)
		$uri = $this->userinfo->getEndpoint();
		foreach ($uriitems as $k => $v) {
			//uri
			if (strpos($uri, "%{$k}%") === false) {
				continue;
			}
			$uri = str_replace("%{$k}%", $v, $uri);
			if (array_key_exists($k, $request)) {
				//uriで使ったパラメータは削除する
				unset($request[$k]);
			}
		}
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
		$res = $http->send($method, $request, $body);
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



	/////

	/**
	 * create html (from IndexController)
	 */
	public function createHtml($res = null, $content = null) {
		require_once APPLICATION_PATH."/models/".MODELS."/apiHtml.php";
		$html = new Html($this, MODELS);
		$htmlrec = $html->createBody($res, $content);
		$this->setResultContent($htmlrec);
	}

	/**
	 * create content (from IndexController)
	 */
	public function createHtmlContent($res) {
		require_once APPLICATION_PATH."/models/".MODELS."/apiHtml.php";
		$html = new Html($this, MODELS);
		$htmlrec = $html->createContent($res);
		return $htmlrec;
	}

}
?>
