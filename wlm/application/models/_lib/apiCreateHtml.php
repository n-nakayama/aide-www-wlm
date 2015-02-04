<?php
/**
 * create html super class
 */
/** view */
require_once APPLICATION_PATH.'/controllers/lib/View.php';

//html作成
abstract class CreateHtml {
	public $api = null;
	public $userid = null;
	public $apiname = null;
	public $viewpath = null;

	/**
	 * constructor
	 */
	public function __construct($api, $userid, $apiname, $viewpath = null) {
		$this->api = $api;
		$this->userid = $userid;
		$this->apiname = $apiname;
		$this->viewpath = (substr($viewpath, 0, 1) == "/") ? $viewpath : "/{$viewpath}";
	}

	/**
	 *  create html array, implement subclass
	 */
	abstract protected function createHtmlArray($res, $key, $value, $srcptn = "content1", $loop = false);


	/**
	 * create html body
	 */
	public function createBody($res, $content) {
		//ビュー(ユーザリスト)の作成
		$htmlusers = $this->createHtmlUserList($this->api->users, $this->api->userinfo);

		//html本体
		$view = $this->createViewInstance();
		//html タイトル部
		$htmlrec = $this->createHtmlTitle($view, $htmlusers);
		if ($res == null) {
			//初期表示
			return $htmlrec;
		}

		//html作成 content部(api実行結果)
		$view->addColumnItems("title", $this->api->cfnname);
		$uri = ($this->api->requestUri == null) ? "" : "<li>Endpoint: ".$this->api->requestUri."</li>";
		$view->addColumnItems("requesturi", $uri);

		//err or ok
		if ((!empty($res->iserr) && ($res->iserr)) || ($this->api->isErr)) {
			//エラーメッセージ処理
			$msg = $res->status;
			$body = $res->body;
			if (is_object($body)) {
				$msg = (isset($body->error)) ? "{$msg}: ". $body->error->message : $msg;
			}else if (is_array($body)) {
				extract($body);
				$msg = (isset($error)) ? "{$msg}: {$error['message']}" : $msg;
			}else{
				$msg = "{$msg}";	//: {$body}";
			}

		}else{
			$msg = (empty($res->status) ? null : $res->status);
		}
		$view->addColumnItems("message", $msg);

		//content
		if ($content != null) {
			$view->addColumnItems("content", $content);

		}else{
			$body = $this->createContent((empty($res->body) ? null : $res->body));
			$view->addColumnItems("content", $body);
		}

		//※未使用
		$view->addColumnItems("form", "");
		$view->addColumnItems("listitemstitle", "");
		$view->addColumnItems("listitems", "");

		//debug用
		$html = "<br>\n-----(DEBUG: apiCreateHtml.php)<br>\n";
		$html .= "Request: <br>\n";
		//表示するときはtoken文字列を削除
		$headers = array();
		if (!empty($res->request)) {
			$html .= "[uri] => ". $res->request["uri"] ."\n";
			foreach ($res->request["header"] as $v) {
				$wks = explode(": ", $v, 2);
				$headers[] = ((count($wks) == 2) && (strcasecmp($wks[0], "x-auth-token") == 0)) ? "{$wks[0]}: ※省略" : $v;
			}
		}
		$html .= "[header] => ". print_r($headers, true);
		if (empty($res->request)) {
			$html .= "[body] => ";
			$html .= "\n\n[Response] => ";
		}else{
			$html .= "[body] => ". print_r($res->request["body"], true);
			$html .= "\n\n[Response] => ". print_r($res->request["response"], true);
		}
		$html = "{$html}\n<br>-----<br>\n";
		$view->addColumnItems("listitems", $html);


		$htmlrec = $view->render("index.phtml");
		return $htmlrec;
	}

