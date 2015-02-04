<?php
/**
 * userinfo
 */
class apiUserInfo {

	public $id = null;	//username

	public $isLogined = false;
	public $iam = null;

	private $items = array(
		//user setting
		"domain" => null,
		"tenant" => null,
		"___pwd" => null,
		"region" => null,
		"proxy" => null,
		"accesskey" => null,
		"secretkey" => null,

		//auto setting
		"tokenId" => null,
		"tenantId" => null,
		"domainId" => null,
		"userId" => null,
		"endpoint" => null,
		"iamendpoint" => null,
		"servicename" => null,	//app.ini(ENDPOINT_SERVICE = "orchestration" or "appmanagement" or "etc")
	);

	private $isIamV2 = true;
	private $isIamV3 = false;

	/**
	 * construct
	 */
	public function __construct($userid, $users = array()) {
		$data = $users->get($userid);
		if ($data == null) {
			return;
		}
		if ($this->initialize($data) === false) {
			return;
		}

		$this->id = $userid;

		//ログイン処理
		if ($this->isIamV3) {
			$this->iam = $this->login("V3", FQDN_IAMV3, ENDPOINT_SERVICE);
		}else{
			$this->iam = $this->login("V2", FQDN_IAM, ENDPOINT_SERVICE);
		}
		if (!$this->isLogined) {
			return;
		}

		//ssh転送の場合、endpointを置換
		$this->replaceEndpoint(FQDN_FROM_EP, FQDN_EP);

	}

	private function initialize($data) {
		$wks = explode(",", trim($data));
		$cnt = count($wks);
		if ($cnt == 0) {
			return false;
		}
		$items = array();
		for ($i = 0; $i < $cnt; $i++) {
			$v = trim($wks[$i]);
			if ($v == "IAMV3") {
				$this->isIamV2 = false;
				$this->isIamV3 = true;
			}else{
				$items[] = $v;
			}
		}
		$cnt = count($items);
		$i = 0;
		foreach ($this->items as $key => $value) {
			$this->items[$key] = $items[$i];
			if ((++$i) >= $cnt) {
				break;
			}
		}

		//proxy
		$proxy = Zend_Registry::getInstance()->configuration->get("proxy");
		if (!empty($proxy)) {
			$this->items["proxy"] = $proxy;
		}

		//curlオプション
		//CURLOPT_SSL_VERIFYPEER: ssl無視
		if (isset(Zend_Registry::getInstance()->configuration->curlopts)) {
			$curl_k = Zend_Registry::getInstance()->configuration->curlopts->get("SSL_K");
			if ((!empty($curl_k) && ($curl_k == "false"))) {
				$this->items["curlopts"] = array(
					CURLOPT_SSL_VERIFYPEER => false,
					CURLOPT_SSL_VERIFYHOST => 0
				);
			}
		}

		return true;
	}

	/**	 * login	 */
	private function login($identityName = "V2", $fqdn_iam = null, $servicename = "orchestration") {
		require_once APPLICATION_PATH."/models/_lib/apiIdentity{$identityName}.php";
		try{
			$iam = new apiIdentity($this, $fqdn_iam);
			$this->items["iamendpoint"] = $fqdn_iam;
			$this->items["servicename"] = $servicename;
			if ($iam->islogined) {
				//トークン, テナントID
				$this->items["tokenId"] = $iam->getTokenId();
				$this->items["tenantId"] = $iam->getTenantId();
				$this->items["domainId"] = $iam->getDomainId();
				$this->items["userId"] = $iam->getUserId();
				//endpoint(uri)の取得
				$endpoint = $iam->getEndpoint($servicename);
				if ($endpoint === false) {
					$endpoint = (FQDN_EP != "") ? FQDN_EP : "UNKNOWN_ENDPOINT";
				}
				$this->items["endpoint"] = "{$endpoint}/%apiuri%";

				//ログインOK
				$this->isLogined = true;
			}
			return $iam;

		}catch (Exception $ex) {
			$this->isErr = true;
			print "ERR(apiUserInfo.php): ". $ex->getMessage();
			print "<br>\n";
			return false;
		}
	}


