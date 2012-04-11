<?
$a = new Auth();
if($a->isAdmin()){
?>
<form action="index.php?p=blog&amp;a=save" method="post" class="block">
	<h2>Ny artikkel</h2>
	<input type="hidden" name="key" value="<?=Auth::key()?>">
	<input type="hidden" name="new" value="false">
	<div class="line"><label for="title">Tittel:</label><input type="text" name="title" id="blogtitle" value=""></div>
	<div class="line"><label for="author">Forfatter:</label><input disabled type="text" name="blogauthor" id="author" value=""></div>
	<div class="line"><hr /></div>
	<div class="line"><label for="blogbody">Tekst:</label><textarea name="blogbody" rows="10"><?=DB::_get('instructions')?></textarea></div>
	<div class="footer"><input type="submit" value="Lagre"><input type="button" class="grey" value="Avbryt"></div>
<form>
<? } ?>