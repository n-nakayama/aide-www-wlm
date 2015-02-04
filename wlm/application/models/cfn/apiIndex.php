<?php
/**
 * cfn
 */
require_once APPLICATION_PATH."/models/_lib/apiAbstractIndex.php";

class apiIndex extends apiAbstractIndex {

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

		//ログイン処理
		$userinfo = $this->login($request);	//apiAbstractIndex
		if (empty($userinfo)) {
			return null;				//初期表示
		}
		if (!$userinfo->isLogined) {	//ログインエラー
			return $userinfo->iam->res;
		}

		//api実行の判断
		extract($request);
		if (!isset($cfn)) {					//ログインOK(ログイン処理のみ)
			return $this->logined($request);//apiAbstractIndex
		}

		//api実行(api実行の前処理があればここに記述)
		$this->isCfn = true;
		$this->cfnname = $cfn;
		//
		//request params
		$this->stackname = (isset($stackname)) ? $stackname : null;
		$this->stackid = (isset($stackid)) ? $stackid : null;

		return $this->isCfn;
	}
}
?>
