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

		GET /user/noobtoothfairy@gmail.com/image
			show the user avatar

		POST /user/noobtoothfairy@gmail.com/
			create a new user

		POST /user/noobtoothfairy@gmail.com/credentials
			if the user of email does not exist, then create it
			add credentials to the user of email

		POST /user/noobtoothfairy@gmail.com/card
			if the user of email does not exist, then create it
			add card code to the user of email

		POST /user/noobtoothfairy@gmail.com/image
			if the user of email does not exist, then create it
			add image user of email

		PUT /user/noobtoothfairy@gmail.com/
			update the users email address

		PUT /user/noobtoothfairy@gmail.com/credentials
			update the credentials to the user of email

		PUT /user/noobtoothfairy@gmail.com/card
			update card to the user of email

		PUT /user/noobtoothfairy@gmail.com/image
			update card to the user of email


		DELETE /user/noobtoothfairy@gmail.com/
			delete the user and its credentials + card

		DELETE /user/noobtoothfairy@gmail.com/credentials
			DELETE all credential information about the user to the email

		DELETE /user/noobtoothfairy@gmail.com/card
			delete the card from the databaes

		DELETE /user/noobtoothfairy@gmail.com/image
			delete the image from the databaes
	

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
		private function getParamsRaw() {

			$raw  = '';
			$httpContent = fopen('php://input', 'r');
			while ($kb = fread($httpContent, 1024)) {
				$raw .= $kb;
			}
			$raw = stripslashes($raw);

			if ($raw) {
				$params = get_object_vars(json_decode($raw));
			} else {
				$params = array();
			}


			return $params;
		}
		private function arrayToObject($array) {
			if (!is_array($array)) {
				return $array;
			}

			$object = new stdClass();
			if (is_array($array) && count($array) > 0) {
				foreach ($array as $name=>$value) {
					$name = strtolower(trim($name));
					if (!empty($name)) {
						$object->$name = $this->arrayToObject($value);
					}
				}
				return $object;
			}
			else {
				return FALSE;
			}
		}
		protected function parseRequest() {
			$headers = array();


			// if ($this->method == 'PUT') {
			$headers = array_merge($headers, $this->getParamsRaw());
			// }

			$headers = array_merge($headers, getallheaders());
			$headers = array_merge($headers, $_REQUEST);
			$headers = array_merge($headers, $_FILES);

			if (array_key_exists('Auth-Secret', $headers)) {
				$headers['auth_secret'] = $headers['Auth-Secret'];
			}
			if (array_key_exists('Auth-Identity', $headers)) {
				$headers['auth_identity'] = $headers['Auth-Identity'];
			}

			if (array_key_exists('payload', $headers) || array_key_exists('Payload', $headers)) {

				// This code can probably be done nicer
				if ( array_key_exists('payload', $headers) ) {
					$json = json_decode(utf8_encode(stripslashes($headers['payload'])));
				} else {
					$json = json_decode(utf8_encode(stripslashes($headers['Payload'])));
				}

				$pl = get_object_vars($json);
				$headers = array_merge($headers, $pl);
			}

			// die(print_r($this->params));

			$this->params = $this->arrayToObject($headers);



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