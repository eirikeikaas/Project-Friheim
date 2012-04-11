<?php	error_reporting(E_ALL ^ E_NOTICE);

		include_once('media/lib/DB.php');
		include_once('media/lib/Auth.php');
		
		$d = new DB();
		$a = new Auth();
		
		function get($n, $v){
			return (isset($_GET[$n]) && $_GET[$n] === $v);
		}
		
		if(get('a','login')){
			$a->login($_POST['brid'], $_POST['pswd']);
		}if(get('a','logout')){
			$a->logout();
		}
		
		$_isl = $a->isLoggedIn();
		$_isl_adm = $a->isAdmin();
		$user = Auth::userData();
		
		$js = array();
		
		function dnd(){
			echo error_get_last();
			die(var_export(debug_backtrace()));
		}
		
		function getPage($page = ""){
			if($page != ""){
				$page = '/home/therace/public_html/login/media/inc/'.basename($page).'.php';
			
				if(file_exists($page)){
					ob_start();
					include_once($page);
					return ob_get_clean();
				}else{
					ob_start(); 
					include_once('/home/therace/public_html/login/media/inc/404.php');
					return ob_get_clean();
				}
			}else{
				return false;
			}
		}
		
		if(($p = getPage($_GET['p'])) === false){
			$p = getPage('new');
		}
		
		?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><?=DB::_get('backendname')?></title>
		<meta name="viewport" content="user-scalable=0, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
		<link rel="stylesheet" href="media/css/style.css">
		<!--[if lt IE 9]><link rel="stylesheet" href="media/css/ielt9.css"><![endif]-->
		<?php
			foreach($GLOBALS['js'] as $j){
				echo $j."\n";
			}
		?>
	</head>
	<body>
		<div id="modalcontainer">
			<div id="modal">
			</div>
		</div>
		<div id="header">
			<div id="title"><?=DB::_get('backendname')?></div>
			<a href="/" class="tab" id="tabfront">Brettet</a>
			<?php if($_isl){ ?><a href="?p=new" class="tab" id="tabadd">Legg til</a>
			<a href="?p=edit" class="tab" id="tabedit">Historikk</a><?php } ?>
			<?php if($_isl_adm){ ?><a href="?p=users" class="tab" id="tabusers">Brukere</a>
			<a href="?p=stats" class="tab" id="tabstats">Statistikk</a>
			<a href="?p=settings" class="tab" id="tabsettings">Innstillinger</a>
			<?php } ?>
			<?php if($_isl){ ?>
				<div id="me" class="right">
					<div id="box"><?= $user['name']; ?>
						<ul id="drop">
							<li><a href="?p=users&a=edit&id=<?=$user['id']?>&me=true">Konto</a></li>
							<li><a href="?a=logout" id="logout">Logg ut</a></li>
						</ul>
					</div>
				</div>
			<?php } ?>
		</div>
		<?php if($_isl){ ?>
		<div id="content">
			<?
				echo $p;
			?>
		</div>
		<?php }else{ ?>
		<form id="login" method="post" action="?a=login">
			<h2>Logg inn</h2>
			<? if(get('e','login')){ ?><div class="msg error">Feil brukernavn eller passord</div><? } ?>
			<div id="inputcontainer">
				<div class="line"><label for="brid">Brukernavn:</label><input type="text" name="brid" id="brid"></div>
				<div class="line"><label for="pswd">Passord:</label><input type="password" name="pswd" id="pswd"></div>
			</div>
			<input type="submit" value="Logg inn">
		</form>
		<?php }
			ob_end_flush();
		?>
	<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-29772183-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

	</script>
	</body>
</html>