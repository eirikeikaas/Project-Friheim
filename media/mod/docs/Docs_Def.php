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
		$this->info("Docs", "docs", "0.1a");

		// ACTIONS ////////////////////////////////////

		$this->defineAction("list", "list.html", array(
			"pre" => array(
				"callback" => "preList",
				"parameterMap" => array(
					"path" => false
				)
			)
		));

		$this->defineAction("edit", "edit.html", array(
			"save" => array(
				"callback" => "saveEdit",
				"route" => "admin/docs/list"
			),
			"pre" => array(
				"callback" => "preEdit",
				"parameterMap" => array(
					"name" => true
				)
			)
		));

		$this->defineAction("upload", "upload.html", array(
			"save" => array(
				"callback" => "uploadFile",
				"route" => "admin/docs/list"
			),
			"pre" => array(
				"callback" => "preUpload",
				"parameterMap" => array(
					"path" => true
				)
		)));

		$this->defineAction("newfolder", "newfolder.html", array(
			"save" => array(
				"callback" => "doMkdir",
				"route" => "admin/docs/list"
			),
			"pre" => array(
				"callback" => "preMkdir",
				"parameterMap" => array(
					"path" => true
				)
			)
		));

		// TABS ////////////////////////////////////////

		$this->defineTab("Dokumentarkiv", "docs", "admin/docs/list", 6, true);
		$this->defineTab("Last opp", "docs", "admin/docs/upload", 10, true);

		// SCRIPTS /////////////////////////////////////

		//$this->defineScript(array("insert", "edit"), "javascript", "http://www.google.com/jsapi");
		//$this->defineScript(array("insert", "edit"), "javascript", "google.load('jquery', '1.7.2');", false);

		////////////////////////////////////////////////

		/*include_once('Docs.php');
		$this->docs = new Docs("docs");*/
	}

	public static function install(){
		/*$table = System::getConfig('blogTable');
		$table = (empty($table)) ? 'blog' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");*/
	}

	public function preList($app, $params){
		$upbase = System::getConfig('basedir')."/".System::getConfig('uploadDir');
		$path = $this->sanitizePath(base64_decode($params['path']));
		$cwd = !empty($path) && $path !== false ? $path : $upbase;		
		chdir($cwd);
		$scan = scandir('.');
		$files = array();

		foreach($scan as $f){
			if($f == '.' || ($f == '..' && getcwd() == $upbase)){
				continue;
			}
			$stat = System::stat($f);

			$tmp = array('name' => $f);
			$tmp['encoded'] = base64_encode($stat['file']['realpath']);
			$tmp['type'] = $stat['filetype']['type'];
			$tmp['created'] = System::nicetime(date('Y/m/d h:i:s',$stat['time']['ctime']));
			$tmp['modified'] = System::nicetime(date('Y/m/d h:i:s',$stat['time']['ctime']));
			$tmp['rights'] = $stat['perms']['human'];
			$files[] = $tmp;
		}

		System::addVars(array('files' => $files, 'folder' => base64_encode($cwd)));
		chdir(System::getConfig('basedir'));
	}

	public function preEdit($app, $params){
		// TODO: Edit file
	}

	public function saveEdit($app, $params){
		// TODO: Edit file
	}

	public function preUpload($app, $params){
		$this->runHook($app, 'startForm');
		System::addVars(array('cwd' => $params['path'])); 
	}

	public function uploadFile($app, $params){
		set_time_limit(0);
		ini_set('upload_max_filesize', '300mb');
		ini_set('post_max_size', '350mb');
		$this->runHook($app, 'saveForm');
		$unique_id = md5(uniqid(rand(), true));
		$media = @$_FILES['file'];
		$path = $this->sanitizePath(base64_decode($app->request()->post('cwd')));
		if($path !== false){
			$cwd = !empty($path) ? $path : System::getConfig('uploadDir');

			if(!empty($media) && file_exists($media['tmp_name'])){
				$filetype = strrchr($media['name'],'.');
				$new_upload = $cwd."/".$media['name'];
				$the_upload = copy($media['tmp_name'], $new_upload);

				if($the_upload !== false){
					$uploaded_file   = $new_upload;
					@chmod($uploaded_file, 0777);
					$this->runHook($app, 'saveFile');
					System::setRoute('admin/docs/list/'.base64_encode($path));
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function preMkdir($app, $params){
		$this->runHook($app, 'startForm');
		System::addVars(array('cwd' => $params['path']));
	}

	public function doMkdir($app, $params){
		$this->runHook($app, 'saveForm');
		$path = $this->sanitizePath(base64_decode($app->request()->post('cwd')));
		
		if($path !== false){
			if(mkdir($path."/".$app->request()->post('folder'))){
				System::setRoute('admin/docs/list/'.base64_encode($path));
				return true;
			}
		}else{
			return false;
		}
	}

	private function sanitizePath($path){
		$upbase = System::getConfig('basedir')."/".System::getConfig('uploadDir');
		$path = str_replace('..', '', $path);

		return strpos($path, $upbase) === 0 ? $path : false;

	}
}