	/** iam version */
	public function isIamV3() {
		return $this->isIamV3;
	}

	/** iam endpoint */
	public function getIamEndpoint() {
		return $this->items["iamendpoint"];
	}



	/** isuser */
	public function isUser() {
		return ($this->id != null);
	}

	/** domain */
	public function getDomain() {
		return $this->items["domain"];
	}

	/** tenant */
	public function getTenant() {
		return $this->items["tenant"];
	}

	/** userid */
	public function getUserId() {
		return $this->items["userId"];
	}

	/** password */
	public function getPassword() {
		return $this->items["___pwd"];
	}

	/** accesskey */
	public function getAccessKey() {
		return $this->items["accesskey"];
	}

	/** secretkey */
	public function getSecretKey() {
		return $this->items["secretkey"];
	}

	/** region */
	public function getRegion() {
		return $this->items["region"];
	}

	/** tokenId */
	public function getTokenId() {
		return $this->items["tokenId"];
	}

	/** tenantId */
	public function getTenantId() {
		return $this->items["tenantId"];
	}

	/** proxy */
	public function getProxy() {
		return $this->items["proxy"];
	}

	/**
	 * curlオプション
	 * ssl証明書を無視する場合"false"。appp.iniに「curlopts.SSL_K」を定義
	 */
	public function getCurlopts() {
		//SSL_VERIFYPEER
		if (isset($this->items["curlopts"])) {
			return $this->items["curlopts"];
		}else{
			return null;
		}
	}
	/**
	 * ssl証明書を無視するフラグ
	 */
	public function isNoCheckSSL() {
		return (isset($this->items["curlopts"][CURLOPT_SSL_VERIFYPEER]) && $this->items["curlopts"][CURLOPT_SSL_VERIFYPEER] === false);
	}

	/**
	 * endpoint
	 */
	public function getEndpoint() {
		return $this->items["endpoint"];
	}

	/** servicename */
	public function getServiceName() {
		return $this->items["servicename"];
	}


	/**
	 * endpointの文字列「%apiuri%」を置換してuserinfoのendpointへ設定する。
	 * @param servicename ...apiごとのserviceCatalog名を指定することによってendpointを変更する
	 */
	public function replaceEndpointForApi($uri, $servicename = null) {
		if (!empty($servicename)) {
			//現状値を取得
			$endpointfrom = $this->getEndpoint();
			//servicenameのendpointを取得
			$endpoint = $this->iam->getEndpoint($servicename);
			$endpoint = trim($endpoint, "/");
			$url = parse_url($endpoint);
			if (FQDN_EP == "REPLACE_TO") {	//only prepre
				if (isset($url["host"])) {
					$fromstr = (isset($url["port"])) ? "{$url['host']}:{$url['port']}" : "{$url['host']}";
					$endpoint = str_replace($fromstr, FQDN_EP, $endpoint);
				}else{
					$endpoint = FQDN_EP;
				}
			}
			$endpoint = "{$endpoint}/%apiuri%";
			//userinfoのendpointを再設定する
			$this->replaceEndpoint($endpointfrom, $endpoint);
		}
		$this->replaceEndpoint("%apiuri%", trim($uri, "/"));
	}

	/**
	 * endpointの置換
	 * 主にssh転送用にendpointの文字列置換してuserinfoのendpointへ設定する
	 */
	public function replaceEndpoint($FROM_STR = null, $TO_STR = null) {
		$endpoint = $this->getEndpoint();
		if ((!empty($FROM_STR)) && (!empty($TO_STR)) && ($FROM_STR != $TO_STR)) {
			//ssh転送用にurlを置換
			$endpoint = str_replace($FROM_STR, $TO_STR, $endpoint);
			$this->items["endpoint"] = $endpoint;
			return;
		}
		if ((!empty($FROM_STR)) && ($FROM_STR != $TO_STR)) {
			//fromを""に置換
			$endpoint = str_replace($FROM_STR, $TO_STR, $endpoint);
			$this->items["endpoint"] = $endpoint;
			return;
		}
		$this->items["endpoint"] = $endpoint;
	}


}
?>
