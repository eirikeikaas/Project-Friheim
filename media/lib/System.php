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
	 * @var array
	 **/
	private static $user = -1;

	/**
	 * Template-variables array
	 *
	 * @access private
	 * @var array
	 **/
	private static $vars = array();

	/**
	 * Template-scripts array
	 *
	 * @access private
	 * @var array
	 **/
	private static $scripts = array();

	/**
	 * Template-scripts array
	 *
	 * @access private
	 * @var array
	 **/
	private static $userScripts = array();

	/**
	 * Template-scripts array
	 *
	 * @access private
	 * @var array
	 **/
	private static $route = "";

	/**
	 * The construct parses the .ini file
	 * 
	 * @access private
	 * @param $inifile string
	 * @return void
	 */

	public function __construct($inifile){
		self::$config = parse_ini_file($inifile);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function registerModule($name){
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
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
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
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function getModule($name, $instantiate = true){
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

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public function authInstance(&$auth){
		self::$auth = $auth;
	}

	/**
	 *
	 */

	public static function log($msg, $trace = false, $full = false){
		$bt = debug_backtrace();
		$f = fopen(System::getConfig('logfile'),'a+');
		if($f!==false){
			$time = date('[dmY-h:i:s]');
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
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function setRoute($route){
		System::log("Route has been set to $route");
		self::$route = $route;
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function getRoute(Slim $app){
		if(!empty(self::$route)){
			$app->redirect(System::getConfig('prefix')."/".self::$route);
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

	public static function setMessage(Slim $app, $message, $success = true){
		$app->flash('message', $message);
		$app->flash('success', $success);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
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
			$output = self::$config[$var];
		}
		return $output;
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

	private static function debugData(){
		$user = self::$auth->userData();
		$keynice = implode(str_split(strtoupper(Auth::key(false, true)),2)," ");
		$str = "PHP:  \t ".phpversion()."\n";
		$str .= "MYSQL:\t ".DB::report()."\n";
		$str .= "USER: \t {$user['id']}, {$user['name']}, {$user['email']}\n";
		$str .= "AGE: \t ".(time()-$_SESSION['CREATED'])."sec\n";
		$str .= "MAX: \t ".System::getConfig('sessionRegenerate')."sec\n";
		$str .= "KEY:  \t $keynice\n";
		return $str;
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function vars($vars = array()){
		$system = array();

		$system['title'] = self::getConfig('title');
		$system['prefix'] = self::getConfig('prefix');
		$system['debug'] = self::isDebug();
		$system['debugdata'] = self::debugData();
		$system['user'] = self::$auth->userData();
		$system['user']['admin'] = self::$auth->isAdmin();
		$system['user']['loggedin'] = self::$auth->isLoggedIn();
		$system['tabs'] = self::$tabs;
		$system['scripts'] = array_merge(self::$scripts, self::$userScripts);


		return array_merge($system, self::$vars, $vars);
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	public static function addVars($args){
		self::$vars = array_merge(self::$vars, $args);
		//die(var_export(self::$vars))
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
	private static $scripts = array();

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

	protected function name($name, $slug){
		$this->name = $name;
		$this->slug = $slug;
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
		$method = $this->actionTable[$action]['hooks'][$hook];
		System::log("Fetched action $method / ".$method['callback']);
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
				$vals = $parameters;

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
				include_once($file);	

				$mod = new $mod;
				$mod->addHookInternal($hook, $callback);
			}else if($failable){
				System::log("Could not add hook to module $mod");
			}
		}else{
			Controller::$globalhooks[$hook][] = $callback;
		}
	}

	public static function addHookInternal($hook, $callback){
		$this->hooks[$hook][] = $callback;
	}

	public function runHook($app, $hook, $params = array()){
		System::log("Running hook $hook...");
		$hookcount = 0;
		$data = array("app" => $app);
		$data = array_merge($data, $params);
		if(isset(Controller::$globalhooks[$hook]) && is_array(Controller::$globalhooks[$hook])){
			foreach(Controller::$globalhooks[$hook] as $thehook){
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
		if(isset($this->hooks[$hook]) && is_array($this->hooks[$hook])){
			foreach($this->hooks[$hook] as $thehook){
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
		System::log("$hookcount hooks executed");
	}

	/**
	 * The construct instantiates the DB, starts the session and stores a hashed version of the User-Agent string
	 * 
	 * @access private
	 * @return void
	 */

	protected function defineTab($name, $slug, $route, $icon, $admin = false, $index = 0){
		$this->tabsTable[] = array(
			"name" =>	$name,
			"slug" =>	$slug,
			"route" =>	$route,
			"icon" =>	$icon,
			"admin" =>	$admin,
			"index" =>	$index
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
				self::$scripts[$action[$i]][] = array(
					"type" => $type,
					"script" => $script,
					"link" => $link
				);
			}
		}else{
			self::$scripts[$action][] = array(
				"type" => $type,
				"script" => $script,
				"link" => $link
			);
		}
	}
}

abstract class Model{
	private $table = "";

	abstract static function insert();
	abstract static function update();
	abstract static function delete();

	protected function table($name){
		$this->table = $name;
	}

	public function getAll($offset = 0, $limit = 10, $join = false){
		$c_offset = DB::escape($offset);
		$c_limit = DB::escape($limit);	
		$t = $this->table;
		$res = DB::get("SELECT * FROM $t LIMIT $c_limit OFFSET $c_offset", false);
		return $res;
	}

	public function getSingle($id, $join = false){
		$c_id = DB::escape($id);
		$t = $this->table;
		$res = DB::get("SELECT * FROM $t WHERE id = $c_id LIMIT 1");
		return $res;
	}

	private function formatJoin($join){
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

?>