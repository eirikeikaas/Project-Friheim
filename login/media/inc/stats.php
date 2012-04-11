<?

$a = new Auth();
if($a->isAdmin()){
$GLOBALS['js'][] = '<script type="text/javascript" src="media/js/raphael.js"></script>';
$GLOBALS['js'][] = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>';
$GLOBALS['js'][] = '<script type="text/javascript" src="media/js/stats.js"></script>';

?>
<div class="block">
	<div id="graphbox"></div>
</div>
<div class="block">
</div>
<? }else{ header('location: index.php'); } ?>