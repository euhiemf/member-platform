<?php


	/*

		For every request, username and password must be passed


		CRUD:
			user:
				email:
					credentials
					card
			card:
				inactivate

		database:
			install
			uninstall
			reinstall


		GET /database/install
			install the database

		GET /database/uninstall
			remove the database file

		GET /database/reinstall
			remove the database file and install the database

		GET /user/noobtoothfairy@gmail.com/
			show user info

		GET /user/noobtoothfairy@gmail.com/credentials
			show user credentials

		GET /user/noobtoothfairy@gmail.com/card
			show user card info and code

		POST /user/noobtoothfairy@gmail.com/
			create a new user

		POST /user/noobtoothfairy@gmail.com/credentials
			if the user of email does not exist, then create it
			add credentials to the user of email

		POST /user/noobtoothfairy@gmail.com/card
			if the user of email does not exist, then create it
			add card code to the user of email

		PUT /user/noobtoothfairy@gmail.com/
			update the users email address

		PUT /user/noobtoothfairy@gmail.com/credentials
			update the credentials to the user of email

		PUT /user/noobtoothfairy@gmail.com/card
			update card to the user of email


		DELETE /user/noobtoothfairy@gmail.com/
			delete the user and its credentials + card

		DELETE /user/noobtoothfairy@gmail.com/credentials
			DELETE all credential information about the user to the email

		DELETE /user/noobtoothfairy@gmail.com/card
			delete the card from the databaes


	

	*/


	/**
	* CRUD handler
	* takes ?target=x&path=y from method, GET, POST, PUT, DELETE
	*/
	class CRUD
	{


		private $handlers = array();
		public $params;

		function __construct()
		{
			$this->target = $_GET["target"];
			$this->path = $_GET["path"];
			$headers = getallheaders();
			if (isset($headers['X-HTTP-Method-Override'])) {
				$this->method = $headers['X-HTTP-Method-Override'];
			} elseif (isset($headers['X-Http-Method-Override'])) {
				// på binero blir det små bokstäver i X-Http-Method-Override?!
				$this->method = $headers['X-Http-Method-Override'];
			}
			else {
				$this->method = $_SERVER['REQUEST_METHOD'];
			}

			switch ($this->method) {
				case 'POST':
					$this->action = 'CREATE';
					break;
				case 'GET':
					$this->action = 'READ';
					break;
				case 'PUT':
					$this->action = 'UPDATE';
					break;
				case 'DELETE':
					$this->action = 'DELETE';
					break;
				default:
					$this->action = 'READ';
					break;
			}

			$this->path_parts = explode('/', $this->path);

			$this->parseRequest();

		}
		private function setParamsRaw() {

			$raw  = '';
			$httpContent = fopen('php://input', 'r');
			while ($kb = fread($httpContent, 1024)) {
				$raw .= $kb;
			}
			$params = json_decode(stripslashes($raw));
			$this->params = json_decode(stripslashes($raw));


		}
		protected function parseRequest() {
			if ($this->method == 'PUT') {

				$this->setParamsRaw();

			} else {

				$headers = array_merge(getallheaders(), $_REQUEST);

				if (isset($headers['payload'])) {
					$this->params =  json_decode(stripslashes($headers['payload']));
				} else {
					$this->setParamsRaw();
				}

			}
		}

		public function handle($target, $callback) {
			$this->handlers[$target] = $callback;
		}

		public function listen() {

			if (in_array($this->target, $this->handlers)) {
				call_user_func_array($this->handlers[$this->target], array_merge([$this->action], $this->path_parts));
			}

		}

		public function getDataAsArray() {

			$return = array();

			for ($i = 0; $i < func_num_args(); $i++) {
				$param = func_get_arg($i);
				array_push($return, isset($this->params->$param) ? $this->params->$param : '');
			}

			return $return;

		}
	}



?>