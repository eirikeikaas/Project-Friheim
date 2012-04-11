<?

$a = new Auth();
$db = &DB::getInstance();
$user = Auth::userData();

if($a->isAdmin()){
if(isset($_GET['a']) && $_GET['a'] == 'delete'){
	if(Auth::key($_POST['key']) && $_POST['delete'] === "SLETT"){
		$c_pswd = Auth::password($_POST['pswd']);
		$c_user = $db->escape_string($_POST['user']);
		if(DB::get("SELECT id FROM tr_users WHERE password = '{$c_pswd}' AND id = {$user['id']}")){
			DB::set("DELETE FROM tr_points WHERE user = {$c_user}");
			DB::set("DELETE FROM tr_userval WHERE user = {$c_user}");
			DB::set("DELETE FROM tr_uservalmonth WHERE user = {$c_user}");
			DB::set("DELETE FROM tr_users WHERE id = {$c_user}");
			
			header('location: index.php?p=users&msg=done');
		}
	}
}
?>
<form method="post" action="index.php?p=delete&a=delete" class="block">
	<h2>Slett en bruker</h2>
	<input type="hidden" name="key" value="<?=Auth::key();?>">
	<? if(!isset($_GET['id'])){ ?><div class="line"><label for="user">Velg en bruker:</label>
		<select name="user" id="user">
		<? $data = DB::get("SELECT id, CONCAT(firstname,' ',lastname) AS name FROM tr_users");
			$dlen = count($data);
			for($i=0;$i<$dlen;$i++){
				?>
				<option value="<?=$data[$i]['id']?>"><?=$data[$i]['name']?></option>
				<?
			}
		?>
		</select>
	</div><? }else{ ?>
		<input type="hidden" name="user" value="<?=$_GET['id']?>">
		<? } ?>
	<div class="line"><label for="pswd">Ditt passord:</label><input type="password" name="pswd"></div>
	<div class="line"><label for="delete">Skriv "SLETT":</label><input type="text" name="delete"></div>
	<div class="footer"><input type="submit" value="Slett bruker" onclick="return confirm('Er du sikker på at du vil slette denne brukeren?');"><input type="button" value="Avbryt" class="grey" onclick="document.location = 'index.php';"></div>
</form>
<? } ?>