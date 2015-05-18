<?php


	/**
	* 
	*/
	class DatabaseInstallation
	{
		

		protected function createDatabase() {

			// config
			$db_type = 'sqlite';

			$db_sqlite_path = $this->db_path;

			$db_connection = new PDO($db_type . ':' . $db_sqlite_path);


			$queries = array(
				'CREATE TABLE IF NOT EXISTS `students` (
				`student_id` INTEGER PRIMARY KEY,
				`user_email` varchar(64));
				CREATE UNIQUE INDEX `email_UNIQUE` ON `students` (`user_email` ASC);',

				'CREATE TABLE IF NOT EXISTS `student_logins` (
				`student_id` INTEGER PRIMARY KEY,
				`user_password_hash` varchar(255));',

				'CREATE TABLE IF NOT EXISTS `student_credentials` (
				`student_id` INTEGER PRIMARY KEY,
				`first_name` varchar(64),
				`last_name` varchar(64),
				`nin` varchar(64),
				`member_charge` varchar(64),
				`start_date` varchar(64),
				`end_date` varchar(64),
				`sex` varchar(64),
				`co_address` varchar(64),
				`street_address` varchar(64),
				`postal_number` varchar(64),
				`post_town` varchar(64),
				`mobile_number` varchar(64),
				`sd_mobile_number` varchar(64),
				`class` varchar(64));',

				'CREATE TABLE IF NOT EXISTS `student_groups` (
				`sql_index` INTEGER PRIMARY KEY,
				`student_id` INTEGER,
				`group_id` INTEGER,
				`cetera` varchar(64));',

				'CREATE TABLE IF NOT EXISTS `groups` (
				`group_id` INTEGER PRIMARY KEY,
				`name` varchar(64),
				`admin_level` varchar(64),
				`cetera` varchar(64));',

				'CREATE TABLE IF NOT EXISTS `events` (
				`event_id` INTEGER PRIMARY KEY, -- same as group_id in groups
				`datetime` varchar(64),
				`name` varchar(64),
				`price` varchar(64),
				`payed` varchar(64),
				`location` varchar(64),
				`cetera` varchar(64));',

				'CREATE TABLE IF NOT EXISTS `societies` (
				`society_id` INTEGER PRIMARY KEY, -- same as group_id in groups
				`name` varchar(64),
				`cetera` varchar(64));',

				'CREATE TABLE IF NOT EXISTS `student_cards` (
				`sql_index` INTEGER PRIMARY KEY,
				`student_id` INTEGER,
				`card_id` INTEGER,
				`cetera` varchar(64));',

				'CREATE TABLE IF NOT EXISTS `cards` (
				`sql_index` INTEGER PRIMARY KEY,
				`card_id` INTEGER,
				`issued` varchar(64),
				`active` varchar(64),
				`cetera` varchar(64));',

			);

				
			for ($i=0; $i < count($queries); $i++) {

				$sql = $queries[$i];

				$query = $db_connection->prepare($sql);
				$query->execute();

			}

			if (file_exists($db_sqlite_path)) {
				echo "Database $db_sqlite_path was created, installation was successful.";
			} else {
				echo "Database $db_sqlite_path was not created, installation was NOT successful. Missing folder write rights ?";
			}

			// $this->createNewUser(true, 'admin', "\$2y\$10\$xE/ztEVBrDu.vhG47BWLnOefTFY/5V29nO3yE1XJGpF..8g9Zwz82", 2);
		}

		public function uninstall() {
			if ($this->installed) {
				unlink($this->db_path);
				$this->installed = false;
			}
		}

		public function install() {
			if ( ! $this->installed ) {
				$this->createDatabase();
				$this->establishConnection();
				$this->addPWDusers();
			}
		}

		public function reinstall() {
			$this->uninstall();
			$this->install();
		}
		
	}


?>