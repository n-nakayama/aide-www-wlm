<?php
require_once 'Zend/Controller/Action.php';
//require_once 'Zend/Layout.php';
//require_once 'Zend/View.php';
require_once "lib/View.php";

class IndexController extends Zend_Controller_Action{

	private $nextform = "index.phtml";

	/**
	 * entry point
	 */
	public function indexAction() {
		$this->serviceAction();
	}

	/**
	 * main
	 */
	public function serviceAction() {
		//get configuration
		$conf = Zend_Registry::getInstance()->configuration;
		//define("URL_WLM", $conf->get("URL_TEMPLATE"));
		define("UPLOAD_DIR", $conf->get("uploaddir"));

		define("ENDPOINT_SERVICE", $conf->get("ENDPOINT_SERVICE"));
		define("MODELS", $conf->get("MODELS"));

		define("FQDN_IAM", $conf->get("FQDN_IAM"));
		define("FQDN_IAMV3", $conf->get("FQDN_IAMV3"));

		define("FQDN_EP", $conf->get("FQDN_EP"));
		if (isset($conf->sshfwds)) {
			define("FQDN_FROM_EP", $conf->sshfwds->get("REPLACE_EP_FROM"));
		}else{
			define("FQDN_FROM_EP", "");
		}

		//ユーザ一覧
		$users = $conf->get("users");

		//ビジネスロジック呼出し用パラメータとビジネスロジック（phpファイル）名の取得
		$request = $this->getRequest()->getParams();

		require_once APPLICATION_PATH."/models/".MODELS."/apiIndex.php";
		$svc = new apiIndex($this->getRequest()->getBaseUrl(), $users);	//, $conf);
		$res = $svc->exec($request);
		if (empty($res) || (!empty($res->iserr) && ($res->iserr)) || (!$svc->userinfo->isLogined)) {
			//初期表示またはログインエラー
			$svc->createHtml($res);
			echo $svc->getResultContent();
			return;
		}

		//uidあり
		if ($svc->isCfn) {
			//cfnあり(apiの実行)
			$newrequest = $this->getNewRequest($request);
			$class = "api".$svc->cfnname;
			require_once APPLICATION_PATH."/models/".MODELS."/{$class}.php";
			$bizsvc = new $class($svc);
			$res = $bizsvc->exec($newrequest);
			$content = $bizsvc->createHtmlContent($res);

		}else{
			$bizsvc = $svc;
			$content = $bizsvc->createHtmlContent($res);
		}
		//html作成
		$bizsvc->createHtml($res, $content);
		echo $bizsvc->getResultContent();
		return;
	}

	/////private

	/** Zendのオリジナルパラメータは削除する */
	private function getNewRequest($request) {
		foreach ($request as $key => $value) {
			if (($key == "controller") || ($key == "action") || ($key == "module")) {
				unset($request[$key]);
			}
			if (($key == "uid") || ($key == "cfn")) {
				unset($request[$key]);
			}
		}
		return $request;
	}


}
?>
