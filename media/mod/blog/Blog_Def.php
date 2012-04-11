<?php

/**
 * undocumented class
 *
 * @package default
 * @implements Module_Definition
 * @author 
 **/

class Blog_Def extends Controller{
	private $blog = false;

	public function __construct(){
		$this->name("Blog", "blog");

		// ACTIONS ////////////////////////////////////

		$this->defineAction("list", "list.html", array(
			"pre" => "preList"
		));

		$this->defineAction("edit", "edit.html", array(
			"save" => array(
				"callback" => "saveEdit",
				"route" => "admin/blog/list"
			),
			"pre" => array(
				"callback" => "preEdit",
				"parameterMap" => array(
					"id" => true
				)
			)
		));

		$this->defineAction("insert", "insert.html", array(
			"pre" => "preInsert",
			"save" => array(
				"callback" => "saveInsert",
				"route" => "admin/blog/list"
			)
		));

		// TABS ////////////////////////////////////////

		$this->defineTab("Artikler", "blog", "admin/blog/list", 8, true);

		// SCRIPTS /////////////////////////////////////

		$this->defineScript(array("insert", "edit"), "javascript", "http://www.google.com/jsapi");
		$this->defineScript(array("insert", "edit"), "javascript", "google.load('jquery', '1.7.2');", false);

		////////////////////////////////////////////////

		include_once('Blog.php');
		$this->blog = new Blog("blog");
	}

	public static function install(){
		$table = System::getConfig('blogTable');
		$table = (empty($table)) ? 'blog' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
	}

	public function preList($app, $params){
		$posts = $this->blog->getAll(0, 10);
		System::addVars(array('posts' => $posts));
	}

	public function preEdit($app, $params){
		$this->runHook($app, 'startForm');
		$post = $this->blog->getSingle($params['id'], false);
		System::addVars(array('post' => $post));
	}

	public function saveEdit($app, $params){
		$this->runHook($app, 'saveForm');

		if($this->blog->update($app->request()->post('id'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret", true);
			return true;
		}else{
			System::setMessage($app, "Noe gikk galt under lagringen av artikkelen", true);
			System::log("Could not save?");
			return false;
		}
	}

	public function preInsert($app, $params){
		$this->runHook($app, 'startForm');
		// Empty
	}

	public function saveInsert($app, $params){
		$this->runHook($app, 'saveForm');
		if($this->blog->insert($app->request()->post('author'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret", true);
			return true;
		}else{
			System::setMessage($app, "Noe gikk galt under lagringen av artikkelen", true);
			System::log("Could not save?");
			return false;
		}
	}
}