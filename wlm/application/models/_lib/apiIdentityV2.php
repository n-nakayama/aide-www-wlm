<?php
/**
 * Identity V2 class
 * a.ide
 */
require_once "apiIdentityAbstract.php";

class apiIdentity extends apiIdentityAbstract {

	/**
	 * constructer
	 */
	public function __construct($userinfo, $fqdn = null) {
		$auth = array(
			"auth" => array(
				"tenantName" => $userinfo->getTenant(),
				"passwordCredentials" => array(
					"username" => $userinfo->id,
					"password" => $userinfo->getPassword()
				)
			)
		);
		parent::__construct($userinfo, $fqdn, $auth);
	}

	/////

	/**
	 * get tokenid
	 */
	 public function getTokenId() {
		if (!$this->islogined) {
			return false;
		}
		if (is_object($this->body)) {
		 	return $this->body->access->token->id;
		}
		if (is_array($this->body)) {
			return $this->body["access"]["token"]["id"];
		}
		return false;
	 }

	/**
	 * get domainid
	 */
	 public function getDomainId() {
		if (!$this->islogined) {
			return false;
		}
		return false;
	 }

	/**
	 * get tenantid
	 */
	 public function getTenantId() {
		if (!$this->islogined) {
			return false;
		}
		if (is_object($this->body)) {
		 	return $this->body->access->token->tenant->id;
		}
		if (is_array($this->body)) {
			return $this->body["access"]["token"]["tenant"]["id"];
		}
		return false;
	 }

	/**
	 * get userid
	 */
	 public function getUserId() {
		if (!$this->islogined) {
			return false;
		}
		if (is_object($this->body)) {
		 	return $this->body->access->user->id;
		}
		if (is_array($this->body)) {
			return $this->body["access"]["user"]["id"];
		}
	 	return false;
	 }

	/**
	 * get endpoint
	 */
	 public function getEndpoint($service) {
		//$svc->endpoints[0]->publicURL;
		if (is_object($service)) {
			return $service->endpoints[0]->publicURL;
		}
		if (is_array($service)) {
			return $service["endpoints"][0]["publicURL"];
		}
		//type
		if (is_string($service)) {
			$svc = $this->getServiceCatalog($service);
			if ($svc !== false) {
				return $this->getEndpoint($svc);
			}
		}
		return false;
	 }

	 /**
	  * get serviceCatalog
	  */
	 public function getServiceCatalog($type = null, $name = null) {
	 	if (!$this->islogined) {
	 		return false;
	 	}
		if (($type == null) && ($name == null)) {
			if (is_object($this->body)) {
				return $this->body->access->serviceCatalog;
			}else if (is_array($this->body)) {
				return $this->body["access"]["serviceCatalog"];
			}
			return false;
		}
	 	$type = strtolower($type);
	 	$name = strtolower($name);
		if (is_object($this->body)) {
	 		foreach ($this->body->access->serviceCatalog as $k => $service) {
	 			if (strtolower($service->type) == $type) {
	 				return $service;
	 			}
	 			if ($service->name == $name) {
	 				return $service;
		 		}
		 	}

		}else if (is_array($this->body)) {
	 		foreach ($this->body["access"]["serviceCatalog"] as $k => $service) {
	 			if (strtolower($service["type"]) == $type) {
	 				return $service;
	 			}
	 			if ($service["name"] == $name) {
	 				return $service;
		 		}
		 	}
		}
		return false;
	 }
}
?>
