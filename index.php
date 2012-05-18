<?php

error_reporting(E_ALL);
ini_set('display_errors',true);

//////////////////////////////////////////////////////////////////

define('SYS_CONFIG', 'media/lib/config.ini');

//////////////////////////////////////////////////////////////////

include_once('media/lib/Idiorm.php'); // *NYI*
include_once('media/lib/DB.php');
include_once('media/lib/Auth.php');
include_once('media/lib/System.php');
include_once('media/lib/Slim/Slim.php');
include_once('media/lib/Slim/Views/TwigView.php');

TwigView::$twigDirectory = __DIR__ . '/media/lib/Twig/lib/Twig/';
TwigView::$twigOptions = array(/*'cache' => 'cache',*/ 'debug' => System::isDebug(), 'auto_reload' => System::isDebug(), 'optimizations' => 0);
TwigView::$twigExtensions = array('Twig_Debug_Extension');

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
    'parameters' => '([a-zA-Z0-9\=]{1,}(:){0,1}){1,}'
));

// Set auth-instance
$sys->authInstance($a);
$prefix = System::getConfig('prefix');

////////////////// AUTO MODULE-DISCOVERY /////////////////////////

System::moduleDiscovery();

//////////////////////////////////////////////////////////////////

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
		$app->render(System::getConfig('logintemplate'), System::vars($app));
	}else{
		$app->render(System::getConfig('admintemplate'), System::vars($app));
	}
});

// Handle module
$app->map('/admin/:module/:action(/:parameters(/))', function($module, $action, $parameters = false) use($a, $app, $sys, $prefix){
	if(!$a->isLoggedIn()){
		$app->render(System::getConfig('logintemplate'), System::vars($app));
	}else{
		$_module = $module;
		$module = $sys->getModule($module);
		if($module !== false){
			if($parameters == "save"){
				$app->pass();
			}
			if($module->doAction("pre", $action, $app, $parameters) !== false){
				if($tpl = $module->getTemplate($action)){
					$app->render($tpl, System::vars($app));
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
				$app->render(System::getConfig("errortemplate"), System::vars($app, array("msg" => "failed to complete action")));
			}else{
				if(!System::getRoute($app)){
					$app->redirect($prefix.'/');
				}
			}
		}else{
			$app->render(System::getConfig("errortemplate"));
		}
	}
});

// Handle static index
$app->get('/(:route)', function($route = "") use($app){
	
	$static = System::getConfig("staticindex");
	$static = !empty($static) && file_exists($static) ? $static : 'static/index.html';
	
	include($static);
	
	/////////////////////////////////////////////////////////////
	
	$cb = System::getConfig("staticmanager");
	if(!empty($cb)){
		if(is_callable($cb)){
			$route = substr($route, -1) == "/" ? substr($route, 0, -1) : $route; 
			if($cb instanceof Closure){
				$cb($route, $app);
			}else{
				call_user_func_array($cb, array($route, $app));
			}
		}else{
			System::log("Callback for static is not callable!");
		}
	}
	
})->conditions(array('route' => '.*'));

$app->run();

?>