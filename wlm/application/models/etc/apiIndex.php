<?php
/**
 * etc (resources)
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

print "DEBUG: NextS5 Resources<br>\n";

		//ログイン処理
		$userinfo = $this->login($request);		//apiAbstractIndex
		if (empty($userinfo)) {
			return null;						//初期表示
		}
		if (!$userinfo->isLogined) {
			return $userinfo->iam->res;
		}

		//api実行の準備
		extract($request);
		if (!isset($etc)) {
			return $this->logined($request);	//ログインOK(ログイン処理のみ)
		}

		//api実行(api実行の前処理があればここに記述)
		$this->isCfn = true;
		$this->cfnname = $etc;
		//
		//request params
		$this->stackname = (isset($stackname)) ? $stackname : null;
		$this->stackid = (isset($stackid)) ? $stackid : null;

		return $this->isCfn;

	}
}
?>
