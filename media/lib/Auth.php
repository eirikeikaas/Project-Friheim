<?php

/**
 * ======= Blest General Authentication Class ======
 * 
 * Info
 * 
 * @version 1.0
 * @author Eirik Eikaas, Blest AS
 * @copyright Copyright (c) 2012, Blest AS
 * @uses DB
 */

class Auth{
	/**
	 * Connection-object
	 *
	 * @access private
	 * @var object
	 **/
	private $conn = false;

	/**
	 * User-agent string
	 *
	 * @access private
	 * @var string
	 **/
	private $ua = "";

	/**
	 * Salt for cookie-crypting
	 *
	 * @access private
	 * @static
	 * @var string
	 **/
	private static $cryptsalt = '$/I&SDq';

	/**
	 * Crypt-algorithm to be used
	 *
	 * @access private
	 * @static
	 * @var string
	 **/
	private static $cryptalgo = MCRYPT_DES;

	/**
	 * Crypt-mode to be used
	 *
	 * @access private
	 * @static
	 * @var string
	 **/
	private static $cryptmode = MCRYPT_MODE_ECB;

	/**
	 * General salt
	 *
	 * @access private
	 * @static
	 * @var string
	 **/
	private static $salt = 'KUCYEW#ix24zh23#"H/#Z';

	/**
	 * Logged-in bool, states whether or not the user is logged in
	 *
	 * @access private
	 * @static
	 * @var bool
	 **/
	private static $loggedIn = false;

	/**
	 * Admin bool, states whether or not the user is admin
	 *
	 * @access private
	 * @static
	 * @var string
	 **/
	private static $admin = false;

	/**
	 * Holds useful information about the user
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $user = false;

	/**
	 * Holds useful information about the user
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $users = false;

	/**
	 * The users login-hash
	 *
	 * @access private
	 * @static
	 * @var string
	 **/
	private static $loginhash = "";

	/**
	 * The table in which users are stored
	 *
	 * @access private
	 * @var string
	 **/
	private $usertable = "";

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $watable = array();


	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public function __construct($table = "Users"){
		if(class_exists("DB") && class_exists("System")){
			$this->conn = &DB::getInstance();
			self::$users = ORM::for_table(System::getConfig('userTable'));
			$this->session();

			$this->ua = md5($_SERVER['HTTP_USER_AGENT']);
			$this->usertable = $table;
			
			// Hooks
			System::addGlobalHook('startForm', function($data){
				System::addVars(array("key" => Auth::key()));
			});

			System::addGlobalHook('saveForm', function($data){
				$key = $data['app']->request()->post('key');
				if(!empty($key)){
					if(Auth::key($key)){
						return true;
					}else{
						System::log("Auth failed to verify the form-key and will now redirect to index with message");
					}
				}else{
					System::log("Auth form-key was not in POST and will now redirect to index with message");
				}
				System::setMessage($data['app'], "Kunne ikke verifisere form-nøkkel", false);
				$data['app']->redirect(System::getConfig('prefix').'/admin');
				return false;
			});
		}else{
			die("You need to include the DB-class and System-class before instantiating Auth");
		}
	}

	/**
	 * Destruct destroys the executed session-variable
	 * 
	 * @access public
	 * @return void
	 */

	public function __destruct(){
		$_SESSION['executed'] = -1;
		unset($_SESSION['executed']);
	}

	/**
	 * Starts and evaluates sessions
	 * 
	 * @access private
	 * @return void
	 */

