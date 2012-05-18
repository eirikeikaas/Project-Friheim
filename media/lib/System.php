<?php

/**
 * ======= System Class ======
 * 
 * Info
 * 
 * @version 1.0
 * @author Eirik Eikaas, Blest AS
 * @copyright Copyright (c) 2012, Blest AS
 * @todo FIX: docblocks
 * @uses DB
 */

class System{
	/**
	 * Modules array
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $modules = array();

	/**
	 * Tabs array
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $tabs = array();

	/**
	 * Configuration array
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $config = -1;

	/**
	 * Auth-instance
	 *
	 * @access private
	 * @static
	 * @var object
	 **/
	private static $auth = -1;

	/**
	 * User array
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $user = -1;

	/**
	 * Template-variables array
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $vars = array();

	/**
	 * Template-scripts array
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $scripts = array();

	/**
	 * User-scripts array
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $userScripts = array();

	/**
	 * Temporary storage for redirection-route
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $route = "";

	/**
	 * The construct parses the .ini file
	 * 
	 * @access public
	 * @param $inifile string
	 * @return void
	 */

	public function __construct($inifile){
		self::$config = parse_ini_file($inifile);
	}
	
	/**
	 * Writes .ini-file
	 * 
	 * @access public
	 * @static
	 * @param $file string
	 * @$options array
	 * @return void
	 */
	
	public static function write_ini_file($file, array $options){
		$tmp = '';
		foreach($options as $section => $values){
			$tmp .= "[$section]\n";
			foreach($values as $key => $val){
				if(is_array($val)){
					foreach($val as $k =>$v){
						$tmp .= "{$key}[$k] = \"$v\"\n";
					}
				}else{
					$tmp .= "$key = \"$val\"\n";
				}
			}
			$tmp .= "\n";
		}
		file_put_contents($file, $tmp);
		unset($tmp);
	}

	/**
	 * Registers a module to System::$modules
	 * 
	 * @access public
	 * @static
	 * @param $name string
	 * @return boolean
	 */

	public static function registerModule($name){
		if(self::moduleIsActive($name)){
			$file = System::getConfig("basedir")."/media/mod/$name/".ucfirst($name)."_Def.php";
			$module = array();
	
			if(file_exists($file)){
				$module['name'] = ucfirst($name);
				$module['slug'] = strtolower($name);
	
				self::$modules[$module['name']] = $module;

				include_once($file);

				$class = ucfirst($name)."_Def";
				if(class_exists($class)){
					$class = new $class;
					$tabs = $class->getTabs();
					$tabslen = count($tabs);
	
					for($i=0;$i<$tabslen;$i++){
						array_push(self::$tabs, $tabs[$i]);
					}
	
					return true;
				}
			}else{
				return false;
			}
		}else{
			System::log("Excluded module $name");
		}
	}

	/**
	 * Installs modules by running [instance]::install()
	 * 
	 * @access public
	 * @static
	 * @return void
	 */

	public static function installModules(){
		$keys = array_keys(self::$modules);
		$len = count($keys);

		for($i=0;$i<$len;$i++){
			$class = self::getModule($keys[$i], true);

			if(method_exists($class, 'install')){
				$class::install();
			}
		}
	}

	/**
	 * Finds a registered module and instantiates it
	 * 
	 * @access public
	 * @static
	 * @param $name string
	 * @pararm $instantiate bool
	 * @return mixed
	 */

	public static function getModule($name, $instantiate = true){
		$name = strtolower($name);
		$file = System::getConfig("basedir")."/media/mod/$name/".ucfirst($name)."_Def.php";

		if(!empty($name) && array_key_exists(ucfirst($name), self::$modules)){
			include_once($file);
			$class = ucfirst($name)."_Def";

			if(class_exists($class) && get_parent_class($class) == "Controller"){
				if(!$instantiate){
					return $class;
				}else{
					return new $class;
				}
			}
		}else{
			return false;
		}
	}
	
