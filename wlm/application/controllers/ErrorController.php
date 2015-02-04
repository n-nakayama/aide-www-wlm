<?php
require_once 'Zend/Controller/Action.php';

require_once "lib/View.php";

class ErrorController extends Zend_Controller_Action{

	public function errorAction(){
		$message = "";	//$this->getResponse()->getException() ."</b><br>\n";
		foreach($this->getResponse()->getException() as $k => $ex) {
			$wks = explode("#", $ex);
			$com1 = "<b>";
			$com2 = "</b>";
			foreach($wks as $n => $wk) {
				$message .= "{$com1}{$wk}{$com2}<br>\n";
				$com1 = "#";
				$com2 = "";
			}
//			break;
		}

		//ページ遷移時の処理
//		$view = new Cfmg_View();
//		$view->setScriptPath(APPLICATION_VIEW_PATH);
		//埋め込みデータの設定
//		$view->addColumnItems("message", $message);
		//レンダリング
//		echo $view->render("index.phtml");

		echo "<b>ERROR:</b>$message<br>\n";
		print_r($this);
	}
}
?>
