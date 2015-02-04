<?php
/**
 *
 * 2012/6/8 a.ide
 * View extension Zend_View
 *
 */
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: View.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * Super class for extension.
 */
require_once 'Zend/View.php';

/**
 * Concrete class for handling view scripts.
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Cfmg_View extends Zend_View
{
	private $_patternStart = null;
	private $_patternStart2 = null;
	private $_patternEnd = null;

	private $_removeItems = array();
	private $_removeEnd = "<!-- REMOVE:END -->";


	private $_columnItems = array();

    /**
     * Constructor
     *
     * Register Zend_View_Stream stream wrapper if short tags are disabled.
     *
     * @param  array $config
     * @return void
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

	/**
	 * 明細のパターン検索文字の設定
	 * @param object $string
	 * @return
	 */
	public function setSearchPattern($string = null) {
		if ($string == null) {
			$this->_patternStart2 = null;
			$this->_patternStart = null;
			$this->_patternEnd = null;
		}else{
			$this->_patternStart2 = "<!-- SEARCH:$string -->";
			$this->_patternStart = "<!-- SEARCH:START $string -->";
			$this->_patternEnd = "<!-- SEARCH:END $string -->";
		}
	}

	/**
	 * ページ作成時にphtmlファイルから削除する範囲を設定（複数可）
	 * @param object $string
	 * @return
	 */
	public function addRemovePattern($string) {
//		$cnt = count($this->_removeItems);
		$this->_removeItems[$string] = "<!-- REMOVE:START $string -->";
	}

	/**
	 * カラムデータの設定（一括）
	 * @param object $columnName
	 * @param object $value
	 * @return
	 */
	public function addColumnItemsAll($items = array()) {
		foreach ($items as $columnName => $value) {
			$this->_columnItems[$columnName] = $value;
		}
	}
	/**
	 * カラムデータの設定
	 * @param object $columnName
	 * @param object $value
	 * @return
	 */
	public function addColumnItems($columnName, $value) {
		$this->_columnItems[$columnName] = $value;
	}

	/**
	 * 削除パターンのクリア
	 * @return
	 */
	public function clearRemoveItems() {
		$this->_removeItems = array();
	}

	/**
	 * カラムデータのクリア
	 * @return
	 */
	public function clearColumnItems() {
		$this->_columnItems = array();
	}

	/**
	 * バッファ内容の書き換え(置き換え文字列をデータへ置き換える)
	 * @param object $buffer
	 * @return
	 */
	protected function reBuffer($buffer) {
//		$buffer = preg_replace('/<\?(?!xml|php)/s', '<?php ', $buffer);
		//明細パターン部分を削除
		if (count($this->_removeItems) > 0) {
			$buffer2 = $this->removeBuffer($buffer);
		}else{
			$buffer2 = $buffer;
		}

		//タイトル明細パターンの抜粋
		if ($this->_patternStart != null) {
			$poss = stripos($buffer2, $this->_patternStart);
			if ($poss === false) {
				$poss = stripos($buffer2, $this->_patternStart2);
				if ($poss === false) {
					return $buffer2;
				}
			}
			$pose = stripos($buffer2, $this->_patternEnd, $poss + 1);
			if ($pose === false) {
				$pose = stripos($buffer2, "<!-- SEARCH:", $poss + 1);
				if ($pose === false) {
					$pose = strlen($buffer2);
				}
			}
			$buffer2 = substr($buffer2, $poss, ($pose - $poss));
			//パターン指定の文字列(<!-- SERACH: ... -->)は出力しない
			$bufs = explode("\n", $buffer2, 2);
			$buffer2 = (count($bufs) == 2) ? $bufs[1] : $buffer2;
		}else{
			$buffer2 = $buffer2;
		}
		//明細パターンへデータ埋め込み
		foreach ($this->_columnItems as $k => $v) {
			$buffer2 = preg_replace("/\[\+$k\+\]/s", "$v", $buffer2);
		}
		return $buffer2;
	}
	// removeパターンが複数の場合、bufferからすべて削除する(htmlで表示しない)。
	private function removeBuffer($buffer) {
		$buffer2 = $buffer;
		foreach ($this->_removeItems as $no => $value) {
			//開始文字探す
			$poss = stripos($buffer2, $value);
			if ($poss === false) {
				continue;
			}
			//終了文字探す(入れ子対応)
			$removeEnd = str_replace(":END ", ":END $no ", $this->_removeEnd);
			$pose = stripos($buffer2, $removeEnd, $poss + 1);
			if ($pose === false) {
				$pose = stripos($buffer2, $this->_removeEnd, $poss + 1);
				if ($pose === false) {
					$pose = strlen($buffer2);
					$len = 0;
				}else{
					$len = strlen($this->_removeEnd);
				}
			}else{
				$len = strlen($removeEnd);
			}
			//終了文字直後の改行コードは削除する
			$poscrlf = stripos($buffer2, "\n", $pose);
			$pose = ($poscrlf === false) ? $pose + $len : $poscrlf + 1;
			//指定範囲の文字列を削除
			$remove = substr($buffer2, $poss, ($pose - $poss));
			$buffer2 = str_replace($remove, "", $buffer2);
		}
		return $buffer2;
	}

}
