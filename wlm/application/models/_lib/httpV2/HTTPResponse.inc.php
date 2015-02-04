<?php
/**
 * http response class
 * update 141106 a.ide Zend_Http_Response対応
 */
class HTTPResponse {

	public $iserr = false;
	public $status = null;
	public $statusCode = 0;
	public $header = array();
	public $body = null;

	public $isContentTypeHtml = false;
	public $isContentTypeJson = false;
	public $isContentTypeXml = false;

	public $request = null;

	/**
	 * constructor
	 */
	public function __construct($response = null, $request = null) {
		$this->request = $request;

		//Zend_Http_Response対応
		if (is_object($response)) {
			$this->status = $response->getStatus();
			$this->statusCode = $response->getStatus();
			$this->header = $response->getHeaders();
			$this->body = Functions::func_json_decode($response->getRawBody());

			if (isset($request["request"])) {
				$wks = explode("\r\n\r\n", $request["request"], 2);
				if (count($wks) < 2) {
					$this->request["header"] = explode("\r\n", $wks[0]);
					$this->request["body"] = null;
				}else{
					$this->request["header"] = explode("\r\n", $wks[0]);
					$this->request["body"] = $wks[1];
				}
			}
			$this->request["response"] = $response->getRawBody();
			return;
		}

		if (empty($response)) {
			return;
		}
		//header and body
		$wks = explode(CRLF.CRLF, $response, 3);
		//body
		if (count($wks) >= 3) {	//for proxy
			$header0 = $wks[0];
			$header = $wks[1];
			$this->body = $wks[2];

		}else if (count($wks) >= 2) {
			$header0 = null;
			$header = $wks[0];
			$this->body = $wks[1];
		}else{
			$header = null;
		}

		//header
		$lines = explode(CRLF, $header);
		$cnt = 0;
		foreach ($lines as $line) {
			$cols = explode(": ", $line, 2);
			if (empty($cols)) {
				continue;
			}
			if (count($cols) == 1) {
				$this->status = "{$cols[0]} ".(empty($header0) ? "" : "(proxy:{$header0})");
				continue;
			}
			if (count($cols) == 2) {
				$this->header[$cols[0]] = $cols[1];
			}
			$k = strtolower($cols[0]);
			$v = strtolower($cols[1]);
			if ($k == "content-length") {
				$this->size = $cols[1];
			}
			if ($k ==  "content-type") {
				if (strpos($v, "application/x-amz-json-1.1") !== false) {
					$this->isContentTypeJson = true;
					$this->body = Functions::func_json_decode($this->body);

				}else if (strpos($v, "application/json") !== false) {
					$this->isContentTypeJson = true;
					$this->body = Functions::func_json_decode($this->body);

				}else if (strpos($v, "application/xml") !== false) {
					$this->isContentTypeXml = true;

				}else{
					$this->isContentTypeHtml = true;
				}
			}
		}

		//check error
		$stss = explode(" ", $this->status);
		if (count($stss) >= 2) {
			if ($stss[1] >= 300) {
				$this->iserr = true;
			}
			$this->statusCode = $stss[1];
		}
	}

	/** status=100の場合、レスポンスを再設定するための判断用 */
	public function isStatusContinue() {
		return ($this->statusCode == 100);
	}

	/**
	 * エラーフラグの再設定
	 * ※apiによって正常終了コードが異なるので・・・
	 */
	public function resetErrCode($rescodes = array()) {
		$stss = explode(" ", $this->status);
		if (count($stss) < 2) {
			return;
		}
		foreach ($rescodes as $code) {
			if ($stss[1] == $code) {
				$this->iserr = false;
				return;
			}
		}
		return;
	}

}
?>