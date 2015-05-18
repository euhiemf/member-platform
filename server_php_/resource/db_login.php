<?php

	/**
	* 
	*/
	class db_login
	{
		
		private function studentExists($email) {
			if ($this->db->getStudentID($email) !== false) {
				return true;
			} else {
				return false;
			}
		}

		private function notModifyingSelf($email) {
			return $email != $this->db->privileges->identity;
		}

		public function authorized($email) {

			if ($this->studentExists($email) && $this->notModifyingSelf($email) && (count(array_intersect($this->db->privileges->admin_types, array("mass_registrator", "super_admin", "admin"))) <= 0)) {
				echo "You do not have the required privileges to create a new user!";
				return false;
			}

			return true;

		}
	    private function checkLoginRegistrationData($data) {
	         // validating the input
	        if (!empty($data['user_password_new'])
	            && !empty($data['user_password_repeat'])
	            && ($data['user_password_new'] === $data['user_password_repeat'])
	        ) {
	            // only this case return true, only this case is valid
	            return true;
	        } elseif (empty($data['user_password_new']) || empty($data['user_password_repeat'])) {
	            echo "Empty Password";
	        } elseif ($data['user_password_new'] !== $data['user_password_repeat']) {
	            echo "Password and password repeat are not the same";
	        } elseif (strlen($data['user_password_new']) < 6) {
	            echo "Password has a minimum length of 6 characters";
	        } else {
	            echo "An unknown error occurred.";
	        }

	        // default return
	        return false;
	    }

		public function CREATE($email, $data) {

		    if (version_compare(PHP_VERSION, '5.3.7', '<')) {
		        echo "Sorry, Simple PHP Login does not run on a PHP version older than 5.3.7 !";
		    } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
		        require_once("libraries/password_compatibility_library.php");
		    }

			$data = get_object_vars($data);
			if ( $this->checkLoginRegistrationData($data) ) {
				$pwd = $data['user_password_new'];

		        // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 char hash string.
		        // the constant PASSWORD_DEFAULT comes from PHP 5.5 or the password_compatibility_library
		        $user_password_hash = password_hash($pwd, PASSWORD_DEFAULT);


		        $id = $this->db->getStudentID($email);

		        $sql = 'INSERT INTO student_logins (student_id, user_password_hash)
		                VALUES(:student_id, :user_password_hash)';

		        $query = $this->db_connection->prepare($sql);

		        $query->bindValue(':student_id', $id);
		        $query->bindValue(':user_password_hash', $user_password_hash);
		        // PDO's execute() gives back TRUE when successful, FALSE when not
		        // @link http://stackoverflow.com/q/1661863/1114320
		        $registration_success_state = $query->execute();

		        if ($registration_success_state) {
		            echo "Your account has been created successfully. You can now log in. $email";
		            return true;
		        } else {
		            echo "Sorry, your registration failed. Please go back and try again.";
		        }


			}
		}
		public function READ($email) {
			# code...
			echo "you cant read the password of a user :)"
		}
		public function UPDATE($email, $data) {
			# code...
		}
		public function DELETE($email) {
			# code...
		}
	}

?>