<?
$a = new Auth();
if($a->isAdmin()){
if(isset($_GET['a']) && $_GET['a'] == 'save'){
	if(Auth::key($_POST['key'])){
		unset($_POST['key']);
		$pkey = array_keys($_POST);
		$plen = count($_POST);
		for($i=0;$i<$plen;$i++){
			DB::_set($pkey[$i],$_POST[$pkey[$i]]);
		}
		
		header('location: index.php?p=settings&msg=saved');
	}
}
?>
<form action="index.php?p=settings&amp;a=save" method="post" class="block">
	<h2>Innstillinger</h2>
	<?php if($_GET['msg'] == "saved"){ ?><div class="msg success">Instillingene ble lagret</div><? } ?>
	<input type="hidden" name="key" value="<?=Auth::key()?>">
	<div class="line"><label for="sitename">Navn på siden:</label><input type="text" name="sitename" id="sitename" value="<?=DB::_get('sitename')?>"></div>
	<div class="line"><label for="backendname">Navn på panel:</label><input type="text" name="backendname" id="backendname" value="<?=DB::_get('backendname')?>"></div>
	<div class="line"><hr /></div>
	<div class="line"><label for="visual_green_limit">Nedre grense for grønnfarge(%):</label><input type="text" name="visual_green_limit" id="visual_green_limit" value="<?=DB::_get('visual_green_limit')?>"></div>
	<div class="line"><label for="visual_red_limit">Øvre grense for rødfarge(%):</label><input type="text" name="visual_red_limit" id="visual_red_limit" value="<?=DB::_get('visual_red_limit')?>"></div>
	<div class="line"><hr /></div>
	<div class="line"><label for="instructions">Instruksjoner:</label><textarea name="instructions" rows="10"><?=DB::_get('instructions')?></textarea></div>
	<div class="line"><hr /></div>
	<div class="line"><label>"Justert verdi"<br/>mail-tekst:</label><textarea name="" rows="10"></textarea></div>
	<div class="footer"><input type="submit" value="Lagre"><input type="button" class="grey" value="Avbryt"></div>
<form>
<? } ?>