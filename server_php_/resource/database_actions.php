<?php

	require_once('database_installation.php');
	require_once('password_compatibility_library.php');

	class DatabaseActions extends DatabaseInstallation
	{

		public function establishConnection() {
			try {
				$this->db_connection = new PDO('sqlite' . ':' . $this->db_path);
				return true;
			} catch (PDOException $e) {
				echo "PDO database connection problem: " . $e->getMessage();
			} catch (Exception $e) {
				echo "General problem: " . $e->getMessage();
			}
			return false;
		}

		public function addPWDusers() {

			$this->predefined_logins = json_decode(file_get_contents("pwd.json"));

			foreach ($this->predefined_logins as $identity => $properties) {

				$id = $this->createStudent($identity);

				$this->createLogin($id, $properties->secret);

				// echo print_r($this->predefined_logins);

				if (isset($properties->groups)) {
					for ($i = 0; $i < count($properties->groups); $i++) {
						$group_id = $this->createGroup($properties->groups[$i]);
						$this->bindUserToGroup($id, $group_id);
					}
				}

			}

		}

		public function getStudentID($email) {

			$sql = 'SELECT student_id, user_email FROM students WHERE user_email = :user_email LIMIT 1';

			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':user_email', $email);
			$query->execute();
			$result_row = $query->fetchObject();
			if ($result_row) {
				return $result_row->student_id;
			} else {
				return false;
			}
		}

		public function createStudent($email) {
			$sql = 'SELECT student_id, user_email FROM students WHERE user_email = :user_email LIMIT 1';

			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':user_email', $email);
			$query->execute();

			// As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
			// If you meet the inventor of PDO, punch him. Seriously.
			$result_row = $query->fetchObject();
			if ($result_row) {
				return $result_row->student_id;
			} else {

				$sql = 'INSERT INTO students (student_id, user_email) VALUES(NULL, :user_email)';
				$query = $this->db_connection->prepare($sql);
				$query->bindValue(':user_email', $email);
				$query->execute();
				$id = $this->db_connection->lastInsertId("student_id");

				return $id;
			}
		}

		public function updateStudentEmail($email, $new_email) {
			$sql = 'UPDATE students set user_email = :new_email WHERE user_email = :user_email';

			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':user_email', $email);
			$query->bindValue(':new_email', $new_email);
			$query->execute();
		}

		public function deleteStudent($email) {

			$sql = 'DELETE FROM students WHERE user_email = :user_email';

			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':user_email', $email);
			$query->execute();

			// ALSO DELETE CARD AND CREDENTIALS!!!!!!!!!!!!!!!!!!!

		}

		public function setStudentCredentials($email, $data) {
			$sql = 'INSERT INTO student_credentials (student_id, first_name, last_name, nin, member_charge, start_date, end_date, sex, co_address, street_address, postal_number, post_town, mobile_number, sd_mobile_number, class ) VALUES(:student_id, :first_name, :last_name, :nin, \'\', :start_date, \'\', :sex, :co_address, :street_address, :postal_number, :post_town, :mobile_number, :sd_mobile_number, :class )';
	        $query = $this->db_connection->prepare($sql);

	        $id = $this->getStudentID($email);

	        $query->bindValue(':student_id', $id);

			$query->bindValue(':first_name', $data['first_name']);
			$query->bindValue(':last_name', $data['last_name']);
			$query->bindValue(':nin', $data['nin']);
			$query->bindValue(':start_date', time());
			$query->bindValue(':sex', $data['sex']);
			$query->bindValue(':co_address', $data['co_address']);
			$query->bindValue(':street_address', $data['street_address']);
			$query->bindValue(':postal_number', $data['postal_number']);
			$query->bindValue(':post_town', $data['post_town']);
			$query->bindValue(':mobile_number', $data['mobile_number']);
			$query->bindValue(':sd_mobile_number', $data['sd_mobile_number']);
			$query->bindValue(':class', $data['class']);
			$query->execute();
		}

		public function readStudentCredentials($email) {

			$id = $this->getStudentID($email);

			$sql = 'SELECT * FROM student_credentials WHERE student_id = :id LIMIT 1';
			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':id', $id);
			$query->execute();
			$result_row = $query->fetchObject();

			if ($result_row) {
				echo json_encode($result_row);
			} else {
				echo "failed to fetch data from database";
			}
		}

		public function updateStudentCredentials($email, $data) {


			$sets = " ";

			foreach ($data as $key => $value) {
				$sets .= "$key=:$key, ";
			}
			$sets = rtrim($sets, ", ");

			$sql = 'UPDATE student_credentials SET' . $sets . ' WHERE student_id = :student_id';
			// die($sql);
	        $query = $this->db_connection->prepare($sql);

	        $id = $this->getStudentID($email);
	        $query->bindValue(':student_id', $id);

			foreach ($data as $key => $value) {
				$sets += "$key=:$value, ";
				$query->bindValue(":$key", $data[$key]);
			}

			$query->execute();
		}

		public function deleteStudentCredentials($email) {

			$id = $this->getStudentID($email);

			$sql = 'DELETE FROM student_credentials WHERE student_id = :student_id';
			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':student_id', $id);
			$query->execute();

		}

		public function bindCard($email, $card_number) {
	    	$id = $this->getStudentID($email);
	    	$datetime = time();

	    	$sql = "INSERT INTO cards (sql_index, card_id, issued, active, cetera) VALUES(NULL, :card_id, :datetime, 'yes', '')";
	    	$query = $this->db_connection->prepare($sql);
	        $query->bindValue(':card_id', $card_number);
	        $query->bindValue(':datetime', $datetime);
			$query->execute();

	    	$sql = "INSERT INTO student_cards (sql_index, student_id, card_id, cetera) VALUES(NULL, :student_id, :card_id, '')";
	    	$query = $this->db_connection->prepare($sql);
	        $query->bindValue(':student_id', $id);
	        $query->bindValue(':card_id', $card_number);
			$query->execute();
		}
		public function bindImage($email, $image) {
	    	$id = $this->getStudentID($email);
	    	$sql = "INSERT INTO student_images (sql_index, student_id, image, cetera) VALUES(NULL, :student_id, :image, '')";
	    	$query = $this->db_connection->prepare($sql);
	        $query->bindValue(':student_id', $id);
	        $query->bindValue(':image', $image);
			$query->execute();
		}
		public function getImage($email) {
			$sql = 'SELECT image FROM student_images WHERE id = :id LIMIT 1';

	    	$id = $this->getStudentID($email);
			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':id', $id);
			$query->execute();
			if ($result_row) {

				return $result_row->image;

			} else {
				return false;
			}
		}

		public function createGroup($name, $level = 1) {


			$sql = 'SELECT group_id FROM groups WHERE name = :name AND admin_level = :level LIMIT 1';

			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':name', $name);
			$query->bindValue(':level', $level);
			$query->execute();

			$result_row = $query->fetchObject();

			if ($result_row) {

				return $result_row->group_id;

			} else {

				$sql = "INSERT INTO groups (group_id, name, admin_level, cetera) VALUES(NULL, :name, :level, 'etc')";
				$query = $this->db_connection->prepare($sql);
				$query->bindValue(':name', $name);
				$query->bindValue(':level', $level);

				$query->execute();

				$group_id = $this->db_connection->lastInsertId('group_id');

				return $group_id;

			}



		}
		private function bindUserToGroup($student_id, $group_id) {

			$sql = "SELECT group_id FROM student_groups WHERE student_id = :student_id AND group_id = :group_id LIMIT 1";
			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':student_id', $student_id);
			$query->bindValue(':group_id', $group_id);
			$query->execute();
			$result_row = $query->fetchObject();


			if ( ! $result_row ) {

				$sql = "INSERT INTO student_groups (student_id, group_id, cetera) VALUES(:student_id, :group_id, 'etc')";

				$query = $this->db_connection->prepare($sql);

				$query->bindValue(':student_id', $student_id);
				$query->bindValue(':group_id', $group_id);

				$res = $query->execute();

			}

		}
		public function createLogin($id, $user_password) {

			$sql = 'INSERT INTO student_logins (student_id, user_password_hash) VALUES(:student_id, :user_password_hash)';
			$query = $this->db_connection->prepare($sql);


			$user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
			$query->bindValue(':student_id', $id);
			$query->bindValue(':user_password_hash', $user_password_hash);

			$registration_success_state = $query->execute();

		}
		public function createUserAndAddItToGroup($email, $group_name) {
			$student_id = $this->createStudent($email);
			$group_id = $this->createGroup($group_name);
			$this->bindUserToGroup($student_id, $group_id);
		}
		public function isValidLuhn($number) {
			settype($number, 'string');
			$sumTable = array(array(0,1,2,3,4,5,6,7,8,9), array(0,2,4,6,8,1,3,5,7,9));
			$sum = 0;
			$flip = 0;
			for ($i = strlen($number) - 1; $i >= 0; $i--) {
				$sum += $sumTable[$flip++ & 0x1][$number[$i]];
			}

			return $sum % 10 === 0;
		}



	}





?>
