<?
$me = Auth::userData();
$user = Auth::userData($_GET['id']);
$a = new Auth();
if($a->ifAdmin($me['id'] === $user['id'],true)){
$GLOBALS['js'][] = '<script src="media/js/pm.js" type="text/javascript"></script>';
$GLOBALS['js'][] = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>';
$GLOBALS['js'][] = '<script type="text/javascript" src="media/js/useredit.js"></script>';

if(isset($_GET['getmonth'],$_GET['month'])){
$dim = date('t',$date);
			?>
			<input type="hidden" name="dim" value="<?=$dim?>">
	<table class="small">
		<?php
			$ditm = date('t',mktime(0,0,0,$_GET['month']));
			for($i=0;$i<$ditm;$i++){
				$date = mktime(0,0,0,$_GET['month'],$i+1);
				$weekend = (date('N',$date)>=6)?'weekend':'';
				$even = ($i%2)?'even':'';
				$c_month = DB::escape($_GET['month']);
			
				$val = DB::get("SELECT id, value, correction_value FROM tr_points WHERE user = {$user['id']} AND month = {$c_month} AND day = ".($i+1));
				if(date('N',$date)==1){
				?>
					<tr class="newweek" >
						<td>&nbsp;</td>
					</tr>
				<?
				}
				?>
				<tr class="<?=$even.' '.$weekend?>">
					<td><?=$i+1?>.</td>
					<td><?=(strlen($val['value']) > 0)?$val['value'].$user['unit']:""?></td>
					<td><input autofocus tabindex="0" class="inputsum" name="value_<?=$i+1?>_<?=$val['id']?>_<?=$c_month?>" type="text" value="<?=$val['correction_value']?>"></td>
				</tr>
		<?php } ?>
	</table>
	<?
	die();
}
?>
<form class="block" action="?p=users&amp;a=save&amp;id=<?=$user['id']?>" method="post">
	<input type="hidden" name="new" value="false">
	<input type="hidden" name="key" value="<?=Auth::key()?>">
	<h2>Rediger bruker</h2>
	<?php if($_GET['msg'] == "pswderrfi"){ ?><div class="msg error">For å endre et passord så du fylle ut alle passord feltene. Og alle passordfeltene må være minimum 6 tegn</div><? } ?>
	<?php if($_GET['msg'] == "pswderrmm"){ ?><div class="msg error">De nye passordene er ikke identiske</div><? } ?>
	<?php if($_GET['msg'] == "gen"){ ?><div class="msg error">Kunne ikke lagre brukeren</div><? } ?>
	<div class="line"><label for="fname">Fornavn: </label><input id="fname" name="fname" type="text" value="<?= $user['firstname'] ?>"></div>
	<div class="line"><label for="lname">Etternavn: </label><input id="lname" name="lname" type="text" value="<?= $user['lastname'] ?>"></div>
	<div class="line"><label for="email">Email: </label><input id="email" name="email" type="text" value="<?= $user['email'] ?>"></div>
	<? if($a->isAdmin()){ ?>
	<div class="line"><label for="desc">Budsjettgrunnlag: </label><textarea rows="8" id="desc" name="desc" type="text"><?= $user['description'] ?></textarea></div>
	<div class="line"><hr /></div>
	<div class="line"><label for="dept">Avdeling: </label>
		<select name="dept">
			<?
			$data = DB::get("SELECT name, id, ((SELECT dept FROM tr_users WHERE id = {$user['id']}) = id) AS current FROM tr_dept");
			$dlen = count($data);
			for($i=0;$i<$dlen;$i++){
				$s = ($data[$i]['current']==="1")?'selected="selected"':'';
			?>
			<option value="<?=$data[$i]['id']?>" <?=$s?>><?=$data[$i]['name']?></option>
			<?
			}
			?>
		</select>
	</div>
	<div class="line"><label for="dept">Måned: </label>
		<select name="month">
			<?
			$data = DB::get("SELECT name, id, ((SELECT registermonth FROM tr_users WHERE tr_users.id = {$user['id']}) = id) AS current FROM tr_months");
			$dlen = count($data);
			for($i=0;$i<$dlen;$i++){
				$s = ($data[$i]['current']==="1")?' selected="selected"':'';
			?>
			<option value="<?=$data[$i]['id']?>"<?=$s?>><?=$data[$i]['name']?></option>
			<?
			}
			?>
		</select>
	</div>
	<? } ?>
	<div class="line"><hr /></div>
	<? if(!$a->isAdmin()){ ?><div class="line"><label for="oldpswd">Gammelt passord: </label><input autocomplete="off" name="oldpswd" id="oldpswd" type="password" value=""></div><? } ?>
	<div class="line"><label for="newpswd">Passord: </label><input autocomplete="off" name="newpswd" id="newpswd" type="password" value=""></div>
	<div class="line"><label for="cnfpswd">Bekreft passord: </label><input autocomplete="off" name="cnfpswd" id="cnfpswd" type="password" value=""></div>
	<div class="line"><label>Passordstyrke: </label><div id="pswdstrength" class="none"></div></div>
	<? if($a->isAdmin() && $_GET['me'] != "true"){ ?>
	<div class="line"><hr /></div>
	<? $totdata = DB::get("SELECT SUM(total) AS total FROM tr_uservalmonth WHERE user = {$user['id']}"); ?>
	<div class="line"><label for="unit">Enhet: </label><input id="unit" name="unit" type="text" value="<?= $user['unit'] ?>"></div>
	<div class="line"><label for="total">Totalbudsjett: </label><button type="button" id="refreshbudget"><img src="media/img/refresh.png" alt="refresh" width="16" height="16" /></button><input name="total" id="total" type="text" value="<?=$totdata['total']?>"></div>
	<div id="budgeteditor">
		<? 
			$data = DB::get("SELECT (SELECT name FROM tr_months WHERE id = tr_uservalmonth.month) AS month, total FROM tr_uservalmonth WHERE user = {$user['id']} ORDER BY id ASC");
			$dlen = count($data);
			
			for($i=0;$i<$dlen;$i++){
			?>
				<div><label for="<?=$data[$i]['month']?>"><?=$data[$i]['month']?></label><input name="<?=$data[$i]['month']?>" id="<?=$data[$i]['month']?>" type="text" value="<?=$data[$i]['total']?>"><div class="clear"></div></div>
			<?
			}
			?>

	</div>
	
	<div class="line"><hr /></div>
	<div class="line">
		<label for="total">Historikk for mnd: </label>
		<select id="historyselect" name="history">
			<?
			$data = DB::get("SELECT name, id, ((SELECT registermonth FROM tr_users WHERE tr_users.id = {$user['id']}) = id) AS current FROM tr_months");
			$dlen = count($data);
			for($i=0;$i<$dlen;$i++){
				$s = ($data[$i]['current']==="1")?' selected="selected"':'';
			?>
			<option value="<?=$user['id']?>_<?=$data[$i]['id']?>"<?=$s?>><?=$data[$i]['name']?></option>
			<?
			}
			?>
		</select>
	</div>
	<div id="historyshow">
	<table class="small">
		<?php
			$dim = date('t',$date);
			?>
			<input type="hidden" name="dim" value="<?=$dim?>">
			<?
			for($i=0;$i<$dim;$i++){
			$date = mktime(0,0,0,$user['registermonth'],$i+1);
			$weekend = (date('N',$date)>=6)?'weekend':'';
			$even = ($i%2)?'even':'';
			
			$val = DB::get("SELECT id, value, correction_value FROM tr_points WHERE month = {$user['registermonth']} AND user = {$user['id']} AND day = ".($i+1));
			if(date('N',$date)==1){
			?>
			<tr class="newweek" >
				<td>&nbsp;</td>
			</tr>
			<?
			}
		?>
			<tr class="<?=$even.' '.$weekend?>">
				<td><?=$days[date('N',$date)-1]['name']?> <?=$i+1?>. <?=$month['name']?></td>
				<td><?=(strlen($val['value']) > 0)?$val['value'].$user['unit']:""?></td>
				<td><input autofocus tabindex="0" class="inputsum" name="value_<?=$i+1?>_<?=$val['id']?>_<?=$user['registermonth']?>" type="text" value="<?=$val['correction_value']?>"></td>
			</tr>
		<?php } ?>
		</table>
	</div>
	<div class="line">
		<label>Korriger verdi:</label> <input type="text" name="correction" id="correction" />
	</div>
	<div class="line">
		<? $data = DB::get("SELECT IFNULL(SUM(value),0) AS sum FROM tr_points WHERE user = {$user['id']} AND correction = 1"); ?>
		<label>Ny sum:</label> <div class="value" id="correctionvalue"><span><?=$data['sum']?></span><?=$user['unit']?></div>
	</div>
	<? } ?>
	<div class="footer"><input type="submit" value="Lagre"><input type="button" onclick="javascript:document.location = 'index.php?p=users';" class="grey" value="Avbryt"><? if($a->isAdmin()){ ?><a class="delete" href="index.php?p=delete&id=<?=$user['id']?>">Slett bruker</a></div><? } ?>
</form>
<? }else{ header('Location: index.php'); } ?>