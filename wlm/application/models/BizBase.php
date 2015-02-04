<?php
abstract class BizBase {
	/**
	 * ビジネスロジックのインスタンス化（new XxxXxxx()は行わない）
	 * @return
	 */
	public static function getInstance() {
		return new BizService();
	}

	/**
	 * ビュー（html作成）オブジェクトの作成
	 * @return
	 */
	public static function createViewInstance($viewpath = null) {
		require_once APPLICATION_PATH.'/controllers/lib/View.php';
		//$view = new Cfmg_View();
		$view = new View();
		$view->setScriptPath(APPLICATION_VIEW_PATH."{$viewpath}");
		return $view;
	}

	/**
	 * サブクラスで実装する
	 * @return
	 */
	public abstract function exec($request = array());


	/////

	private $results = array("errno" => 0, "message" => "", "columnitems" => array(), "content" => "");


	public function getResults($key = null) {
		return ($key == null) ? $this->results : $this->results[$key];
	}

	public function getMessage() {
		return $this->results["message"];
	}

	public function getNextform() {
		return $this->results["nextform"];
	}

	public function getResultContent() {
		return $this->results["content"];
	}

	public function getColumnItems($key = null) {
		if ($key != null) {
			return $this->results["columnitems"][$key];
		}else{
			return $this->results["columnitems"];
		}
	}


	public function isErr() {
		if ($this->results["errno"] != 0) {
			return true;
		}
		return false;
	}

	public function isNextform() {
		if (array_key_exists("nextform", $this->results)) {
			if ($this->results["nextform"] != "") {
				return true;
			}
		}
		return false;
	}


	public function setError($errno, $message) {
		$this->results["errno"] = $errno;
		$this->results["message"] = $message;
	}

	public function setMessage($value) {
		$this->results["message"] = $value;
	}

	public function setNextform($value) {
		$this->results["nextform"] = $value;
	}

	public function setResultContent($value) {
		$this->results["content"] = $value;
	}

	public function addColumnItems($key, $value) {
		$this->results["columnitems"][$key] = $value;
	}

	public function setResults($key, $value) {
		$this->results[$key] = $value;
	}


}
?>
