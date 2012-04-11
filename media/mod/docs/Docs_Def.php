<?php

/**
 * undocumented class
 *
 * @package default
 * @implements Module_Definition
 * @author 
 **/

class Docs_Def extends Controller{
	private $blog = false;

	public function __construct(){
		$this->name("Docs", "docs");

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
					"name" => true
				)
			)
		));

		$this->defineAction("upload", "upload.html", array(
			"save" => "uploadFile",
			"pre" => "preUpload"
		));

		// TABS ////////////////////////////////////////

		$this->defineTab("Dokumentarkiv", "docs", "admin/docs/list", 6, true);
		$this->defineTab("Last opp", "docs", "admin/docs/upload", 10, true);

		// SCRIPTS /////////////////////////////////////

		$this->defineScript(array("insert", "edit"), "javascript", "http://www.google.com/jsapi");
		$this->defineScript(array("insert", "edit"), "javascript", "google.load('jquery', '1.7.2');", false);

		////////////////////////////////////////////////

		include_once('Docs.php');
		$this->docs = new Docs("docs");
	}

	public static function install(){
		$table = System::getConfig('blogTable');
		$table = (empty($table)) ? 'blog' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
	}

	public function preList($app, $params){
		//$posts = $this->blog->getAll(0, 10);

		//System::addVars(array('posts' => $posts));
	}

	public function preEdit($app, $params){
		//$post = $this->blog->getSingle($params['id'], false);
		//System::addVars(array('post' => $post));
	}

	public function saveEdit($app, $params){
		System::log("hmmmmm");
		if($this->blog->update($app->request()->post('id'),$app->request()->post('blogbody'), $app->request()->post('title'))){
			System::setMessage($app, "Artikkelen ble lagret", true);
			System::log("yay");
		}else{
			System::log("nay");
		}
		return true;
	}

	public function preUpload($app, $params){

	}

	public function uploadFile($app, $params){     
		$unique_id = md5(uniqid(rand(), true));
		$media = $_FILES['photo']['name'];
		$filetype = strrchr($media,'.');
		$new_upload = System::getConfig('uploadDir')."$unique_id.$filetype";
		$the_upload = copy($_FILES['photo']['tmp_name'], $new_upload);
		$uploaded_file   = $new_upload;
		@chmod($uploaded_file, 0777);
		$app -> redirect('/upload/');
	}
}