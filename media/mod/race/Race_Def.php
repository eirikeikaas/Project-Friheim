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
		$this->race = $this->loadHelper();

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
			"pre" => "preNew",
			"save" => array(
				"callback" => "saveNew",
				"route" => "admin/race/new"
			)
		));

		///////////////////////////////////////////////

		$this->defineTab("Legg til", "race", "admin/race/new", 13, true, -1);
		$this->defineTab("Historikk", "race", "admin/race/history", 1, true);
		$this->defineTab("Statistikk", "race", "admin/race/stats", 7, true);

		///////////////////////////////////////////////

		/*$this->addFilter("users", "list", function($data){
			return $data;
		}, true);
		
		///////////////////////////////////////////////

		$this->addHook("users", "insert", function($data){
			return true;
		});*/
		
		///////////////////////////////////////////////
		
		$this->defineScript(array("new", "stats"), "javascript", "https://www.google.com/jsapi");
		$this->defineScript("new", "javascript", "google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Year', 'Sales'],
          ['2004',  1000],
          ['2005',  1170],
          ['2006',  660],
          ['2007',  1030],
          ['2004',  1000],
          ['2005',  1170],
          ['2006',  660],
          ['2007',  1030],
          ['2004',  1000],
          ['2005',  1170],
          ['2006',  660],
          ['2007',  1030],
          ['2004',  1000],
          ['2005',  1170],
          ['2006',  660],
          ['2007',  1030],
          ['2004',  1000],
          ['2005',  1170],
          ['2006',  660],
          ['2007',  1030],
          ['2004',  1000],
          ['2005',  1170],
          ['2006',  660],
          ['2007',  1030],
          ['2004',  1000],
          ['2005',  1170],
          ['2006',  660],
          ['2007',  1030]
        ]);

        var options = {
        	legend: { position: 'none' },
        	chartArea:{left:0,top:0,width:'100%',height:'100%'},
        	hAxis: { textPosition: 'none' },
        	vAxis: { textPosition: 'none', baselineColor: '#d0d0d0' },
        	colors:['#30aec0'],
        	pointSize: 4
        };

        var chart = new google.visualization.AreaChart(document.getElementById('graph'));
        chart.draw(data, options);
      }", false);
	}

	public static function install(){
		/*$table = System::getConfig('usersTable');
		$table = (empty($table)) ? 'users' : $table;
		DB::set("CREATE TABLE IF NOT EXISTS $table (id INT NOT NULL AUTO_INCREMENT ,title VARCHAR(60) NULL ,author INT NULL , timestamp DATETIME NULL , body TEXT NULL , PRIMARY KEY (id) ,UNIQUE INDEX id_UNIQUE (id ASC) )");
		*/
	}

	public function preStats($app, $params){
		/*$usersorm = $this->race->find_many();
		$users = array();
		foreach($usersorm as $user){
			$users[] = $user->as_array();
		}
		print_r($users);
		System::addVars(array('users' => $users));*/
	}

	public function preHistory($app, $params){
		$this->runHook($app, 'startForm');
		/*$post = $this->race->find_one($params['id']);
		System::addVars(array('post' => $post));*/
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
	
	public function preNew($app, $params){
		$this->runHook($app, 'startForm');
		
	}

	public function saveNew($app, $params){
		$this->runHook($app, 'saveForm');
		$post = $app->request()->post();
		
		foreach($post as $k => $v){
			if(strpos($k, 'week') === 0){
				$this->race->insert($post['user'], $v, (int)substr($k, -1), $post['month'], 0);
			}
		}
		
	}
}