	private function session(){
		@session_start();

		if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > System::getConfig('sessionDestroy'))) {
			session_destroy();
			session_unset();
		}
		$_SESSION['LAST_ACTIVITY'] = time();

		if (!isset($_SESSION['CREATED'])) {
			$_SESSION['CREATED'] = time();
		} else if (time() - $_SESSION['CREATED'] > System::getConfig('sessionRegenerate')) {
			session_regenerate_id(true);
			$_SESSION['CREATED'] = time();
		}
	}

	/**
	 * Logs-in users according to the params
	 *
	 * @access public
	 * @param $brid string
	 * @param $pswd string
	 * @return void
	 */
	
	public function login($brid, $pswd){
		$c_brid = $this->conn->escape_string($brid);
		$c_pswd = self::password($pswd);
				
		// Check that brid is email
		if(preg_match('/^([_a-z0-9-.])+@([a-z0-9-]+.)+[a-z.]{2,5}$/i',$c_brid) === 1){
			$q = self::$users->where('email', $c_brid)->where('password', $c_pswd)->find_one();

			// Check that query didn't fail
			if($q !== false){
				$r = $q->as_array();
				$hash = hash('sha256',hash('sha256',(time()*rand())*5000));
				$remote = hash('sha256',hash('sha256',(time()*rand())*5000).self::$salt);
				setcookie('auth',Auth::encrypt($hash), 0, '/');
				setcookie('user',Auth::encrypt($r['email']), 0, '/');
				$_SESSION[$hash] = $remote;
				$remote = hash('sha256',$remote.$this->ua);
				$q->loginhash = $remote;
				$q->save();
				return true;
			}else{
				return -2;
			}
		}else{
			return -3;
		}
	}

	/**
	 * Encrypt a string and salt with $cryptsalt
	 * @access private
	 * @param $str string
	 * @return string
	 */

	private static function encrypt($str){
		$block = mcrypt_get_block_size(self::$cryptalgo, 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);
		return mcrypt_encrypt(self::$cryptalgo, self::$cryptsalt, $str, self::$cryptmode);
	}

	/**
	 * Decrypt a string
	 * @access private
	 * @param $str string
	 * @return string
	 */

	private static function decrypt($str){
		$str = mcrypt_decrypt(self::$cryptalgo, self::$cryptsalt, $str, self::$cryptmode);
		$block = mcrypt_get_block_size('des', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		return substr($str, 0, strlen($str) - $pad);
	}

	/**
	 * Check whether ot not an IP-address is in the walkaround array
	 * 
	 * @todo NOT YET IMPLEMENTED
	 * @access private
	 * @param $str string
	 * @return bool
	 */
	
	private function wa($ip){
		if(in_array($ip, $this->watable)){
			setcookie('wac',sha1(time().$user['id'].md5(time)));
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Logout destroys cookies and session and returns to the same page
	 *
	 * @access public
	 * @return void
	 */
	
	public function logout(){
		setcookie('auth','');
		setcookie('user','');
		unset($_SESSION['auth']);
		unset($_SESSION['admin']);
		session_destroy();
		return true;
	}

	/**
	 * Checks whether or not the user is currently logged in
	 * 
	 * @access public
	 * @return bool
	 */
	
	public function isLoggedIn(){
		if(self::$loggedIn===false)	{
			if(isset($_COOKIE['auth'])){
				$remote = Auth::decrypt($_COOKIE['auth']);	
				$c_email = $this->conn->escape_string(urldecode(Auth::decrypt($_COOKIE['user'])));
			
				if(strlen($remote) === 64){
					self::$loginhash = $loginhash = hash('sha256',@$_SESSION[$remote].$this->ua);
					$q = self::$users->raw_query("SELECT *, CONCAT(firstname, ' ', lastname) AS name FROM {$this->usertable} WHERE loginhash = :loginhash AND email = :email", array('loginhash' => $loginhash, 'email' => $c_email))->find_one();

					if($q !== false){
						self::$loggedIn = true;
						self::$user = $q->as_array();
						return true;
					}else{
						return false;
					}
				}
			}
		}else{
			return true;
		}
	}

	/**
	 * Checks if a user is admin, either based on the given ID or the current user
	 * 
	 * @access public
	 * @param $id int
	 * @return bool
	 */
	
	public function isAdmin($id = false){
		if(self::$admin===false && !$id){
			$remote = Auth::decrypt(@$_COOKIE['auth']);
			$c_email = $this->conn->escape_string(urldecode(Auth::decrypt(@$_COOKIE['user'])));
			
			if(strlen($remote) === 64){
				$loginhash = hash('sha256',@$_SESSION[$remote].$this->ua);
				$q = $this->conn->query("SELECT admin FROM {$this->usertable} WHERE loginhash = '{$loginhash}' AND email = '{$c_email}' LIMIT 1");
				
				if($q !== false){
					if($q->num_rows === 1){
						$r = $q->fetch_assoc();
						self::$admin = (bool)$r['admin'];
						return self::$admin;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}
		}else if($id !== false){
			$q = $this->conn->query("SELECT admin FROM {$this->usertable} WHERE id = {$c_id}");
			
			if($q !== false){
				if($q->num_rows === 1){
					$r = $q->fetch_assoc();
					return (bool)$r['admin'];
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return true;
		}
	}

	/**
	 * Retruns useful information about a user, either based on the given ID or the current user
	 *
	 * @access private
	 * @static
	 * @param $id int
	 * @return array
	 */
	
	public static function userData($id = false){
		if($id !== false){
			$d = &DB::getInstance();
			$c_id = $d->escape_string($id);
			$q = self::$users->find_one($c_id);
			if($q !== false){
				$a = $q->as_array();
				$a['name'] = $a['firstname'].' '.$a['lastname'];
				
				$qd = ORM::for_table('UsersData')->where('user', $c_id)->find_many();
				
				if($qd !== false){
					$b = $qd->as_array();
					$a['data'] = $b;
					return $a;
				}
			}
			
		}else{
			return self::$user;
		}
	}

	/**
	 * Returns a hashed version of the given password
	 * @access public
	 * @static
	 * @param $pass string
	 * @return string
	 */
	
	public static function password($pass){
		return hash("sha256",hash("sha256",$pass.self::$salt));
	}

	/**
	 * Updates a password by confirming that they have given the correct current password, or is
	 * an admin, and that the two new passwords they have given is the same
	 * 
	 * @access public
	 * @param $id int
	 * @param $old string
	 * @param $new string
	 * @param $cnf string
	 * @return bool
	 */
	
	public function updatePassword($id, $old, $new, $cnf){
		$c_pswd = self::password($old);
		$c = (bool)DB::get("SELECT id FROM {$this->usertable} WHERE password = '{$c_pswd}' AND id = {$id}") !== false;
		if($new === $cnf && $this->ifAdmin($c,true)){
			$c_new = self::password($new);
			return (bool)DB::set("UPDATE {$this->usertable} SET password = '{$c_new}' WHERE id = {$id}");
		}else{
			return false;
		}
	}

	/**
	 * Generates or checks form-keys to prevent XSR
	 *
	 * @access public
	 * @static
	 * @param $check string
	 * @return mixed
	 */
	
	public static function key($check=false, $return = false){
		if($check === false){
			if($return){
				return @$_SESSION['key'];
			}
			// Generate
			$key = hash('sha256',(time()*(rand()*10)).$_SESSION[Auth::decrypt($_COOKIE['auth'])]);
			$stamp = time();
			
			$_SESSION['key'] = $key;
			$_SESSION[$key] = $stamp;
			
			System::log("Key: ".implode(str_split(strtoupper(Auth::key(false, true)),2),":"));

			return $key;
		}else{
			// Check ( Session-key is the same as $check, the stamp is not older than 10 minutes and the stamp is not from the future )
			if($check===@$_SESSION['key'] && @$_SESSION[$check] > time()-600 && @$_SESSION[$check] < time()){
				unset($_SESSION['key']);
				unset($_SESSION[$check]);
				$_SESSION['executed'] = time();
				return true;
			}else{
				return false;
			}
		}
	}

	/**
	 * Checks to see if the was checked no more than 5secs ago
	 * This is used to confirm that the form-key has been checked and not forgotten. Again, to prevent XSR
	 * 
	 * @access public
	 * @static
	 * @return bool
	 */

	public static function executed(){
		if(isset($_SESSION['executed']) && (int)$_SESSION['executed'] < time()+5 && (int)$_SESSION['executed'] > time() && (int)$_SESSION['executed'] != -1){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Simple XOR logic
	 * 
	 * @access public
	 * @param $condition bool
	 * @param $negative bool
	 * @return bool
	 */
	
	public function ifAdmin($condition, $negative = false){
		System::log("DEPRECATION WARNING: ifAdmin() will be removed due to abstraction", true);
		if($negative){
			if(!$this->isAdmin()){
				return $condition;
			}else{
				return true;
			}
		}else{
			if($this->isAdmin()){
				return $condition;
			}else{
				return true;
			}
		}
	}
}

?>