<?php
/**
 * Workload Manager API sample application
 * 14/9/22 a.ide
 */
//
// zendfw application for path
//
define('APPLICATION_PATH', '../../../application');

//本番環境
//define('APPLICATION_ENVIRONMENT', 'production');
//開発環境
define('APPLICATION_ENVIRONMENT', 'development_mw_prepre_ops');

// REQUIRE APPLICATION BOOTSTRAP（ブートストラップ）
// アプリケーション固有のセットアップを行います。
// MVC環境の利用のためのセットアップだったり、
// テスト環境用のセットアップ、開発環境用のセットアップなどができます。
require APPLICATION_PATH . '/bootstrap.php';

// DISPATCH（ディスパッチ）
// Dispatch the request using the front controller.
// The front controller is a singleton, and should be setup by now. We
// will grab an instance and dispatch it, which dispatches your
// application.
$front = Zend_Controller_Front::getInstance();
//自動描画モードをオフ
$front->setParam('noViewRenderer', true);
//ユーザデータを渡す
//$front->setParam('DIVS', $DIVS);

// アプリケーションを実行する
$front->dispatch();
?>
