<?php

/**
 * undocumented class
 *
 * @package default
 * @implements Module_Definition
 * @author 
 **/

class Groups_Def extends Controller{
	private $groups = false;

	public function __construct(){
		$this->info("Groups", "groups", "0.1a");

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

		$this->defineTab("Grupper", "groups", "admin/groups/list", 2, true, 6);

		include_once('Groups.php');
		$this->groups = new Groups("groups");
	}

	public static function install(){
		$table = System::getConfig('usersTable');
		$table = (empty($table)) ? 'users' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
	}

	public function preList($app, $params){
		/*$users = $this->users->getAll(0, 10, false);
		System::addVars(array('users' => $users));*/
	}

	public function preEdit($app, $params){
		$this->runHook($app, 'startForm');
		/*$post = $this->users->getSingle($params['id']);
		System::addVars(array('post' => $post));*/
	}

	public function saveEdit($app, $params){
		$this->runHook($app, 'saveForm');
		/*if($this->users->update($app->request()->post('id'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret");
			return true;
		}else{
			System::setMessage($app, "Noe gikk galt under lagringen av artikkelen", false);
			System::log("Could not save?");
			return false;
		}*/
	}

	public function saveInsert($app, $params){
		$this->runHook($app, 'saveForm');
		/*if($this->users->update($app->request()->post('id'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret");
			return true;
		}else{
			System::setMessage($app, "Noe gikk galt under lagringen av artikkelen", false);
			System::log("Could not save?");
			return false;
		}*/
	}
}