<?php


	/**
	* 
	*/
	class db_card
	{
		private function studentExists($email) {
			// The students should not be able to change their card number on their own.
			return false;
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

		private function message($text) {
			return "{\"message\": \"$text\"}";
		}
		private function error($text) {
			return "{\"error\": \"$text\"}";
		}

		public function CREATE($email, $data) {
			$this->db->bindCard($email, $data->card_number);
			echo $this->message("You have registerd your card!");

		}
		public function READ($email) {
			# code...
		}
		public function UPDATE($email, $data) {
			# code...
		}
		public function DELETE($email) {
			# code...
		}
	}


?>