<?php

	/**
	*
	*/
	class Authtests
	{

		protected function pwdJSONAuth() {

			$identity = $this->identity;
			$secret = $this->secret;


			// die("$identity, $secret");

			if (isset($this->predefined_logins->$identity)) {
				if ($this->predefined_logins->$identity->secret == $secret) {
					return true;
				}
			}

			return false;
		}

	}


	/**
	*
	*/
	class Authenticator extends Authtests
	{

		protected $predefined_logins = "";
		protected $groups = array();

		function __construct($db) {
			$this->database = $db; 
			$this->predefined_logins = json_decode(file_get_contents("pwd.json"));
		}

		public function unauthorize() {
			header('HTTP/1.0 401 Unauthorized');
			die('Invalid authorization credentials or nonauthorized request.');
		}

		// private function

		public function getPrivileges() {
			# code...
			$holder = new stdClass();
			$holder->admin_types = $this->groups;
			$holder->identity = $this->getIdentity();
			$holder->secret = $this->getSecret();

			return $holder;
		}

		private function pwdJSONGroups() {
			$identity = $this->identity;
			$this->groups = $this->predefined_logins->$identity->groups;
		}

		public function getIdentity() {
			return $this->identity;
		}
		public function getSecret() {
			return $this->secret;
		}

		private function databaseAuth() {
			//  TODODODODDODODODOOOOOOOOOOOOOOOOOOOOOOOOOOO
			// users should be able to change their own settings
		}

		public function validate($identity, $secret) {
			$this->identity = $identity;
			$this->secret = $secret;

			if ($this->pwdJSONAuth()) {
				$this->pwdJSONGroups();
			} elseif ($this->databaseAuth()) {

			} else {
				die('{"error": "You are not authorized"}');
			}

		}
	}


?>
