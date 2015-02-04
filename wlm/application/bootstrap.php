<?php
// 2012/6/7 a.ide created

//
// 2014/10/10 a.ide update
//
// アプリケーションの動作環境によってlibpathを設定する(_libraryの格納先)
//
$ip = $_SERVER['SERVER_ADDR'];
//
if ($ip === "10.123.231.137") {	//yumserver
	$libpath = APPLICATION_PATH . '/../../_library/';
}else{
	$libpath = APPLICATION_PATH . '/../_library/';
}
set_include_path($libpath . PATH_SEPARATOR . get_include_path());
//
// zendfw abstract path
defined('ABSTRACTZEND_PATH')
    or define('ABSTRACTZEND_PATH', APPLICATION_PATH);

//
// app_XXXX.iniファイル名の確定
$wks = explode("/", $_SERVER["REQUEST_URI"]);
if (count($wks) >= 3) {
	define("APP_INI", "app_{$wks[2]}.ini");
}else{
	define("APP_INI", "app.ini");
}

//
/////

//
// コンポーネントをロードする
require_once 'Zend/Controller/Front.php';

// AUTOLOADER（オートロード）
// オートロードのセットアップです。
// Zend Frameworkのクラスが自動的に呼び出されるようになる仕掛けです。
// いちいちincludeやrequireなど書かなくてよくなります。
////require_once "Zend/Loader.php";
////Zend_Loader::registerAutoload();

// APPLICATION CONSTANTS - Set the constants to use in this application.
// These constants are accessible throughout the application, even in ini
// files. We optionally set APPLICATION_PATH here in case our entry point
// isn't index.php (e.g., if required from our test suite or a script).
defined('APPLICATION_PATH')
    or define('APPLICATION_PATH', dirname(__FILE__));

defined('APPLICATION_ENVIRONMENT')
    or define('APPLICATION_ENVIRONMENT', 'development');

defined('APPLICATION_VIEW_PATH')
    or define('APPLICATION_VIEW_PATH', APPLICATION_PATH.'/views');

// FRONT CONTROLLER - Get the front controller.
// The Zend_Front_Controller class implements the Singleton pattern, which is a
// design pattern used to ensure there is only one instance of
// Zend_Front_Controller created on each request.
$frontController = Zend_Controller_Front::getInstance();

// CONTROLLER DIRECTORY SETUP - Point the front controller to your action
// controller directory.
$frontController->setControllerDirectory(APPLICATION_PATH.'/controllers');

// APPLICATION ENVIRONMENT - Set the current environment
// Set a variable in the front controller indicating the current environment --
// commonly one of development, staging, testing, production, but wholly
// dependent on your organization and site's needs.
$frontController->setParam('env', APPLICATION_ENVIRONMENT);

// LAYOUT SETUP - Setup the layout component
// The Zend_Layout component implements a composite (or two-step-view) pattern
// In this call we are telling the component where to find the layouts scripts.
////Zend_Layout::startMvc(APPLICATION_PATH . '/layouts/scripts');


// VIEW SETUP - Initialize properties of the view object
// The Zend_View component is used for rendering views. Here, we grab a "global"
// view instance from the layout object, and specify the doctype we wish to
// use -- in this case, XHTML1 Strict.
////$view = Zend_Layout::getMvcInstance()->getView();


// CONFIGURATION - Setup the configuration object
// The Zend_Config_Ini component will parse the ini file, and resolve all of
// the values for the given section.  Here we will be using the section name
// that corresponds to the APP's Environment
require_once("Zend/Config/Ini.php");
$configuration = new Zend_Config_Ini(APPLICATION_PATH .'/config/'.APP_INI, APPLICATION_ENVIRONMENT);

// DATABASE ADAPTER - Setup the database adapter
// Zend_Db implements a factory interface that allows developers to pass in an
// adapter name and some parameters that will create an appropriate database
// adapter object.  In this instance, we will be using the values found in the
// "database" section of the configuration obj.
////require_once("Zend/Db/Table.php");
////$dbAdapter = Zend_Db::factory($configuration->database);

// DATABASE TABLE SETUP - Setup the Database Table Adapter
// Since our application will be utilizing the Zend_Db_Table component, we need
// to give it a default adapter that all table objects will be able to utilize
// when sending queries to the db.
////Zend_Db_Table_Abstract::setDefaultAdapter($dbAdapter);

// REGISTRY - setup the application registry
// An application registry allows the application to store application
// necessary objects into a safe and consistent (non global) place for future
// retrieval.  This allows the application to ensure that regardless of what
// happends in the global scope, the registry will contain the objects it
// needs.
require_once("Zend/Registry.php");
$registry = Zend_Registry::getInstance();
$registry->configuration = $configuration;
////$registry->dbAdapter     = $dbAdapter;

// CLEANUP - remove items from global scope
// This will clear all our local boostrap variables from the global scope of
// this script (and any scripts that called bootstrap).  This will enforce
// object retrieval through the Applications's Registry
unset($frontController, $view, $configuration, $dbAdapter, $registry);
?>

