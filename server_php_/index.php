<?php
header('Access-Control-Allow-Origin: *');

/*

	types of registration:
	'credential'
	'pass'
	'card-number'

	the admin level name is 'student_union_platform' ctrl-f to change

	admin level 0 registration means, you can't register
	admin level 1 -||- 				, you can add accounts of admin level 0
	admin level 2 -||- 				, you can add accounts of admin level 0 and admin level 1

	only one account of admin level 2
	normal members have admin level 0
	admin level 2 account add admin level 1 accounts that will be used at registration etc.




*/


/**
 * Class OneFileLoginApplication
 *
 * An entire php application with user registration, login and logout in one file.
 * Uses very modern password hashing via the PHP 5.5 password hashing functions.
 * This project includes a compatibility file to make these functions available in PHP 5.3.7+ and PHP 5.4+.
 *
 * @author Panique
 * @link https://github.com/panique/php-login-one-file/
 * @license http://opensource.org/licenses/MIT MIT License
 */
class OneFileLoginApplication
{
    /**
     * @var string Type of used database (currently only SQLite, but feel free to expand this with mysql etc)
     */
    private $db_type = "sqlite"; //

    /**
     * @var string Path of the database file (create this with _install.php)
     */
    private $db_sqlite_path = "studentunion.db";

    /**
     * @var object Database connection
     */
    private $db_connection = null;

    /**
     * @var bool Login status of user
     */
    private $user_is_logged_in = false;

    /**
     * @var string System messages, likes errors, notices, etc.
     */
    public $feedback = "";


    /**
     * Does necessary checks for PHP version and PHP password compatibility library and runs the application
     */
    public function __construct()
    {
    }

    public function start()
    {
        if ($this->performMinimumRequirementsCheck()) {
            $this->runApplication();
        }
    }

    public function install()
    {
    	error_reporting(E_ALL);

		// config
		$db_type = $this->db_type;
		$db_sqlite_path = $this->db_sqlite_path;

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
			`student_id` INTEGER PRIMARY KEY,
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

		// check for success
		if (file_exists($db_sqlite_path)) {
			echo "Database $db_sqlite_path was created, installation was successful.";
		} else {
			echo "Database $db_sqlite_path was not created, installation was NOT successful. Missing folder write rights ?";
		}

		$this->createDatabaseConnection();

		$this->createNewUser(true, 'admin', "\$2y\$10\$xE/ztEVBrDu.vhG47BWLnOefTFY/5V29nO3yE1XJGpF..8g9Zwz82", 2);

    }

    /**
     * Performs a check for minimum requirements to run this application.
     * Does not run the further application when PHP version is lower than 5.3.7
     * Does include the PHP password compatibility library when PHP version lower than 5.5.0
     * (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
     * @return bool Success status of minimum requirements check, default is false
     */
    private function performMinimumRequirementsCheck()
    {
        if (version_compare(PHP_VERSION, '5.3.7', '<')) {
            echo "Sorry, Simple PHP Login does not run on a PHP version older than 5.3.7 !";
        } elseif (version_compare(PHP_VERSION, '5.5.0', '<')) {
            require_once("libraries/password_compatibility_library.php");
            return true;
        } elseif (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            return true;
        }
        // default return
        return false;
    }

    /**
     * This is basically the controller that handles the entire flow of the application.
     */
    public function runApplication()
    {
        // check is user wants to see register page (etc.)
        // start the session, always needed!
        // $this->doStartSession();
        // check for possible user interactions (login with session/post data or logout)
        
        // $this->performUserLoginAction();

        $this->doLoginWithPostData();

        if ($this->getUserLoginStatus()) {

	        if (isset($_GET["action"]) && $_GET["action"] == "register") {
	            $this->doRegistration();
	        } elseif (isset($_GET["action"]) && $_GET["action"] == "get_autofill") {
	            $this->dummyGetAutofill();
	        }

            $this->showPageLoggedIn();

        } else {
            $this->showPageLoginForm();
        }

    }

    /**
     * Creates a PDO database connection (in this case to a SQLite flat-file database)
     * @return bool Database creation success status, false by default
     */
    private function createDatabaseConnection()
    {
        try {
            $this->db_connection = new PDO($this->db_type . ':' . $this->db_sqlite_path);
            return true;
        } catch (PDOException $e) {
            $this->feedback = "PDO database connection problem: " . $e->getMessage();
        } catch (Exception $e) {
            $this->feedback = "General problem: " . $e->getMessage();
        }
        return false;
    }

