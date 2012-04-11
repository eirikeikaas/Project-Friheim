<?php

error_reporting(E_ALL);
ini_set('display_errors',true);

//////////////////////////////////////////////////////////////////

define('SYS_CONFIG', 'media/lib/config.ini');

//////////////////////////////////////////////////////////////////

include_once('media/lib/DB.php');
include_once('media/lib/Auth.php');
include_once('media/lib/System.php');
include_once('media/lib/Slim/Slim.php');
include_once('media/lib/Slim/Views/TwigView.php');

TwigView::$twigDirectory = __DIR__ . '/media/lib/Twig/lib/Twig/';
TwigView::$twigOptions = array(/*'cache' => 'cache',*/ 'debug' => System::isDebug());

// Read config.ini
$sys = new System(SYS_CONFIG);

// Auth
$a = new Auth();

// Initiate Slim
$app = new Slim(array(
   'view' => new TwigView,
   'templates.path' => '.'
));

// Slim-Route
Slim_Route::setDefaultConditions(array(
    'module' => '[a-z]{3,}',
    'action' => '[a-z]{3,}',
    'parameters' => '([a-zA-Z0-9]{1,}(:){0,1}){1,}'
));

// Set auth-instance
$sys->authInstance($a);
$prefix = System::getConfig('prefix');

////////////////// AUTO MODULE-DISCOVERY /////////////////////////

$dir = scandir("media/mod");
$dirlen = count($dir);

for($i=0;$i<$dirlen;$i++){
	if($dir[$i] == "." || $dir[$i] == ".."){ continue; }
	$sys->registerModule($dir[$i]);
}

//////////////////////////////////////////////////////////////////

// Handle static index
$app->get('/', function(){
	include('static/index.html');
});

// Handle login
$app->post('/admin/login(/)', function() use($a, $app, $prefix){
	if($a->login($app->request()->post('brid'),$app->request()->post('pswd')) !== true){
		$app->flash("error","Brukernavn eller passord er ikke rett");
	}
	$app->redirect($prefix.'/admin');
});

//Handle logout
$app->get('/admin/logout(/)', function() use($a, $app, $prefix){
	$a->logout();
	$app->redirect($prefix.'/');
});

//Handle admin index
$app->get('/admin(/)', function() use($a, $app){
	if(!$a->isLoggedIn()){
		$app->render(System::getConfig('logintemplate'), System::vars());
	}else{
		$app->render(System::getConfig('admintemplate'), System::vars());
	}
});

// Handle module
$app->map('/admin/:module/:action(/:parameters(/))', function($module, $action, $parameters = false) use($a, $app, $sys, $prefix){
	if(!$a->isLoggedIn()){
		$app->render(System::getConfig('logintemplate'), System::vars());
	}else{
		$_module = $module;
		$module = $sys->getModule($module);
		if($module !== false){
			if($parameters == "save"){
				$app->pass();
			}
			if($module->doAction("pre", $action, $app, $parameters) !== false){
				if($tpl = $module->getTemplate($action)){
					$app->render($tpl, System::vars());
				}else{
					if(!System::getRoute($app)){
						$app->redirect($prefix.'/');
					}
				}
			}else{
				$app->notfound();
			}
		}else{
			$app->render(System::getConfig("errortemplate"));
		}
	}
})->via('GET', 'POST');

// Handle module save
$app->post('/admin/:module/:action/save(/)', function($module, $action) use($a, $app, $sys, $prefix){
	if(!$a->isLoggedIn()){
		$app->render(System::getConfig('logintemplate'), System::vars());
	}else{
		$_module = $module;
		$module = $sys->getModule($module);
		if($module !== false){
			if($module->doAction("save", $action, $app) === false){
				$app->render(System::getConfig("errortemplate"), array("msg" => "failed to complete action"));
			}else{
				if(!System::getRoute($app)){
					$app->redirect($prefix.'/');
				}
			}
			$app->render(System::getConfig("errortemplate"));
		}else{
			$app->render(System::getConfig("errortemplate"));
		}
	}
});

$app->run();

?>