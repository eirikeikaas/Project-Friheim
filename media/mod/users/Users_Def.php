<?php

/**
 * undocumented class
 *
 * @package default
 * @implements Module_Definition
 * @author 
 **/

class Users_Def extends Controller{
	private $users = false;

	public function __construct(){
		$this->info("Users", "users", "0.1a");

		// ACTIONS ////////////////////////////////////

		$this->defineAction("list", "list.html", array(
			"pre" => "preList"
		));

		$this->defineAction("edit", "edit.html", array(
			"pre" => array(
				"callback" => "preEdit",
				"parameterMap" => array(
					"id" => true
				)
			),
			"save" => "saveEdit"
		));

		$this->defineAction("insert", "insert.html", array(
			"save" => "saveInsert"
		));

		///////////////////////////////////////////////

		$this->defineTab("Brukere", "users", "admin/users/list", 11, true, 5);

		//include_once('Users.php');
		$this->users = ORM::for_table('users');
	}

	public static function install(){
		/*$table = System::getConfig('usersTable');
		$table = (empty($table)) ? 'users' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
		*/
	}

	public function preList($app, $params){
		$usersorm = $this->users->find_many();
		$users = array();
		foreach($usersorm as $user){
			$array = $user->as_array();
			$this->applyFilter($app, 'list', $array);
			$users[] = $array;
		}
		System::addVars(array('users' => $users));
	}

	public function preEdit($app, $params){
		$this->runHook($app, 'startForm');
		$post = $this->users->find_one($params['id']);
		System::addVars(array('post' => $post));
	}

	public function saveEdit($app, $params){
		$this->runHook($app, 'saveForm');
		if($this->users->update($app->request()->post('id'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret");
			return true;
		}else{
			System::setMessage($app, "Noe gikk galt under lagringen av artikkelen", false);
			System::log("Could not save?");
			return false;
		}
	}

	public function saveInsert($app, $params){
		$this->runHook($app, 'saveForm');
		if($this->users->update($app->request()->post('id'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret");
			return true;
		}else{
			System::setMessage($app, "Noe gikk galt under lagringen av artikkelen", false);
			System::log("Could not save?");
			return false;
		}
	}
}