	public static function loadEndpoint($name, $alias = "", $instantiate = false){
		$name = strtolower($name);
		$file = System::getConfig("basedir")."/media/mod/$name/".ucfirst($name)."_Endpoint.php";
				
		if(!empty($name) && array_key_exists(ucfirst($name), self::$modules)){
			include_once($file);
			$class = ucfirst($name)."_Endpoint";

			if(class_exists($class) && get_parent_class($class) == "Endpoint"){
				if(!empty($alias)){
					class_alias($class, $alias);
				}
			
				if(!$instantiate){
					return $class;
				}else{
					return new $class;
				}
			}
		}else{
			return false;
		}
	}
	
	/**
	 * Finds available modules in modules directory and stores them in System::$modules
	 * 
	 * @access public
	 * @static
	 * @return void
	 */

	public static function moduleDiscovery(){
		$dir = scandir("media/mod");
		$dirlen = count($dir);

		for($i=0;$i<$dirlen;$i++){
			if($dir[$i] == "." || $dir[$i] == ".."){ continue; }
			self::registerModule($dir[$i]);
		}
	}

	/**
	 * Checks whether or not a module is active
	 * 
	 * @todo Truly implement this..
	 * @access public
	 * @static
	 * @param $name string
	 * @return Controller
	 */

	public static function moduleIsActive($name){
		return array_search(strtolower($name), explode(",",trim(strtolower(System::getConfig('excludeModules'))))) === false;
	}

	/**
	 * Registers auth-instance to System::$auth
	 * 
	 * @access public
	 * @return void
	 */

	public function authInstance(&$auth){
		self::$auth = $auth;
	}

	/**
	 * logs errors and warnings to log-file
	 * 
	 * @access public
	 * @static
	 * @param $msg string
	 * @param $trace boolean
	 * @param $full boolean
	 * @return void
	 */

	public static function log($msg, $trace = false, $full = false){
		$bt = debug_backtrace();
		$f = fopen(System::getConfig('logfile'),'a+');
		if($f!==false){
			$time = date('[dmY-H:i:s]');
			if($trace){
				if(!$full){
				$backtrace = "";
				$len  = count($bt);
				for($i=1;$i<$len;$i++){
					$args = "";
					$arglen = count($bt[$i]['args']);
					if($arglen>0){
						for($ii=0;$ii<$arglen;$ii++){
							if(gettype($bt[$i]['args'][$ii]) == ("string"|"int"|"boolean"|"integer"|"double"|"array")){
								$args .= var_export($bt[$i]['args'][$ii]);
							}else{
								$args .= gettype($bt[$i]['args'][$ii]);
							}
							if($ii+1<$arglen){
								$args .= ", ";
							}
						}
					}
					$class = @$bt[$i]['class'];
					$type = @$bt[$i]['type'];
					$function = @$bt[$i]['function'];
					$file = @$bt[$i]['file'];
					$line = @$bt[$i]['line'];

					$backtrace .= "  #$i  {$class}{$type}{$function}($args) @ {$file}:{$line}\n";
				}
			}else{
				ob_start();
				debug_print_backtrace();
				$backtrace = ob_get_clean();
			}
				fwrite($f, "$time $msg @ {$bt[0]['file']}:{$bt[0]['line']}\n----------------------------------------------------------\n$backtrace----------------------------------------------------------\n");
			}else{
				fwrite($f, "$time $msg @ {$bt[0]['file']}:{$bt[0]['line']}\n");
			}
			fclose($f);
		}
	}

	/**
	 * Temporarily stores the redirection routing
	 * 
	 * @access public
	 * @static
	 * @param $route string
	 * @param $external boolean
	 * @return void
	 */

	public static function setRoute($route, $external = false){
		System::log("Route has been set to $route");
		self::$route = array($route, $external);
	}

	/**
	 * Does the actual redirection based on System::$route
	 * 
	 * @access public
	 * @static
	 * @param $app Slim
	 * @return boolean
	 */

