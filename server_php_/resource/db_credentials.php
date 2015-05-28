<?php

	require_once 'FormValidator.php';



	/**
	* 
	*/
	class db_credentials
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


		private function validateData($data) {

			$data = get_object_vars($data);

			$validations = array(
				'first_name' => 'not_empty',
				'last_name' => 'not_empty',
				'nin' => 'number',
				'sex' => 'not_empty',
				'co_address' => 'whateva',
				'street_address' => 'not_empty',
				'postal_number' => 'not_empty',
				'post_town' => 'not_empty',
				'mobile_number' => 'not_empty',
				'sd_mobile_number' => 'whateva',
				'class' => 'not_empty'
			);

			function filter($key) {
				return isset($validations[$key]);
			}

			foreach ($data as $key => $value) {
				if (!isset($validations[$key])) {
					unset($data[$key]);
				}
			}


			$sanatize_and_required = array_keys($validations);

			$validator = new FormValidator($validations, $sanatize_and_required, $sanatize_and_required);

			if ($validator->validate($data)) {

				$data = $validator->sanatize($data);
				return $data;

			} else {
				die($validator->getJSON());
			}
		}

		private function message($text) {
			return "{\"message\": \"$text\"}";
		}
		private function error($text) {
			return "{\"error\": \"$text\"}";
		}
		
		public function CREATE($email, $data) {
			$data = $this->validateData($data);
			$this->db->setStudentCredentials($email, $data);
			$this->READ($email);
		}
		public function READ($email) {
			// $data = $this->validateData($data);
			$this->db->readStudentCredentials($email);
		}
		public function UPDATE($email, $data) {
			$data = $this->validateData($data);
			$this->db->updateStudentCredentials($email, $data);
			$this->READ($email);
		}
		public function DELETE($email) {
			// $data = $this->validateData($data);
			$this->db->deleteStudentCredentials($email);
			echo $this->message("deleted credentials of $email");
		}
	}


?>