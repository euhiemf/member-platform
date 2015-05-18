<?php
	
	require_once('database_actions.php');

	/**
	* 
	*/
	class Database extends DatabaseActions
	{
		
		protected $db_path = 'studentunion.db';


		function __construct() {
			$this->imports = new stdClass();
			$this->installed = file_exists($this->db_path);
		}

		public function import($name, $path) {
			// $this->imports = 
			include_once($path);
			// $vars = get_declared_classes();
			// echo $name;
			// die(print_r($vars));
			$ref = new ReflectionClass($name);
			$class = $ref->newInstanceArgs();
			$class->db = $this;
			$this->imports->$name = $class;
		}

	}


?>