	public static function getRoute(Slim $app){
		if(!empty(self::$route[0])){
			if(self::$route[1]){
				$app->redirect(self::$route[0]);
			}else{
				$app->redirect(System::getConfig('prefix')."/".self::$route[0]);
			}
		}else{
			return false;
		}
	}

	/**
	 * Sets a flash-message for Slim
	 * 
	 * @access public
	 * @static
	 * @param $app Slim
	 * @param $message string
	 * @param $success boolean
	 * @return void
	 */

	public static function setMessage(Slim $app, $message, $success = true){
		$app->flash('message', $message);
		$app->flash('success', $success);
	}

	/**
	 * Returns config-variables from System::$config array
	 * 
	 * @access public
	 * @static
	 * @param $var string
	 * @return mixed
	 */

	public static function getConfig($var){
		if(is_array($var)){
			$len = count($var);
			$cur = self::$config;

			for($i=0;$i<$len;$i++){
				$cur = $cur[$var[$i]];
			}

			$output = $cur;
		}else{
			if(isset(self::$config[$var]) && !empty(self::$config[$var])){
				$output = self::$config[$var];
			}else{
				System::log("Var '$var' does not exist in configfile");
				return false;
			}
		}
		return $output;
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function addOption($key, $type, $human, $default){
		if(class_exists("ORM")){
			$option = ORM::for_table(System::getConfig('settingstable'))->create();
			$option->key = $key;
			$option->human = $human;
			$option->type = $type;
			$option->value = $default;
			$option->save();
		}else{
			self::log("Calling a DB-function before the ORM-class is defined is just silly..");
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function getOption($key){
		if(class_exists("ORM")){
			$option = ORM::for_table(System::getConfig('settingstable'))->where('key', $key)->find_one();
			if($option !== false){
				return $option->value;
			}else{
				System::log("Option was not found", true);
				return false;
			}
		}else{
			self::log("Calling a DB-function before the ORM-class is defined is just silly..");
		}
	}
	
	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function updateOption($key, $value, $human = ""){
		if(class_exists("ORM")){
			$option = ORM::for_table(System::getConfig('settingstable'))->where('key', $key)->find_one();
			if($human != ""){ $option->human = $human; }
			$option->value = value;
			$option->save();
		}else{
			self::log("Calling a DB-function before the ORM-class is defined is just silly..");
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function deleteOption($key){
		if(class_exists("ORM")){
			$option = ORM::for_table(System::getConfig('settingstable'))->where('key', $key)->find_one();
			$option->delete();
		}else{
			self::log("Calling a DB-function before the ORM-class is defined is just silly..");
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function isDebug(){
		return (bool)self::$config['debug'];
	}

	private static function debugData($app){
		$user = self::$auth->userData();
		$keynice = implode(str_split(strtoupper(Auth::key(false, true)),2),":");
		$varkeys = array_keys(self::$vars);
		$error = @$php_errormsg;
		$str = "PHP:  \t ".phpversion()." | $error\n";
		$str .= "MYSQL:\t ".DB::report()."\n";
		$str .= "USER: \t {$user['id']}, {$user['name']}, {$user['email']}\n";
		$str .= "AGE: \t ".(time()-$_SESSION['CREATED'])."sec\n";
		$str .= "MAX: \t ".System::getConfig('sessionRegenerate')."sec\n";
		$str .= "KEY:  \t $keynice\n";
		$str .= "VARS: \t ".implode($varkeys, ", ")."\n";
		return $str;
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function vars($app, $vars = array()){
		$system = array();

		$system['title'] = self::getOption('title');
		$system['prefix'] = self::getConfig('prefix');
		$system['debug'] = self::isDebug();
		$system['debugdata'] = self::debugData($app);
		$system['admincontact'] = self::getConfig('admincontact');
		$system['user'] = self::$auth->userData();
		$system['user']['admin'] = self::$auth->isAdmin();
		$system['user']['loggedin'] = self::$auth->isLoggedIn();
		$system['tabs'] = self::sortedTabs();
		$system['scripts'] = array_merge(self::$scripts, self::$userScripts);

		return array_merge($system, self::$vars, $vars);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function sortedTabs(){
		$tabs = self::$tabs;
		$keys = array();
		$rest = array();
		
		foreach($tabs as $tab){
			$keys[] = $tab['index'];
			$rest[] = $tab;
		}
		
		array_multisort($keys, SORT_ASC, $rest);
		
		return $rest;
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function addVars($args){
		self::$vars = array_merge(self::$vars, $args);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function addScript($type, $script, $link = true){
		self::$scripts[] = array(
			"type" => $type,
			"script" => $script,
			"link" => $link
		);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function addGlobalHook($hook, $callback){
		Controller::addHook('*', $hook, $callback);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function setUserScripts($array){
		if(is_array($array)){
			self::$userScripts = $array;
		}
	}

	public static function stat($file) {
		clearstatcache();
		$ss=@stat($file);
		if(!$ss) return false; //Couldnt stat file
	 
		$ts=array(
			0140000=>'ssocket',
			0120000=>'llink',
			0100000=>'-file',
			0060000=>'bblock',
			0040000=>'ddir',
			0020000=>'cchar',
			0010000=>'pfifo'
		);
	 
		$p=$ss['mode'];
		$t=decoct($ss['mode'] & 0170000); // File Encoding Bit
	 
		$str =(array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
		$str.=(($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
		$str.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
		$str.=(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
		$str.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
		$str.=(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
		$str.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));
	 	
	 	$s = array();

		$s=array(
			'perms'=>array(
				'umask'=>sprintf("%04o",@umask()),
				'human'=>$str,
				'octal1'=>sprintf("%o", ($ss['mode'] & 000777)),
				'octal2'=>sprintf("0%o", 0777 & $p),
				'decimal'=>sprintf("%04o", $p),
				'fileperms'=>@fileperms($file),
				'mode1'=>$p,
				'mode2'=>$ss['mode']
			),
			'owner'=>array(
				'fileowner'=>$ss['uid'],
				'filegroup'=>$ss['gid'],
				'owner'=>
					(function_exists('posix_getpwuid'))?
						@posix_getpwuid($ss['uid']):'',
				'group'=>
					(function_exists('posix_getgrgid'))?
						@posix_getgrgid($ss['gid']):''
			),

	 		'file'=>array(
				'filename'=>$file,
				'realpath'=>(@realpath($file) != $file) ? @realpath($file) : '',
				'dirname'=>@dirname($file),
				'basename'=>@basename($file)
			),

			'filetype'=>array(
				'type'=>substr($ts[octdec($t)],1),
				'type_octal'=>sprintf("%07o", octdec($t)),
				'is_file'=>@is_file($file),
				'is_dir'=>@is_dir($file),
				'is_link'=>@is_link($file),
				'is_readable'=> @is_readable($file),
				'is_writable'=> @is_writable($file)
			),
	  
			'device'=>array(
				'device'=>$ss['dev'], //Device
				'device_number'=>$ss['rdev'], //Device number, if device.
				'inode'=>$ss['ino'], //File serial number
				'link_count'=>$ss['nlink'], //link count
				'link_to'=>(@$s['type']=='link') ? @readlink($file) : ''
			),
	 
			'size'=>array(
				'size'=>$ss['size'], //Size of file, in bytes.
				'blocks'=>$ss['blocks'], //Number 512-byte blocks allocated
				'block_size'=> $ss['blksize'] //Optimal block size for I/O.
			),
	
			'time'=>array(
				'mtime'=>$ss['mtime'], //Time of last modification
				'atime'=>$ss['atime'], //Time of last access.
				'ctime'=>$ss['ctime'], //Time of last status change
				'accessed'=>@date('Y M D H:i:s',$ss['atime']),
				'modified'=>@date('Y M D H:i:s',$ss['mtime']),
				'created'=>@date('Y M D H:i:s',$ss['ctime'])
			)
		);
	
		clearstatcache();
		return $s;
	}

	public static function nicetime($date){
		if(empty($date)){
			return "ERROR: No date provided";
		}
		$periods = array("sekund", "minutt", "time", "dag", "uke", "måned", "år", "tiår");
		$lengths = array("60","60","24","7","4.35","12","10");
		$now = time();
		$unix_date = strtotime($date);
		
		// check validity of date
		if(empty($unix_date)){
			return "ERROR: Invalid date";
		}

		// is it future date or past date
		if($now > $unix_date){
			$difference = $now - $unix_date;
			$tense = "siden";
		}else{
			$difference = $unix_date - $now;
			$tense = "fra nå";
		}

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}

		$difference = round($difference);
		if($difference != 1) {
			$periods = array("sekunder", "minutter", "timer", "dager", "uker", "måneder", "år", "tiår"); // plural for international words
		}
		return "$difference $periods[$j] {$tense}";
	}

	public static function formatForTable($data){
		$array = array();

		return $array;
	}
}

/**
 * ======= Module-definition Class ======
 * 
 * Info
 * 
 * @version 1.0
 * @abstract
 * @author Eirik Eikaas, Blest AS
 * @copyright Copyright (c) 2012, Blest AS
 * @todo FIX: docblocks
 * @uses DB
 */

abstract class Controller{
	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private $actionTable = array();

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private $tabsTable = array();

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private $name = "";

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private $slug = "";

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private $version = "";

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $scripts = array();

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private $filters = array();

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private $hooks = array();

	/**
	 * Walk-around array which holds IP-addresses that can bypass the authentication
	 *
	 * @access private
	 * @static
	 * @var array
	 **/
	private static $globalhooks = array();

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public abstract function __construct();

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function info($name, $slug, $version){
		$this->name = $name;
		$this->slug = $slug;
		$this->version = $version;
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public function version($version = false){
		if($version !== false){
			return version_compare($version, $this->version);
		}else{
			return $this->version;
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public function getTabs(){
		$tabs = $this->tabsTable;
		$len = count($tabs);
		$index = array();
		$data = array();

		foreach($tabs as $key => $val){
			$index[$key] = $val['index'];
			$data[$key] = $val;
		}

		array_multisort($index, SORT_ASC, $data);
		
		return $data;
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public function getTemplate($action){
		if(!empty($action)){
			$tpl = 'media/mod/'.$this->slug.'/templates/'.$this->actionTable[$action]['template'];

			if(file_exists($tpl)){
				return $tpl;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public function doAction($hook, $action, $app, $parameters = false){
		if(isset($this->actionTable[$action]['hooks'][$hook]) && !empty($this->actionTable[$action]['hooks'][$hook])){
			$method = $this->actionTable[$action]['hooks'][$hook];
		}else{
			System::log("Action '$action' does not exist", true);
			$app->redirect(System::getConfig('prefix').'/admin');
			return;
		}
		System::log("Fetched action '$method' / ".$method['callback']);
		$isMapped = false;

		System::setUserScripts(@self::$scripts[$action]);

		if(is_array($method)){
			if(isset($method['route'])){
				System::setRoute($method['route']);
			}

			if(isset($method['parameterMap'])){
				$map = $method['parameterMap'];
				$keys = array_keys($map);
				$len = count($keys);
				$vals = explode(System::getConfig('paramsplit'), $parameters);
				$mapped = array();

				for($i=0;$i<$len;$i++){
					$param = $keys[$i];

					if($map[$param] === true && !isset($vals[$i])){
						return false;
					}else{
						$mapped[$keys[$i]] = $vals[$i];
					}
				}
				$parameters = $mapped;
				$isMapped = true;
			}

			$method = @$method['callback'];
		}

		if(!empty($method) && method_exists($this, $method)){
			if(!$isMapped && $parameters != false){
				$parameters = explode(System::getConfig('paramsplit'), $parameters);
			}
			$m = $this->$method($app, $parameters);
			System::log("Successfully executed $method for {$this->name}");
			return $m;
		}else{
			System::log("$method does not exist (".var_export($method).",".var_export(method_exists($this, $method)).",".var_export((!empty($method))).")", true);
			return false;
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function defineAction($name, $template, $hooks){
		$this->actionTable[$name] = array(
			"template" =>	$template, 
			"hooks" =>		$hooks
		);
	}

	public static function addHook($module, $hook, $callback, $failable = false){
		if($module != '*'){
			$mod = ucfirst($module)."_Def";
			$file = System::getConfig("basedir")."/media/mod/$module/$mod.php";

			if(class_exists($mod) || file_exists($file)){
				HookManager::addHook($module, $hook, $callback);
			}else if($failable){
				System::log("Could not add hook to module $mod");
			}
		}else{
			HookManager::addHook('*', $hook, $callback);
		}
	}

	public function runHook($app, $hook, $params = array()){
		System::log("Running hook $hook...");
		return HookManager::runHook($this->name, $hook, $app, $params);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function addFilter($module, $filter, $callback, $failable = false){
		if($module != '*'){
			$mod = ucfirst($module)."_Def";
			$file = System::getConfig("basedir")."/media/mod/$module/$mod.php";

			if(class_exists($mod) || file_exists($file)){
				FilterManager::addFilter($this->name, $filter, $callback, $failable);
			}else if($failable){
				System::log("Could not add filter to module $mod");
			}
		}else{
			System::log("Global filters does not make sense..");
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function applyFilter($app, $filter, $filterdata){
		System::log("Running filter $filter...");
		return FilterManager::applyFilter($this->name, $filter, $app, $filterdata);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function defineTab($name, $slug, $route, $icon, $admin = false, $index = 0){
		$this->tabsTable[] = array(
			"name" =>	 $name,
			"slug" =>	 $slug,
			"route" =>	 $route,
			"icon" =>	 $icon,
			"allowed" => $admin,
			"index" =>	 $index
		);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function defineScript($action, $type, $script, $link = true){
		$len = count($action);

		if(is_array($action)){
			for($i=0;$i<$len;$i++){
				$this->defineScriptInternal($action[$i], $type, $script, $link);
			}
		}else{
			$this->defineScriptInternal($action, $type, $script, $link);
		}
	}
	
	private function defineScriptInternal($action, $type, $script, $link = true){
		if(isset(self::$scripts[$action]) && !empty(self::$scripts[$action])){
			foreach(self::$scripts[$action] as $scripts){
				if($scripts['script'] === $script){
					return;
				}
			}
		}
		
		self::$scripts[$action][] = array(
			"type" => $type,
			"script" => $script,
			"link" => $link
		);
	}
	
	protected function loadHelper(){
		$file = System::getConfig("basedir")."/media/mod/".ucfirst($this->name)."_Helper.php";
		if(file_exists($file)){
			include_once($file);
			return true;
		}
		return false;
	}
}

class HookManager{
	private static $hooks = array();

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function addHook($namespace, $name, $callback, $failable = false){
		if(is_callable($callback)){
			if(!isset(self::$hooks[$namespace])){ self::$hooks[$namespace] = array(); }
			self::$hooks[$namespace][$name][] = $callback;
		}else{
			System::log("$namespace::$name callback is not callable");
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function runHook($namespace, $hook, $app, $params){
		System::log("Running hook $hook...");
		$hookcount = 0;
		$data = array("app" => $app);
		$data = array_merge($data, $params);

		if($namespace === '*'){
			System::log("You cannot call the global hooks, these are called automagifantastically.");
			return;
		}

		if(isset(self::$hooks['*'][$hook]) && is_array(self::$hooks['*'][$hook])){
			foreach(self::$hooks['*'][$hook] as $thehook){
				if(is_callable($thehook)){
					$hookcount++;

					if($thehook instanceof Closure){
						$thehook($data);
					}else{
						call_user_func_array($thehook, array($data));
					}
				}
			}
		}
		if(isset(self::$hooks[$namespace][$hook]) && is_array(self::$hooks[$namespace][$hook])){
			foreach(self::$hooks[$namespace][$hook] as $thehook){
				if(is_callable($thehook)){
					$hookcount++;

					if($thehook instanceof Closure){
						$thehook($data);
					}else{
						call_user_func_array($thehook, array($data));
					}
				}
			}
		}
		System::log("$hookcount hooks executed for $hook in $namespace");
	}
}

class FilterManager{
	private static $filters = array();

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function addFilter($namespace, $name, $callback, $failable = false){
		if(is_callable($callback)){
			if(!isset(self::$filters[$namespace])){ self::$filters[$namespace] = array(); }
			self::$filters[$namespace][$name][] = $callback;
		}else{
			System::log("$namespace::$name callback is not callable");
		}
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function applyFilter($namespace, $name, $app, &$filterdata){
		System::log("Running filter $name in $namespace...");
		$filtercount = 0;
		$data = array("app" => $app);
		$data = array_merge($data, $filterdata);

		if(isset(self::$filters[$namespace][$name]) && is_array(self::$filters[$namespace][$name])){
			foreach(self::$filters[$namespace][$name] as $thefilter){
				if(is_callable($thefilter)){
					$filtercount++;

					if($thefilter instanceof Closure){
						$filterdata = $thefilter($data);
					}else{
						$filterdata = call_user_func_array($thefilter, array($data));
					}
				}
			}
		}
		System::log("$filtercount filters executed for $name in $namespace");
	}
}

/*
SHOULD BE DEPRECATED ASAP
*/
abstract class Model{
	private $table = "";

	abstract static function insert();
	abstract static function update();
	abstract static function delete();

	protected function table($name){
		$this->table = $name;
	}

	public function getAll($offset = 0, $limit = 10, $join = false){
		System::log("DEPRECATION WARNING: The Model-class is due for removal", true);
		$c_offset = DB::escape($offset);
		$c_limit = DB::escape($limit);	
		$t = $this->table;
		$res = DB::get("SELECT * FROM $t LIMIT $c_limit OFFSET $c_offset", false);
		return $res;
	}

	public function getSingle($id, $join = false){
		System::log("DEPRECATION WARNING: The Model-class is due for removal", true);
		$c_id = DB::escape($id);
		$t = $this->table;
		$res = DB::get("SELECT * FROM $t WHERE id = $c_id LIMIT 1");
		return $res;
	}

	private function formatJoin($join){
		System::log("DEPRECATION WARNING: The Model-class is due for removal", true);
		$formatted = "";
		$len = count($join);

		for($i=0;$i<$len;$i++){
			$xleft = explode($join[$i][0],'.');
			$xright = explode($join[$i][2],'.');

			if($xleft[0] != $this->table){
				$jointable = $xleft[0];
			}else if($xleft[0] != $this->table){
				$jointable = $xright[0];
			}

			$formatted .= "INNER JOIN $jointable ON {$join[$i]} ";

			if($i<$len){
				$formatted .= "AND ";
			}
		}
		return $formatted;
	}
}

abstract class Helper{
	private $name = "";
	private $slug = "";
	private $version = "";

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function info($name, $slug, $version){
		$this->name = $name;
		$this->slug = $slug;
		$this->version = $version;
	}
}

abstract class Endpoint{
	private $name = "";
	private $slug = "";
	private $version = "";
	
	protected function loadHelper(){
		$file = System::getConfig("basedir")."/media/mod/".strtolower($this->name)."/".ucfirst($this->name)."_Helper.php";
		if(file_exists($file)){
			include_once($file);
			return true;
		}
		return false;
	}
	
	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function info($name, $slug, $version){
		$this->name = $name;
		$this->slug = $slug;
		$this->version = $version;
	}
}


?>