    /**
     * Handles the flow of the login/logout process. According to the circumstances, a logout, a login with session
     * data or a login with post data will be performed
     */
    private function performUserLoginAction()
    {
        if (isset($_GET["action"]) && $_GET["action"] == "logout") {
            $this->doLogout();
        } elseif (!empty($_SESSION['user_email']) && ($_SESSION['user_is_logged_in'])) {
            $this->doLoginWithSessionData();
        } elseif (isset($_POST["login"])) {
            $this->doLoginWithPostData();
        }
    }

    /**
     * Simply starts the session.
     * It's cleaner to put this into a method than writing it directly into runApplication()
     */
    private function doStartSession()
    {
        session_start();
    }

    /**
     * Set a marker (NOTE: is this method necessary ?)
     */
    private function doLoginWithSessionData()
    {
        $this->user_is_logged_in = true; // ?
    }

    /**
     * Process flow of login with POST data
     */
    private function doLoginWithPostData()
    {
        if ($this->checkLoginFormDataNotEmpty()) {
            if ($this->createDatabaseConnection()) {
                $this->checkPasswordCorrectnessAndLogin();
            }
        }
    }

    /**
     * Logs the user out
     */
    private function doLogout()
    {
        $_SESSION = array();
        session_destroy();
        $this->user_is_logged_in = false;
        $this->feedback = "You were just logged out.";
    }

    /**
     * The registration flow
     * @return bool
     */
    private function doRegistration()
    {
        if ($this->checkRegistrationData()) {
            if ($this->createDatabaseConnection()) {
                if ($this->checkRegistrationDataValidOfDb()) {
    				if ($_GET['type'] === 'student_logins') {
                    	$this->createNewUser();
    				} elseif ($_GET['type'] === 'student_credentials') {
    					$this->addStudentCredentials();
    				} elseif ($_GET['type'] === 'student_cards') {
    					$this->addCardNumber();
    				}
                }
            }
        }
        // default return
        return false;
    }

    private function dummyGetAutofill() {
    	header('Content-Type: application/json');


		error_reporting(0);
		$p = $_POST['p'];

		// if ($p != 'reg2015') {
		// 	header('HTTP/1.0 401 Unauthorized');
		// 	die();
		// }


		$connect_error = "An error ocurred while connecting to the database, please try again later or contact support";

		$link = mysql_connect('kill-182694.mysql.binero.se', '182694_hn40374', 'trasusP4') or die($connect_error);
		mysql_select_db('182694-kill') or die($connect_error);
		mysql_set_charset("utf-8");

		$q = $_GET['q'];

		$str = mysql_real_escape_string("SELECT * FROM `members-old` WHERE Personnr=$q");
		$query = mysql_query($str);

		if (mysql_num_rows($query) == 0) {
			header("HTTP/1.0 404 Not Found");
			mysql_close($link);
			die();
		}


		while ($row = mysql_fetch_assoc($query)) {
			echo json_encode($row);
		}


		mysql_close($link);

		die();
    }

    private function addStudentCredentials() {
    	$id = $this->getIdFromEmail($_POST['user_email']);

        $this->addUserToAdminGroup($id);

		$sql = 'INSERT INTO student_credentials (
				student_id,
				first_name,
				last_name,
				nin,
				member_charge,
				start_date,
				end_date,
				sex,
				co_address,
				street_address,
				postal_number,
				post_town,
				mobile_number,
				sd_mobile_number,
				class
			) VALUES(
				:student_id,
				:first_name,
				:last_name,
				:nin,
				\'\',
				:start_date,
				\'\',
				:sex,
				:co_address,
				:street_address,
				:postal_number,
				:post_town,
				:mobile_number,
				:sd_mobile_number,
				:class
			)
		';

        $query = $this->db_connection->prepare($sql);

        $query->bindValue(':student_id', $id);

