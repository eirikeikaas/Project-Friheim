<?php

function routeHandler($route, $app){
	$settings = System::loadEndpoint("Settings", "SE", true);
	
	switch($route){
		case "":
			include_once('static/media/tpl/index.php');
			break;
		case (preg_match('/^ajax.*/', $route) ? true : false) :
			include_once('static/media/ajax/endpoint.php');
			die();
			break;
		default:
			echo "FOUR-O-FOUR! PAGE NOT FOUUUUND! :O";
	}
}

?>
