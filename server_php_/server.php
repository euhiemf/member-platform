<?php

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Allow-Headers: accept, content-type, identity, payload, secret, x-http-method-override');
	header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); 
	header('Content-Type: application/json');



	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);



	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
	        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         
	    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
	        header("Access-Control-Allow-Headers:{$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

	    exit(0);
	} 



	require_once('resource/crud.php');
	require_once('resource/authenticator.php');
	require_once('resource/database.php');


	$crud = new CRUD();
	$database = new Database();

	$auth = new Authenticator($database);


	call_user_func_array(array($auth, 'validate'), $crud->getDataAsArray('auth_identity', 'auth_secret'));

	$privileges = $auth->getPrivileges();

	$database->privileges = $privileges;

	function renderPhpToString($__file, $vars=null) {
		if (is_array($vars) && !empty($vars)) {
			extract($vars);
		}
		ob_start();
		include $__file;
		return ob_get_clean();
	}


	function admin() {
		global $privileges, $auth;

		if ( ! in_array('phpliteadmin', $privileges->admin_types) ) {
			return $auth->unauthorize();
		}


		header('Location: phpliteadmin.php');

		// renderPhpToString('phpliteadmin.php', array('password' => $auth->getSecret()));

	}

	function database($crud_action, $action = "") {
		global $privileges, $auth, $database;

		if ( ! in_array('super_admin', $privileges->admin_types) ) {
			return $auth->unauthorize();
		}
		if (in_array($action, array('install', 'reinstall', 'uninstall'))) {
			call_user_func(array($database, $action));
		}
	}
	
	function user($crud_action, $username, $action = "") {
		global $privileges, $auth, $database, $crud;

		if ( ! in_array('student', $privileges->admin_types) ) {
			return $auth->unauthorize();
		}

		switch ($action) {
			case '':
				$import_name = 'db_user';
				break;
			case 'login':
				$import_name = 'db_login';
				break;
			case 'credentials':
				$import_name = 'db_credentials';
				break;
			case 'card':
				$import_name = 'db_card';
				break;
		}

		$database->import($import_name, 'resource/' . $import_name . '.php');

		$database->establishConnection();

		// die("database->imports->$import_name->$crud_action");
		if (method_exists($database->imports->$import_name, 'authorized')) {

			if ( $database->imports->$import_name->authorized($username) ) {

				call_user_func(array($database->imports->$import_name, $crud_action), $username, $crud->params);

			}

		} else {

			call_user_func(array($database->imports->$import_name, $crud_action), $username, $crud->params);

		}

		// echo "$username, $action";

	}

	$crud->handle('database', 'database');
	$crud->handle('user', 'user');
	$crud->handle('admin', 'admin');


	$crud->listen();





?>