        $query->bindValue(':first_name', $_POST['first_name']);
        $query->bindValue(':last_name', $_POST['last_name']);
        $query->bindValue(':nin', $_POST['nin']);
        $query->bindValue(':start_date', time());
        $query->bindValue(':sex', $_POST['sex']);
        $query->bindValue(':co_address', $_POST['co_address']);
        $query->bindValue(':street_address', $_POST['street_address']);
        $query->bindValue(':postal_number', $_POST['postal_number']);
        $query->bindValue(':post_town', $_POST['post_town']);
        $query->bindValue(':mobile_number', $_POST['mobile_number']);
        $query->bindValue(':sd_mobile_number', $_POST['sd_mobile_number']);
        $query->bindValue(':class', $_POST['class']);
		$query->execute();
    }

    private function addCardNumber() {
    	$id = $this->getIdFromEmail($_POST['user_email']);
    	$card_number = $_POST['card_number'];
    	$datetime = time();

        $this->addUserToAdminGroup($id);

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

    /**
     * Validates the login form data, checks if username and password are provided
     * @return bool Login form data check success state
     */
    private function checkLoginFormDataNotEmpty()
    {
        if (!empty($_POST['user_email']) && !empty($_POST['user_password'])) {
            return true;
        } elseif (empty($_POST['user_email'])) {
            $this->feedback = "Email field was empty.";
        } elseif (empty($_POST['user_password'])) {
            $this->feedback = "Password field was empty.";
        }
        // default return
        return false;
    }
    
    private function getIdFromEmail($email)
    {
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
    /**
     * Checks if user exits, if so: check if provided password matches the one in the database
     * @return bool User login success status
     */
    private function checkPasswordCorrectnessAndLogin()
    {
        // remember: the user can log in with username or email address

        $email = $_POST['user_email'];

        $student_id = $this->getIdFromEmail($email);

        // if ( ! $student_id ) {
        // 	$this->feedback = "This user does not exist.";
        // 	return false;
        // }

        $sql = 'SELECT student_id, user_password_hash
                FROM student_logins
                WHERE student_id = :student_id
                LIMIT 1';


        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':student_id', $student_id);
        $query->execute();

        // Btw that's the weird way to get num_rows in PDO with SQLite:
        // if (count($query->fetchAll(PDO::FETCH_NUM)) == 1) {
        // Holy! But that's how it is. $result->numRows() works with SQLite pure, but not with SQLite PDO.
        // This is so crappy, but that's how PDO works.
        // As there is no numRows() in SQLite/PDO (!!) we have to do it this way:
        // If you meet the inventor of PDO, punch him. Seriously.
        $result_row = $query->fetchObject();
        if ($result_row) {
            // using PHP 5.5's password_verify() function to check password
            if (password_verify($_POST['user_password'], $result_row->user_password_hash)) {
                // write user data into PHP SESSION [a file on your server]
                $_SESSION['user_email'] = $email;
                $_SESSION['user_is_logged_in'] = true;
                $this->user_is_logged_in = true;
                return true;
            } else {
                $this->feedback = "Wrong password.";
            }
        } else {
            $this->feedback = "This user does not exist.";
        }
        // default return
        return false;
    }

    private function checkLoginRegistrationData()
    {
         // validating the input
        if (!empty($_POST['user_password_new'])
            && !empty($_POST['user_password_repeat'])
            && ($_POST['user_password_new'] === $_POST['user_password_repeat'])
        ) {
            // only this case return true, only this case is valid
            return true;
        } elseif (empty($_POST['user_password_new']) || empty($_POST['user_password_repeat'])) {
            $this->feedback = "Empty Password";
        } elseif ($_POST['user_password_new'] !== $_POST['user_password_repeat']) {
            $this->feedback = "Password and password repeat are not the same";
        } elseif (strlen($_POST['user_password_new']) < 6) {
            $this->feedback = "Password has a minimum length of 6 characters";
        } else {
            $this->feedback = "An unknown error occurred.";
        }

        // default return
        return false;
    }
    private function checkAdminLevelData()
    {
        if (!empty($_POST['user_admin_level']) or $_POST['user_admin_level'] === '0') {
            if (intval($_POST['user_admin_level']) < $this->getCurrentUserAdminLevel()) {
                return true;
            } else {
                $this->feedback = "You need higher admin level to register this type of account!";
            }
        } else {
            $this->feedback = "You need to specify an admin level";
        }
        return false;
    }
    private function checkCredentialRegistrationData()
    {
    	return    !empty($_POST['first_name'])
			   && strlen($_POST['first_name']) <= 64
			   && !empty($_POST['last_name'])
			   && strlen($_POST['last_name']) <= 64
			   && !empty($_POST['nin'])
			   && strlen($_POST['nin']) <= 64
			   && !empty($_POST['sex'])
			   && strlen($_POST['sex']) <= 64
			   // && !empty($_POST['co_address']) // can be empty
			   && strlen($_POST['co_address']) <= 64
			   && !empty($_POST['street_address'])
			   && strlen($_POST['street_address']) <= 64
			   && !empty($_POST['postal_number'])
			   && strlen($_POST['postal_number']) <= 64
			   && !empty($_POST['post_town'])
			   && strlen($_POST['post_town']) <= 64
			   && !empty($_POST['mobile_number'])
			   && strlen($_POST['mobile_number']) <= 64
			   && !empty($_POST['sd_mobile_number'])
			   && strlen($_POST['sd_mobile_number']) <= 64
			   && !empty($_POST['class'])
			   && strlen($_POST['class']) <= 64;
	}
	private function isValidLuhn($number)
	{
		settype($number, 'string');
		$sumTable = array(
			array(0,1,2,3,4,5,6,7,8,9),
			array(0,2,4,6,8,1,3,5,7,9));
		$sum = 0;
		$flip = 0;
		for ($i = strlen($number) - 1; $i >= 0; $i--) {
			$sum += $sumTable[$flip++ & 0x1][$number[$i]];
		}

		return $sum % 10 === 0;
	}
	private function checkStudentCardRegistrationData()
	{
    	return    !empty($_POST['card_number'])
			   && strlen($_POST['card_number']) === 9
			   && $this->isValidLuhn(substr($_POST['card_number'], 4));
	}

	private function checkEmailData()
	{
		if (!empty($_POST['user_email'])
            && !empty($_POST['user_email'])
            && strlen($_POST['user_email']) <= 64
            && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {

        	return true;

        } elseif (empty($_POST['user_email'])) {
            $this->feedback = "Email cannot be empty";
        } elseif (strlen($_POST['user_email']) > 64) {
            $this->feedback = "Email cannot be longer than 64 characters";
        } elseif (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            $this->feedback = "Your email address is not in a valid email format";
        } 	
	}
    private function checkRegistrationDataValidOfDb()
    {

        if (! $this->checkAdminLevelData()) {
            return false;
        }


        return true;
    }
    /**
     * Validates the user's registration input
     * @return bool Success status of user's registration data validation
     */
    private function checkRegistrationData()
    {
        // if no registration form submitted: exit the method
        
        if ( ! $this->checkEmailData() ) {
        	return false;
        }

        if (!isset($_GET['type'])) {
        	return false;
        } elseif (!in_array($_GET['type'], array('student_credentials', 'student_logins', 'student_cards'))) {
       		return false;
        }

        if ($_GET['type'] === 'student_credentials') {
        	return $this->checkCredentialRegistrationData();
        } elseif ($_GET['type'] === 'student_logins') {
        	return $this->checkLoginRegistrationData();
        } elseif ($_GET['type'] === 'student_cards') {
        	return $this->checkStudentCardRegistrationData();
        }


    }

    /**
     * Creates a new user.
     * @return bool Success status of user registration
     */
    private function createNewUser($pre = false, $user_email = null, $user_password_hash = null, $user_admin_level = null)
    {
        // remove html code etc. from username and email

        if ( ! $pre ) {
	        $user_email = htmlentities($_POST['user_email'], ENT_QUOTES);
	        $user_password = $_POST['user_password_new'];
	        $user_admin_level = $_POST['user_admin_level'];
	        // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 char hash string.
	        // the constant PASSWORD_DEFAULT comes from PHP 5.5 or the password_compatibility_library
	        $user_password_hash = password_hash($user_password, PASSWORD_DEFAULT);
        }


		$id = $this->getIdFromEmail($user_email);


        if ($pre) {
            $this->addUserToAdminGroup($id, $user_admin_level, true);
        } else {
			$this->addUserToAdminGroup($id);
        }


        $sql = 'INSERT INTO student_logins (student_id, user_password_hash)
                VALUES(:student_id, :user_password_hash)';
        $query = $this->db_connection->prepare($sql);

        $query->bindValue(':student_id', $id);
        $query->bindValue(':user_password_hash', $user_password_hash);
        // PDO's execute() gives back TRUE when successful, FALSE when not
        // @link http://stackoverflow.com/q/1661863/1114320
        $registration_success_state = $query->execute();

        if ($registration_success_state) {
            $this->feedback = "Your account has been created successfully. You can now log in. $id";
            return true;
        } else {
            $this->feedback = "Sorry, your registration failed. Please go back and try again.";
        }
        // }
        // default return
        return false;
    }

    private function getCurrentUserAdminLevel() {

    	$id = $this->getIdFromEmail($_SESSION['user_email']);

        $sql = 'SELECT groups.admin_level FROM student_groups, groups WHERE student_groups.group_id = groups.group_id AND student_groups.student_id = :student_id';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':student_id', $id);
        $query->execute();
        $result_row = $query->fetchObject();
        if ($result_row) {
        	$val = $result_row->admin_level;
        	return intval($val);

        } else {
        	return 0;
        }
    }

    private function parseAdminLevel($level, $pre = false)
    {

    	if ( ! $pre ) {
	    	if ( ! $level or intval($level) >= $this->getCurrentUserAdminLevel()) {
	    		$level = 0;
	    	}
    	}

    	$admin_level = $level;

    	return $admin_level;
    }

    private function addUserToAdminGroup($student_id, $user_admin_level = null, $pre = false)
    {
        if ($pre) {
           	$admin_level = $user_admin_level;
        } else {
            $admin_level = intval($_POST['user_admin_level']);
        }


        $sql = 'SELECT * FROM groups WHERE name = :group_name AND admin_level = :admin_level';
        $query = $this->db_connection->prepare($sql);
        $query->bindValue(':group_name', "student_union_platform");
        $query->bindValue(':admin_level', $admin_level);
        $query->execute();
        $result_row = $query->fetchObject();


        if ($result_row) {
        	// admin group is
	        $group_id = $result_row->group_id;

	        // bind the $student_id to the group
	        $this->bindUserToAdminGroup($student_id, $group_id);

        } else {
        	// create the admin group!

			$sql = "INSERT INTO groups (group_id, name, admin_level, cetera) VALUES(NULL, :name, :level, 'etc')";
			$query = $this->db_connection->prepare($sql);
			$query->bindValue(':name', "student_union_platform");
			$query->bindValue(':level', $admin_level);

			$query->execute();

			$group_id = $this->db_connection->lastInsertId('group_id');

			$this->bindUserToAdminGroup($student_id, $group_id);

        }

    }
    private function bindUserToAdminGroup($student_id, $group_id)
    {
        $sql = "SELECT * FROM student_groups WHERE student_id = :student_id AND group_id = :group_id";
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

    		$query->execute();
        }

    }

    /**
     * Simply returns the current status of the user's login
     * @return bool User's login status
     */
    public function getUserLoginStatus()
    {
        return $this->user_is_logged_in;
    }

    /**
     * Simple demo-"page" that will be shown when the user is logged in.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageLoggedIn()
    {
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }
				// if ($_GET['type'] === 'student_logins') {
    //             	$this->createNewUser();
				// 	// 'student_credentials', 'student_logins', 'student_cards'))) {
				// } elseif ($_GET['type'] === 'student_credentials') {
				// 	$this->addStudentCredentials();
				// } elseif ($_GET['type'] === 'student_cards') {
				// 	$this->addCardNumber();
				// }

        echo 'Hello ' . $_SESSION['user_email'] . ', you are logged in.<br/><br/>';
        echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=logout">Log out</a>';

        echo '<h2>Add member</h2>';

        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?action=register&type=student_logins" name="registerform">';

        echo '<label for="login_input_email">User\'s email</label>';
        echo '<input id="login_input_email" type="email" name="user_email" required />';
		echo '<div></div>';
        echo '<label for="input_admin_level">User\'s admin level</label>';
        echo '<input id="input_admin_level" type="text" name="user_admin_level" required />';
		echo '<div></div>';
        echo '<label for="login_input_password_new">Password (min. 6 characters)</label>';
        echo '<input id="login_input_password_new" class="login_input" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />';
		echo '<div></div>';
        echo '<label for="login_input_password_repeat">Repeat password</label>';
        echo '<input id="login_input_password_repeat" class="login_input" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />';
		echo '<br />';
		echo '<button>go</button>';
		echo '</form>';

		echo '<br />';

        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?action=register&type=student_credentials" name="registerform">';
        echo '<label for="login_input_email">User\'s email</label>';
        echo '<input id="login_input_email" type="email" name="user_email" required />';
		echo '<div></div>';
        echo '<label for="input_admin_level">User\'s admin level</label>';
        echo '<input id="input_admin_level" type="text" name="user_admin_level" required />';
        echo '<div></div>';
		echo '<label for="input_first_name">First name</label>';
		echo '<input id="input_first_name" type="text" name="first_name" required />';
		echo '<div></div>';
		echo '<label for="input_last_name">Last name</label>';
		echo '<input id="input_last_name" type="text" name="last_name" required />';
		echo '<div></div>';
		echo '<label for="input_nin">Personal security number</label>';
		echo '<input id="input_nin" type="text" name="nin" required />';
		echo '<div></div>';
		echo '<label for="input_sex">Sex</label>';
		echo '<input id="input_sex" type="text" name="sex" required />';
		echo '<div></div>';
		echo '<label for="input_co_address">C/o address(not required)</label>';
		echo '<input id="input_co_address" type="text" name="co_address" />';
		echo '<div></div>';
		echo '<label for="input_street_address">Street address</label>';
		echo '<input id="input_street_address" type="text" name="street_address" required />';
		echo '<div></div>';
		echo '<label for="input_postal_number">Postal number</label>';
		echo '<input id="input_postal_number" type="text" name="postal_number" required />';
		echo '<div></div>';
		echo '<label for="input_post_town">Post town</label>';
		echo '<input id="input_post_town" type="text" name="post_town" required />';
		echo '<div></div>';
		echo '<label for="input_mobile_number">Mobile number</label>';
		echo '<input id="input_mobile_number" type="text" name="mobile_number" required />';
		echo '<div></div>';
		echo '<label for="input_sd_mobile_number">Alt. mobile number</label>';
		echo '<input id="input_sd_mobile_number" type="text" name="sd_mobile_number" required />';
		echo '<div></div>';
		echo '<label for="input_class">Class</label>';
		echo '<input id="input_class" type="text" name="class" required />';
		echo '<br />';
		echo '<button>Go</button>';
		echo '</form>';

		echo '<br />';

        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?action=register&type=student_cards" name="registerform">';
        echo '<label for="login_input_email">User\'s email</label>';
        echo '<input id="login_input_email" type="email" name="user_email" required />';
		echo '<div></div>';
        echo '<label for="input_admin_level">User\'s admin level</label>';
        echo '<input id="input_admin_level" type="text" name="user_admin_level" required />';
        echo '<div></div>';
		echo '<label for="input_card_number">Card number</label>';
		echo '<input id="input_card_number" type="text" name="card_number" required />';
		echo '<br />';
		echo '<button>Go</button>';
		echo '</form>';

    }

    /**
     * Simple demo-"page" with the login form.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageLoginForm()
    {
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }

        echo '<h2>Login</h2>';

        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '" name="loginform">';
        echo '<label for="login_input_email">Email</label> ';
        echo '<input id="login_input_email" type="text" name="user_email" required /> ';
        echo '<label for="login_input_password">Password</label> ';
        echo '<input id="login_input_password" type="password" name="user_password" required /> ';
        echo '<input type="submit"  name="login" value="Log in" />';
        echo '</form>';

        echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '?action=register">Register new account</a>';
    }

    /**
     * Simple demo-"page" with the registration form.
     * In a real application you would probably include an html-template here, but for this extremely simple
     * demo the "echo" statements are totally okay.
     */
    private function showPageRegistration()
    {
        if ($this->feedback) {
            echo $this->feedback . "<br/><br/>";
        }

        echo '<h2>Registration</h2>';

        echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '?action=register" name="registerform">';
        echo '<label for="login_input_email">User\'s email</label>';
        echo '<input id="login_input_email" type="email" name="user_email" required />';
        echo '<label for="login_input_password_new">Password (min. 6 characters)</label>';
        echo '<input id="login_input_password_new" class="login_input" type="password" name="user_password_new" pattern=".{6,}" required autocomplete="off" />';
        echo '<label for="login_input_password_repeat">Repeat password</label>';
        echo '<input id="login_input_password_repeat" class="login_input" type="password" name="user_password_repeat" pattern=".{6,}" required autocomplete="off" />';
        echo '<input type="submit" name="register" value="Register" />';
        echo '</form>';

        echo '<a href="' . $_SERVER['SCRIPT_NAME'] . '">Homepage</a>';
    }
}

// run the application
$application = new OneFileLoginApplication();
// $application->install();
$application->start();