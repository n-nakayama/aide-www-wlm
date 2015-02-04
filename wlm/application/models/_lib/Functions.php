<?php
/**
 * Functions
 * a.ide
 */

$vs = explode(".", phpversion());
if (!defined("PHP_MAJOR_VERSION")) {
	define('PHP_MAJOR_VERSION',   $vs[0]);
}
if (!defined("PHP_MINOR_VERSION")) {
	define('PHP_MINOR_VERSION',   $vs[1]);
}
if (!defined("PHP_RELEASE_VERSION")) {
	define('PHP_RELEASE_VERSION', $vs[2]);
}

class Functions {

	/** replace URL */
	/** unused
	public static function forwardURL($fromURL = null) {
		$ips = explode(",", URL_FWD_IP);
		$ports = array();
		$ports[0] = explode(",", URL_FWD_PORT1);
		$ports[1] = explode(",", URL_FWD_PORT2);

		$url = str_replace($ips[0], $ips[1], $fromURL);
		foreach ($ports as $port) {
			$url = str_replace($port[0], $port[1], $url);
		}
		return $url;
	}
	 */


	/** json_encode (<=php5.1) ArrayToString */
	public static function func_json_encode($items) {
		if (is_string($items)) {
			return $items;
		}
		if ((PHP_MAJOR_VERSION >= 5) && (PHP_MINOR_VERSION >= 2)) {
			return json_encode($items);
		}
		require_once APPLICATION_PATH."/models/_lib/jsphon/Jsphon.php";
		return Jsphon::encode($items);
	}

	/** json_decode (<=php5.1) */
	public static function func_json_decode($response = null) {
		if ((PHP_MAJOR_VERSION >= 5) && (PHP_MINOR_VERSION >= 2)) {
			return json_decode($response);
		}
		if (strlen($response) == 0) {
			return null;
		}
		require_once APPLICATION_PATH."/models/_lib/jsphon/Jsphon.php";
		return Jsphon::decode($response);
	}


	/** null or "" is true */
	public static function isNullOrEmpty($str) {
		return (empty($str));
		/*
		if ((is_null($str)) || ($str == "")) {
			return true;
		}else{
			return false;
		}
		 */
	}


	public static function uploadFile($uploaddir = null) {
		//保存先
		if ($uploaddir == null) {
			$uploaddir = APPLICATION_PATH."/../www/tmp/";
		}
		if (!file_exists($uploaddir)) {
			throw new Exception("no directory. {$uploaddir}");
		}
		//ファイルオブジェクト
		require_once 'Zend/File/Transfer/Adapter/Http.php';
		$adapter = new Zend_File_Transfer_Adapter_Http();
		//ファイル存在チェック
		$files = array();
		foreach($adapter->getFileInfo() as $k => $info){
			extract($info);
			if ($error != 0) {
				return false;
			}
			$filename = "{$uploaddir}{$name}";
			if (file_exists("{$filename}")){
				unlink("{$filename}");
			}

			//testfile or template or jsonfile
			$istestfile = true;
			$isjsonfile = false;
			$isyamlfile = false;
			$istemplate = false;
			$istpl = substr_compare($name, ".yaml", -5, 5, true);
			if ($istpl == 0) {
				$istestfile = false;
				$isyamlfile = true;
			}else{
				$istpl = substr_compare($name, ".template", -9, 9, true);
				if ($istpl == 0) {
					$isttestfile = false;
					$istemplate = true;
				}else{
					$istpl = substr_compare($name, ".json", -5, 5, true);
					if ($istpl == 0) {
						$isjsonfile = true;
					}
				}
			}

			$fileinfo = array(
				"name" => $name,
				"path" => $uploaddir,
				"isTestfile" => $istestfile,
				"isJsonfile" => $isjsonfile,
				"isYamlfile" => $isyamlfile,
				"isTemplate" => $istemplate
			);
			$files[] = $fileinfo;
		}
		if (count($files) == 0) {
			return false;
		}
		//ファイル保存
		$adapter->setDestination("{$uploaddir}");
		if (!$adapter->receive()) {
		    $msg = "Functions.uploadFile()". print_r($adapter->getMessages(), true);
			throw new Exception($msg);
		}
		return $files;
	}

	/**
	 * ファイル名の日本語対応
	 * @param object $filename
	 */
	public static function convToFilename($filename) {
		return mb_convert_encoding($filename, "SJIS", "AUTO");
	}

	/**
	 * 指定されたファイル名/ディレクトリを削除する。
	 * 引数がディレクトリ名の場合、直下のファイルは削除されます。
	 * @param object $dirfilename
	 * @param object $isdeletedir [optional] 引数指定のディレクトリを削除
	 * @param object $isrecursive [optional] 配下のサブディレクトリを再帰的に削除
	 * @param object $exts [optional] 削除対象のファイル拡張子
	 * @param object $filestrings [optional] ファイルに含まれる文字列(削除対象にする) ※テスト中
	 * @return
	 */
	public static function deleteFiles($dirfilename, $isdeletedir = false, $isrecursive = false, $exts = array(), $filestrings = array()) {
		$dirfilename = Functions::convToFilename($dirfilename);	//日本語対応 a.ide
		if (!file_exists($dirfilename)) {
			return;
		}
		if (is_dir($dirfilename)) {
			$filelist = scandir($dirfilename);
			foreach((array)$filelist as $k => $file) {
				if (($file == ".")||($file == "..")) {
					continue;
				}
				if (($isrecursive)&&(is_dir("{$dirfilename}/$file"))) {
					//再帰的にサブディレクトリも削除
					Functions::deleteFiles("{$dirfilename}/$file", $isdeletedir, $isrecursive, $exts, $filestrings);
				}else{
					Functions::isCheckFileAndDelete("{$dirfilename}/$file", $exts);
				}
			}
			if ($isdeletedir) {
				rmdir($dirfilename);
			}
		}else{
			Functions::isCheckFileAndDelete($dirfilename, $exts);
		}
		return;
	}
	private static function isCheckFileAndDelete($filename, $exts) {
		$isfind = true;
		foreach ($exts as $k => $v) {
			$p = strrpos(strtolower($filename), $v);
			if ($p !== false) {
				$isfind = true;
				break;
			}
			$isfind = false;
		}
		if ($isfind) {
			unlink("{$filename}");
//			print "{$filename}<br>\n";	//※デバッグ用 121212 a.ide
		}
		return $isfind;
	}

}
?>
