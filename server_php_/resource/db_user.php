<?php

	
	require_once 'FormValidator.php';

	/**
	* 
	*/
	class db_user
	{
		
		function __construct()
		{
			// $this->hello = 123123;
			# code...


		}

		private function notModifyingSelf($email) {
			return $email != $this->db->privileges->identity;
		}

		public function authorized($email) {

			if ($this->notModifyingSelf($email) && (count(array_intersect($this->db->privileges->admin_types, array("mass_registrator", "super_admin", "admin"))) <= 0)) {
				echo "You do not have the required privileges to create a new user!";
				return false;
			}

			return true;

		}

		private function message($text) {
			return "{\"message\": \"$text\"}";
		}


		public function CREATE($email)
		{

			// Who can create users?
			// mass_registrator, super_admin, admin

			$validator = new FormValidator();

			if ($validator->validateItem($email, 'email')) {

				$email = $validator->sanatizeItem($email, 'email');

				$this->db->createUserAndAddItToGroup($email, 'student');

				echo $this->message("Added user student of email $email");


			} else {

				die($validator->getJSON());

			}

			// $this->db->

		}

		public function READ($email) {

			$validator = new FormValidator();

			if ($validator->validateItem($email, 'email')) {

				$email = $validator->sanatizeItem($email, 'email');

				$id = $this->db->getStudentID($email);
				if ($id == false) {
					echo $this->message("Couln't find the user of $email");
				} else {
					echo "{\"id\": \"$id\", \"email\": \"$email\", \"group\": \"student\"}";
				}


			} else {

				die($validator->getJSON());

			}
		}

		private function checkIfCanUpdateEmail($old_email, $new_email) {

			$exist_id = $this->db->getStudentID($old_email);
			$id = $this->db->getStudentID($new_email);

			if ($exist_id == false) {
				echo $this->message("$old_email does not exist!");
				return false;
			}

			if ($id != false) {
				echo $this->message("$new_email email is already taken");
				return false;
			}


			return true;
		}

		public function UPDATE($email, $data) {


			$validations = array('old_email' => 'email', 'new_email' => 'email');

			$sanatize_and_required = array('old_email', 'new_email');

			$validator = new FormValidator($validations, $sanatize_and_required, $sanatize_and_required);

			$form = array('old_email' => $email, 'new_email' => $data->new_email);

			if ($validator->validate($form)) {

				$form = $validator->sanatize($form);
				$new_email = $form['new_email'];
				$old_email = $form['old_email'];


				if ($this->checkIfCanUpdateEmail($old_email, $new_email)) {
					$this->db->updateStudentEmail($old_email, $new_email);
					$this->READ($new_email);
				}


			} else {

				die($validator->getJSON());

			}
		}

		public function DELETE($email) {
			$validator = new FormValidator();

			if ($validator->validateItem($email, 'email')) {

				$email = $validator->sanatizeItem($email, 'email');

				$id = $this->db->getStudentID($email);
				if ($id == false) {
					echo "Couln't find the user of $email";
				} else {
					$this->db->deleteStudent($email);
					echo "Deleted user!";
				}


			} else {

				die($validator->getJSON());

			}
		}

	}


?>