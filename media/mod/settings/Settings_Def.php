<?php

/**
 * undocumented class
 *
 * @package default
 * @implements Module_Definition
 * @author 
 **/

class Settings_Def extends Controller{
	private $users = false;

	public function __construct(){
		$this->info("Settings", "settings", "0.1a");
		$this->loadHelper();

		// ACTIONS ////////////////////////////////////

		$this->defineAction("edit", "edit.html", array(
			"pre" => "preEdit",
			"save" => array(
				"callback" => "saveEdit",
				"route" => "admin/settings/edit"
			)
		));

		///////////////////////////////////////////////

		$this->defineTab("Innstillinger", "settings", "admin/settings/edit", 4, true, 999);
	}

	public static function install(){
		/*$table = System::getConfig('usersTable');
		$table = (empty($table)) ? 'users' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
		*/
	}

	public function preEdit($app, $params){
		$this->runHook($app, "startForm");
		$settings = ORM::for_table(System::getConfig('settingstable'))->find_many();
		System::addVars(array('settings' => $settings));
	}

	public function saveEdit($app, $params){
		$this->runHook($app, "saveForm");
		$posts = $app->request()->post();
		
		foreach($posts as $postk => $postv){
			$row = ORM::for_table(System::getConfig('settingstable'))->where('key', $postk)->find_one();
			if($row !== false){
				$row->value = $postv;
				$row->save();
			}
		}
		
		System::setMessage($app, "Innstillingene ble lagret");
	}
}