	/**
	 * create html content
	 */
	public function createContent($res) {
		$userid = $this->userid;
		$body = (empty($res->body)) ? null : $res->body;
		$htmlrec = null;

		if ($body == null) {
			return $htmlrec;
		}
		if (!$this->isObjectOrArray($body)) {
			$htmlrec .= print_r($body, true);
			return $htmlrec;
		}

		//html
		$view = $this->createViewInstance();
		//タイトル
		$view->setSearchPattern("title");
		$htmlrec .= $view->render("index_content.phtml");

		//明細
		$view->setSearchPattern("content1");
		foreach ($body as $key => $value) {
			if ($this->isObjectOrArray($value)) {
				$vals = $this->createHtmlArray($res, $key, $value);
				$htmlval = $vals["html"];
			}else{
				$htmlval = $value;
			}
			$htmlkey = (is_numeric($key)) ? "" : $key;
			//
			$view->addColumnItems("key", $htmlkey);
			$view->addColumnItems("value", $htmlval);
			$htmlrec .= $view->render("index_content.phtml");
 		}

		//html本体
		$view->setSearchPattern();
		$view->addRemovePattern("content");
		$view->addColumnItems("content", $htmlrec);
		$htmlrec = $view->render("index_content.phtml");
 		return $htmlrec;
	}


	/////

	/** ユーザ一覧の作成 */
	private function createHtmlUserList($users, $user) {
		//ビュー(ユーザリスト)の作成
		$view = $this->createViewInstance();
		$htmlrec = "";
		$comm = "";
		foreach($users as $userid => $orgid) {
			if ((!empty($user)) && ($user->isLogined) && ($userid == $user->id)) {
				$view->setSearchPattern("users1");
			}else{
				$view->setSearchPattern("users2");
			}
			$view->addColumnItems("comm", $comm);
			$view->addColumnItems("baseurl", apiIndex::$BASE_URL);
			$view->addColumnItems("userid", "$userid");
			$htmlrec .= $view->render("index.phtml");
			$comm = " | ";
		}
		return $htmlrec;
	}

	/** htmlタイトル部の作成 */
	//protected function createHtmlTitle($view, $htmlusers) {
	private function createHtmlTitle($view, $htmlusers) {
		//
		if ($this->api->userinfo == null) {
			$epcfn = FQDN_EP;
			$epiam = FQDN_IAM;
		}else{
			$epcfn = $this->api->userinfo->getEndpoint();
			$epiam = $this->api->userinfo->getIamEndpoint();
		}

		//埋め込み文字列
		$items = array(
			"MODELS" => MODELS,
			"FQDN_IAM" => $epiam,
			"FQDN_EP" => $epcfn,
			"users" => $htmlusers,
			"userid" => $this->userid,
			"baseurl" => apiIndex::$BASE_URL,
		);
		//html作成 title部
		$view->addRemovePattern("users");
		$view->addRemovePattern("apis");
		$view->addRemovePattern("form");
		$view->addColumnItemsAll($items);

		//初期表示
		$view->addColumnItems("title", "");
		$view->addColumnItems("requesturi", "");
		$view->addColumnItems("message", "");
		$view->addColumnItems("form", "");
		$view->addColumnItems("content", "");
		$view->addColumnItems("listitemstitle", "");
		$view->addColumnItems("listitems", "");
		$htmlrec = $view->render("index.phtml");

		return $htmlrec;
	}

	/////

	/** view object */
	protected function createViewInstance($viewpath = null) {
		//$view = new Cfmg_View();
		$view = new View();
		$viewpath = ($viewpath == null) ? $this->viewpath : $viewpath;
		$view->setScriptPath(APPLICATION_VIEW_PATH."{$viewpath}");
		return $view;
	}

	/** is_object or is_array */
	protected function isObjectOrArray($value) {
		return ((is_object($value)) || (is_array($value)));
	}

	/////

	public function createHtmlLink($view, $ptn, $apiname, $params = array(), $linktext = null) {
		$querystring = $this->createQueryString($params);

		$view->setSearchPattern($ptn);
		$view->addColumnItems("baseurl", apiIndex::$BASE_URL);
		$view->addColumnItems("userid", $this->userid);
		$view->addColumnItems("MODELS", MODELS);
		$view->addColumnItems("apiname", $apiname);
		$view->addColumnItems("params", $querystring);
		$view->addColumnItems("linktext", (empty($linktext) ? $apiname : $linktext));
		return $view->render("index_content.phtml");
	}

	protected function createQueryString($params) {
		$querystring = "";
		$com = "";
		foreach ($params as $k => $v) {
			if (($v == null) || ($v == "")) {
				continue;
			}
			$querystring .= "{$com}{$k}={$v}";
			$com = "&";
		}
		return $querystring;
	}

}
?>
