<?php
class SysTable {
	private static $_DEFAULT = "/models/010_main/main0010.php";
	
	private static $_LIST = array(
		"010" => "main",
		"020" => "mst",
		"030" => "sir",
		"070" => "sys"
	);

	public static function GetBizServiceFile($sysprocid = "sysprocid") {
		if ($sysprocid == "sysprocid") {
			return self::$_DEFAULT;
		}
		if (strlen($sysprocid) != 7) {
			return self::$_DEFAULT;
		}
		$sysid = substr($sysprocid, 0, 3);
		$procid = substr($sysprocid, 3, 4);
		$syspath = "{$sysid}_" . self::$_LIST[$sysid];
		$procpath = self::$_LIST[$sysid] . $procid;
		$bizpathfile = "/models/{$syspath}/{$procpath}.php";
		return (file_exists($bizpathfile)) ? $bizpathfile : self::$_DEFAULT;
	}
}
?>
