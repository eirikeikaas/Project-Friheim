<?
$GLOBALS['js'][] = '<script src="media/js/pm.js" type="text/javascript"></script>';
$GLOBALS['js'][] = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>';
$GLOBALS['js'][] = '<script type="text/javascript" src="media/js/useredit.js"></script>';
 ?>
<form class="block" action="?p=users&a=save" method="post">
	<input type="hidden" name="new" value="true">
	<input type="hidden" name="key" value="<?=Auth::key()?>">
	<h2>Opprett ny bruker</h2>
	<?php if($_GET['msg'] == "pswderrfi"){ ?><div class="msg error">For å endre et passord så du fylle ut alle passord feltene. Og alle passordfeltene må være minimum 6 tegn</div><? } ?>
	<?php if($_GET['msg'] == "pswderrmm"){ ?><div class="msg error">De nye passordene er ikke identiske</div><? } ?>
	<?php if($_GET['msg'] == "gen"){ ?><div class="msg error">Kunne ikke lagre brukeren</div><? } ?>
	<div class="line"><label for="fname">Fornavn: </label><input id="fname" name="fname" type="text" value=""></div>
	<div class="line"><label for="lname">Etternavn: </label><input id="lname" name="lname" type="text" value=""></div>
	<div class="line"><label for="email">Email: </label><input id="email" name="email" type="text" value=""></div>
	<div class="line"><label for="dept">Avdeling: </label>
		<select name="dept">
			<?
			$data = DB::get("SELECT name, id FROM tr_dept");
			$dlen = count($data);
			for($i=0;$i<$dlen;$i++){
			?>
			<option value="<?=$data[$i]['id']?>"><?=$data[$i]['name']?></option>
			<?
			}
			?>
		</select>
	</div>
	<div class="line"><hr /></div>
	<div class="line"><label for="newpswd">Passord: </label><input autocomplete="off" name="newpswd" id="newpswd" type="password" value=""></div>
	<div class="line"><label for="cnfpswd">Bekreft passord: </label><input autocomplete="off" name="cnfpswd" id="cnfpswd" type="password" value=""></div>
	<div class="line"><label>Passordstyrke: </label><div id="pswdstrength" class="none"></div></div>
	<div class="line"><hr /></div>
	<div class="line"><label for="total">Totalbudsjett: </label><button type="button" id="refreshbudget"><img src="media/img/refresh.png" alt="refresh" width="16" height="16" /></button><input name="total" id="total" type="text" value=""></div>
	<div id="budgeteditor">
		<div><label for="Januar">Januar</label><input name="Januar" id="Januar" type="text" value=""><div class="clear"></div></div>
		<div><label for="Februar">Februar</label><input name="Februar" id="Februar" type="text" value=""><div class="clear"></div></div>
		<div><label for="Mars">Mars</label><input name="Mars" id="Mars" type="text" value=""><div class="clear"></div></div>
		<div><label for="April">April</label><input name="April" id="April" type="text" value=""><div class="clear"></div></div>
		<div><label for="Mai">Mai</label><input name="Mai" id="Mai" type="text" value=""><div class="clear"></div></div>
		<div><label for="Juni">Juni</label><input name="Juni" id="Juni" type="text" value=""><div class="clear"></div></div>
		<div><label for="Juli">Juli</label><input name="Juli" id="Juli" type="text" value=""><div class="clear"></div></div>
		<div><label for="August">August</label><input name="August" id="August" type="text" value=""><div class="clear"></div></div>
		<div><label for="September">September</label><input name="September" id="September" type="text" value=""><div class="clear"></div></div>
		<div><label for="Oktober">Oktober</label><input name="Oktober" id="Oktober" type="text" value=""><div class="clear"></div></div>
		<div><label for="November">November</label><input name="November" id="November" type="text" value=""><div class="clear"></div></div>
		<div><label for="Desember">Desember</label><input name="Desember" id="Desember" type="text" value=""><div class="clear"></div></div>
	</div>
	<div class="footer"><input type="submit" value="Lagre"/><input type="button" onclick="javascript:document.location = 'index.php?p=users';" class="grey" value="Avbryt" /></div>
</form>