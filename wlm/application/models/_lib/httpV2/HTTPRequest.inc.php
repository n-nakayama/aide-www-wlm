<?php
define("CRLF", "\r\n");
define("POST", "POST");

include_once("HTTPResponse.inc.php");
include_once(dirname(__FILE__)."/../Functions.php");

//print dirname(__FILE__);
//print "<br>\n";

/**
 * renew: 140916 a.ide httpリクエストをcurl_xx関数で実行するように変更
 */
class HTTPRequest {

	static $CONTENT_TYPE_HTML = "text/html;charset=\"utf-8\"";
	static $CONTENT_TYPE_XML = "text/xml;charset=\"utf-8\"";
	static $CONTENT_TYPE_JSON = "application/json";


	public $iserr = false;

	private $isContentTypeJson = false;
	private $isContentTypeHtml = false;

	private $url = null;

	private $proxyUrl = null;
	private $headers = array();
	private $curlOptions = array();

	private $isReturnTransfer = true;
	private $timeout = 15;

	private $response = null;

	/**
	 * constructor
	 */
	public function __construct($url, $config = array(), $timeout = 0) {
		$this->url = $url;
		//config
		foreach ($config as $k => $v) {
			$kk = strtolower($k);
			if ($kk == "proxy") {
				$this->proxyUrl = $v;

			}else if ($kk == "headers") {
				$this->setHeaders($v);

			}else if ($kk == "curloptions") {
				$this->curlOptions = $v;

			}else if ($kk == "returntransfer") {
				$this->isReturnTransfer = $v;

			}else if ($kk == "timeout") {
				$this->timeout = $v;
			}
		}
		$this->timeout = ($timeout > 0) ? $timeout : $this->timeout;
	}

	/**
	 * set header
	 */
	public function setHeaders($headers = array()) {
		if (!is_array($headers)) {
			return false;
		}
		foreach ($headers as $k => $v) {
			$this->headers[$k] = $v;

			$kk = strtolower($k);
			if ($kk == "content-type") {
				if (strpos($v, "application/x-amz-json-1.1") !== false) {
					$this->isContentTypeJson = true;

				}else if ($v == "application/json") {
					$this->isContentTypeJson = true;

				}
			}
		}
	}

	/**
	 * basic credentials
	 */
	public function setCredentials($user = null, $passwd = null, $header = "Authorization") {
		$this->headers[$header] = "Basic ". base64_encode("{$user}:{$pass}");
	}


	/**
	 * send request
	 * @param $page ...UNUSED (Array or String(?a=b&c=d...))
	 */
	public function send($method = "GET", $page = null, $body = null, $curloptions = array()) {
		try{
			$method = strtoupper($method);

			//URL
			if ($method == "GET") {
				$qrystr = (is_array($body)) ? http_build_query($body) : urlencode($body);
			}else{
				if (empty($body)) {
					$this->headers["Content-length"] = 0;
				}else{
					if ($this->isContentTypeJson) {
						$body = Functions::func_json_encode($body);	//toString
					}
					$this->headers["Content-length"] = strlen($body);
				}
				$qrystr = (is_array($page)) ? http_build_query($page) : urlencode($page);
			}
			$url = (strlen($qrystr) > 0) ? $this->url ."?{$qrystr}" : $this->url;

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, "PHP/" . phpversion());
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->isReturnTransfer);
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt($ch, CURLOPT_HEADER, true);

			//request method
			if ($method == "POST") {
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

			}else if ($method == "PUT") {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

			}else if ($method == "DELETE") {
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			}

			//proxy
			$ch = $this->setProxy($ch, $this->proxyUrl);

			//request header
			$headers = array();
			foreach ($this->headers as $k => $v) {
				$headers[] = "{$k}: {$v}";
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

			//etc options
			curl_setopt_array($ch, $this->curlOptions);
			curl_setopt_array($ch, $curloptions);

			//debug a.ide
//			$logpath = dirname(__FILE__) ."/../../../../";
//			curl_setopt($ch, CURLOPT_VERBOSE, true);
//			$fou = fopen("{$logpath}/wkphp_out.log", "w");
//			curl_setopt($ch, CURLOPT_STDERR, $fou);

			//execute
			$res = curl_exec($ch);
			//result
			if (curl_errno($ch)) {
				$res = "ERR 999 HTTPRequest.php ".CRLF.CRLF;
				$res .= "curl_errno=". curl_errno($ch) .CRLF;
				$res .= "curl_error=". curl_error($ch) .CRLF;
				$res .= "curl_getinfo=". print_r(curl_getinfo($ch), true) .CRLF;
				curl_close($ch);
			}else{
				curl_close($ch);
			}
			//request(最終的なリクエストとhtml整形前のレスポンスの保持)
			$request = array(
				"uri" => $url,
				"header" => $headers,
				"body" => $body,
				"response" => $res
			);

			//debug a.ide
//			if ((isset($fou)) && ($fou != null)) {
//				fclose($fou);
//			}


			//reqponseの作成
			$this->response = new HTTPResponse($res, $request);
			//status=100 resetting
			if ((!$this->response->iserr) && ($this->response->isStatusContinue())) {
				$this->response = new HTTPResponse($this->response->body, $request);
			}

			return $this->response;

		}catch(Exception $ex) {
			throw new Exception("HTTPRequest.send(): ". $ex->getMessage());
		}
	}



	/////

	/** proxy */
	private function setProxy($ch, $proxyurl) {
		if (empty($proxyurl)) {
			return $ch;
		}
		//
		// for proxy issued: http://www.symantec.com/docs/TECH210784対応
		if (isset($this->headers["Content-length"])) {
			unset($this->headers["Content-length"]);
		}

		// set proxy info
		$url = parse_url($proxyurl);
		extract($url);
		curl_setopt($ch, CURLOPT_PROXY, $host);
		if (isset($port)) {
			curl_setopt($ch, CURLOPT_PROXYPORT, $port);
		}else{
			curl_setopt($ch, CURLOPT_PROXYPORT, 8080);
		}
		if ((isset($user)) && (isset($pass))) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, "{$user}:{$pass}");
		}
		curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
		return $ch;
	}



	//////////sample code
/*
	private function sample() {
		$credentials = "username:password";
		// Read the XML to send to the Web Service
        $request_file = "./SampleRequest.xml";
        $fh = fopen($request_file, 'r');
        $xml_data = fread($fh, filesize($request_file));
        fclose($fh);

        $url = "http://www.example.com/services/calculation";
        $page = "/services/calculation";
        $headers = array(
            "POST ".$page." HTTP/1.0",
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"run\"",
            "Content-length: ".strlen($xml_data),
            "Authorization: Basic " . base64_encode($credentials)
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, $defined_vars['HTTP_USER_AGENT']);

        // Apply the XML to our curl call
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);

        $data = curl_exec($ch);

        if (curl_errno($ch)) {
            print "Error: " . curl_error($ch);
        } else {
            // Show me the result
            var_dump($data);
            curl_close($ch);
        }
	}
 */

}
?>