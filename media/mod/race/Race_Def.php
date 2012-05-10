<?php

/**
 * undocumented class
 *
 * @package default
 * @implements Module_Definition
 * @author 
 **/

class Race_Def extends Controller{
	private $race = false;

	public function __construct(){
		$this->info("Race", "race", "0.1a");

		// ACTIONS ////////////////////////////////////

		$this->defineAction("stats", "stats.html", array(
			"pre" => "preStats"
		));

		$this->defineAction("history", "history.html", array(
			"pre" => array(
				"callback" => "preHistory",
				"parameterMap" => array(
					"id" => true
				)
			),
		));

		$this->defineAction("new", "new.html", array(
			"save" => "saveNew"
		));

		///////////////////////////////////////////////

		$this->defineTab("Legg til", "race", "admin/race/new", 13, true, -1);
		$this->defineTab("Historikk", "race", "admin/race/history", 1, true);
		$this->defineTab("Statistikk", "race", "admin/race/stats", 7, true);

		///////////////////////////////////////////////

		$this->addFilter("users", "list", function($data){
			return $data;
		}, true);

		//include_once('Users.php');
		//$this->race = ORM::for_table('race_points');
	}

	public static function install(){
		/*$table = System::getConfig('usersTable');
		$table = (empty($table)) ? 'users' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
		*/
	}

	public function preStats($app, $params){
		$usersorm = $this->race->find_many();
		$users = array();
		foreach($usersorm as $user){
			$users[] = $user->as_array();
		}
		print_r($users);
		System::addVars(array('users' => $users));
	}

	public function preHistory($app, $params){
		$this->runHook($app, 'startForm');
		$post = $this->race->find_one($params['id']);
		System::addVars(array('post' => $post));
	}

	/*public function saveEdit($app, $params){
		$this->runHook($app, 'saveForm');
		if($this->race->update($app->request()->post('id'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret");
			return true;
		}else{
			System::setMessage($app, "Noe gikk galt under lagringen av artikkelen", false);
			System::log("Could not save?");
			return false;
		}
	}*/

	public function saveNew($app, $params){
		$this->runHook($app, 'saveForm');
		if($this->race->update($app->request()->post('id'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret");
			return true;
		}else{
			System::setMessage($app, "Noe gikk galt under lagringen av artikkelen", false);
			System::log("Could not save?");
			return false;
		}
	}
}