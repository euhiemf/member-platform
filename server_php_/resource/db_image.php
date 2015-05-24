<?php


	/**
	* 
	*/
	class db_image
	{
		private function studentExists($email) {
			// The students should not be able to change their image number on their own.
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

		public function CREATE($email, $data) {
			$this->db->bindImage($email, $data->image);
			echo $this->message("you have now added an image");
		}
		public function READ($email) {
			$url = $this->db->getImage($email);
			echo "<img src='$url'>";
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