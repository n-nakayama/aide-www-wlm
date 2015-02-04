<?php
/**
 * Identity V3 class
 * a.ide
 */
require_once "apiIdentityAbstract.php";

class apiIdentity extends apiIdentityAbstract {

	/**
	 * constructer
	 * /v3/auth/tokens
	 *  -d '{"auth":{"identity":{"methods":["password"],"password":{"user":{"domain":{"name":"kaikisokenDom01"},"name":"soken00wlm01","password":"kensakensakensa00"}}}}}'
	 */
	public function __construct($userinfo, $fqdn = null) {
		//http://developer.openstack.org/api-ref-identity-v3.html#identity-v3
		$auth = array(
			"auth" => array(
				"identity" => array(
					"methods" => array("password"),
					"password" => array(
						"user" => array(
							"domain" => array(
								"name" => $userinfo->getDomain()
							),
							"name" => $userinfo->id,
							"password" => $userinfo->getPassword()
						)
					)
				)
			),
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
		if (array_key_exists("X-Subject-Token", $this->res->header)) {
			return $this->res->header["X-Subject-Token"];
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
		if ($this->isobject) {
		 	return (empty($this->body->token->project->domain->id) ? false : $this->body->token->project->domain->id);
		}else{
			return $this->body["token"]["project"]["domain"]["id"];
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
		if ($this->isobject) {
		 	return (empty($this->body->token->project->id) ? false : $this->body->token->project->id);
		}else{
			return $this->body["token"]["project"]["id"];
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
		if ($this->isobject) {
		 	return (empty($this->body->token->user->id) ? false : $this->body->token->user->id);
		}else{
			return $this->body["token"]["user"]["id"];
		}
		return false;
	 }

	/**
	 * get endpoint
	 */
	 public function getEndpoint($service) {
		//$svc->endpoints[0]->publicURL;
		if (is_object($service)) {
			$endpoint = $this->_getEndpointPublic($service->endpoints);
			return $endpoint;
		}
		if (is_array($service)) {
			$endpoint = $this->_getEndpointPublic($service["endpoints"], $this->isobject);
			return $endpoint;
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
	 private function _getEndpointPublic($endpoints, $isobject = true) {
	 	if ($isobject) {
		 	foreach ($endpoints as $k => $endpoint) {
		 		if (strtolower($endpoint->interface) == "public") {
		 			return $endpoint->url;
		 		}
		 	}
	 	}else{
		 	foreach ($endpoints as $k => $endpoint) {
		 		if (strtolower($endpoint["interface"]) == "public") {
		 			return $endpoint["url"];
		 		}
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
		if ((empty($type)) && (empty($name))) {
			if (is_object($this->body)) {
				return (empty($this->body->token->catalog) ? false : $this->body->token->catalog);

			}else if (is_array($this->body)) {
				return $this->body["token"]["catalog"];

			}
			return false;
		}

	 	$type = strtolower($type);
	 	$name = strtolower($name);
		if (is_object($this->body)) {
			if (!isset($this->body->token->catalog)) {
				return false;
			}
	 		foreach ($this->body->token->catalog as $k => $service) {
	 			if (strtolower($service->type) == $type) {
	 				return $service;
	 			}
		 	}

		}else if (is_array($this->body)) {
			if (!isset($this->body["token"]["catalog"])) {
				return false;
			}
	 		foreach ($this->body["token"]["catalog"] as $k => $service) {
	 			if (strtolower($service["type"]) == $type) {
	 				return $service;
	 			}
		 	}
		}
		return false;
	 }

}
?>
