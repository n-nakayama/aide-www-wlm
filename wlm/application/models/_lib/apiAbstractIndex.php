<?php
/**
 * Abstract class for apiIndex
 *  * @comment httpリクエストで自作のHttpクライアントを利用
 */
require_once APPLICATION_PATH."/models/BizBase.php";
require_once APPLICATION_PATH."/models/_lib/Functions.php";

abstract class apiAbstractIndex extends BizBase {
	public static $BASE_URL = null;

	protected static $SAMPLE_RESPONSE = false;	//true;	//apiのダミー結果

	/** 共通情報 */
	public $isErr = false;
	public $message = null;

	public $users = null;	//ZendConfig object
	public $requestUri = null;

	/** ユーザ情報 */
	public $userinfo = null;//apiUserInfo object

	public $isCfn = false;
	public $cfnname = null;

	/** api parameter */
	public $stackname = null;
	public $stackid = null;

	/** servicename */
	public $servicename = null;	//インシデント回避 141029 a.ide


	/**
	 * constructot
	 */
	public function __construct($baseurl, $users = null) {
		self::$BASE_URL = $baseurl;
		$this->users = $users;
	}

	/**
	 * execute (dummy)
	 */
	public function exec($request = array()) {
		return null;
	}


	//////////サブクラス共通

	/**
	 * login from subclass(apiIndex.exec())
	 */
	protected function login($request = array()) {
		if (count($request) < 4) {
			return null;	//初期表示
		}

		extract($request);
		if (!(isset($uid) && !empty($uid))) {
			return null;	//初期表示
		}
		//ログイン処理(userinfoの作成)
		require_once APPLICATION_PATH."/models/_lib/apiUserInfo.php";
		$this->userinfo = new apiUserInfo($uid, $this->users);
		if (!$this->userinfo->isLogined) {
			//ログインエラー
			$this->isErr = true;
			return $this->userinfo;
		}
		//ログインOK
		return $this->userinfo;
	}

	/**
	 * logined message from subclass(apiIndex.exec())
	 * ログイン後の表示処理
	 */
	protected function logined($request) {
		$this->cfnname = "Login OK: " .$this->userinfo->id;
		return $this->userinfo->iam->res;
	}

	/**
	 * get config.ini
	 */
	protected function getConfig($key = null) {
		if (empty($key)) {
			return Zend_Registry::getInstance()->configuration;
		}else{
			$conf = Zend_Registry::getInstance()->configuration;
			return $conf->get($key);
		}
	}


	/**
	 * サブクラスからの初期化処理
	 */
	protected function Init($idx, $uri = null, $servicename = null) {
		//shared info
		$this->users = $idx->users;
		$this->userinfo = $idx->userinfo;
		$this->isCfn = $idx->isCfn;
		$this->cfnname = $idx->cfnname;
		//request params
		$this->stackname = $idx->stackname;
		$this->stackid = $idx->stackid;

		//endpoint置換
		//※「cfn」「ops」以外はendpointを再設定する(他サービス用)
		$this->userinfo->replaceEndpointForApi($uri, $servicename);
		$this->servicename = $servicename;
	}


	/** send request */
	protected function sendRequest($method = "GET", $request = array(), $body = null, $uriitems = array(), $isdebug = false) {
		//params
		if ((!empty($request)) && (is_array($request))) {
			extract($request);
		}
		$this->stackname = isset($stackname) ? $stackname : null;
		$this->stackid = isset($stackid) ? $stackid : null;


		//uriの再作成(uriへパラメータ埋め込み only CFN)
		$uri = $this->userinfo->getEndpoint();


		/** インシデント回避 141029 a.ide  プレ環境のみ  start */
//		if (strpos(APPLICATION_ENVIRONMENT, "_pre_") !== false) {

//print "プレ環境回避処理あり(apiAbstractIndex.php)<br>\n";

//			if ($this->userinfo->getServiceName() == "etc") {
//				$url = parse_url($uri);
//				$uri = str_replace($url['host'], FQDN_CFN, $uri);
//				$headers["Host"] = $url["host"];

//			}else{
//				$headers["Host"] = FQDN_FROM_CFN;	//app.ini (cfn, ops only)
//				if (empty($headers["Host"])) {
//					unset($headers["Host"]);
//				}
//			}
//		}
		/** インシデント回避 141029 a.ide  プレ環境のみ  end */


		foreach ($uriitems as $k => $v) {
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
				"Content-Type" => "application/json",
				"X-Auth-Token" => $this->userinfo->getTokenId(),
			)
		);
		//header追加/修正
		if (isset($headers)) {
			foreach ($headers as $k => $v) {
				$config["headers"][$k] = $v;
			}
		}

		//proxy
		$proxy = $this->userinfo->getProxy();
		if (!empty($proxy)) {
			$config["proxy"] = $proxy;
		}

		//curlオプション
		$curlopts = $this->userinfo->getCurlopts();
		if (!empty($curlopts)) {
			$config["curloptions"] = $curlopts;
		}


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
		}

		return $res;
	}

	/**
	 * アプリケーションのエラー用のresponseを作成する
	 */
	protected function createExceptionOfApplication($ex, $classname = "apiAbstractIndex") {
		$res = "ERR 999 Application error {$classname}\r\n\r\n";
		$res .= (empty($ex)) ? "unknown exception\r\n" : $ex->getMessage() ."\r\n";
		return new HTTPResponse($res);
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
