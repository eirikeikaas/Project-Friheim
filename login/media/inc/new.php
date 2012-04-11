<?php

$db = &DB::getInstance();
$user = Auth::userData();
$a = new Auth();

if($a->isLoggedIn()){
if(isset($_GET['a']) && $_GET['a']=='add'){
	
	if(Auth::key($_POST['key'])){
		$c_customer = $db->escape_string($_POST['customer']);
		$c_week = date("W");
		
		$dinm = date('t');
		for($i=0;$i<$dinm;$i++){
			if(isset($_POST['value_'.($i+1)]) && is_numeric($_POST['value_'.($i+1)])){
				$c_value = str_replace(array(' '), '', $db->escape_string($_POST['value_'.($i+1)]));
				$day = $i+1;
				//echo "INSERT INTO tr_points (user, value, month, customer, timestamp, day) VALUES ({$user['id']},{$c_value}, {$user['registermonth']}, '{$c_customer}', NOW(), {$day})\n";
				$qry = DB::set("INSERT INTO tr_points (user, value, month, customer, timestamp, day) VALUES ({$user['id']},{$c_value}, {$user['registermonth']}, '{$c_customer}', NOW(), {$day})");
				if($qry->affected_rows == 0){
					//echo "UPDATE tr_points SET value = {$c_value} WHERE user = {$user['id']} AND month = {$user['registermonth']} AND day = {$day}\n";
					$db->query("UPDATE tr_points SET value = {$c_value} WHERE user = {$user['id']} AND month = {$user['registermonth']} AND day = {$day}");
					//$db->query("REPLACE tr_points (user, value, month, customer, timestamp, day) VALUES ({$user['id']},{$c_value}, {$user['registermonth']}, '{$c_customer}', NOW(), {$day})\n");
				}
			}
		}

		#die("---- END ----");
		
		$c_week1 = $db->escape_string((int)$_POST['memow1']);
		$c_week2 = $db->escape_string((int)$_POST['memow2']);
		$c_week3 = $db->escape_string((int)$_POST['memow3']);
		$c_week4 = $db->escape_string((int)$_POST['memow4']);
		$c_week5 = $db->escape_string((int)$_POST['memow5']);
		$c_text = $db->escape_string($_POST['text']);
		
		$q2 = $db->query("REPLACE tr_memo (week1, week2, week3, week4, week5, text, user, month) VALUES({$c_week1},{$c_week2},{$c_week3},{$c_week4},{$c_week5},'{$c_text}',{$user['id']},{$user['registermonth']})");
	
		if(isset($_POST['month']) && $_POST['month'] == true){
			$q = $db->query("UPDATE tr_users SET registermonth = registermonth+1 WHERE id = {$user['id']}");
		}
	
		if($q!==false){
			// TODO: Gå til denne siden!
			header('location: index.php?p=edit&msg=done');
		}else{
			echo "Det oppstod en feil!<br />";
			die($db->error);
		}
	}else{
		header('location: index.php?p=edit&msg=keyerr');
	}
}

$stat = DB::get("SELECT IFNULL((SELECT SUM(value) FROM tr_points WHERE user = tr_users.id AND month = tr_users.registermonth),0) AS month, IFNULL((SELECT SUM(value) FROM tr_points WHERE user = tr_users.id),0) AS total, IFNULL(((SELECT SUM(value) FROM tr_points WHERE user = tr_users.id)*100)/(SELECT SUM(total) FROM tr_uservalmonth WHERE user = tr_users.id),0) AS totpercent, IFNULL(((SELECT SUM(value) FROM tr_points WHERE user = tr_users.id AND month = tr_users.registermonth)*100)/(SELECT total FROM tr_uservalmonth WHERE user = tr_users.id AND month = tr_users.registermonth),0) AS monthpercent FROM tr_users WHERE id = {$user['id']}");

function visual($value){
	$text = ($value>(int)DB::_get('visual_green_limit'))?' class="green"':'';
	$text = ($value<(int)DB::_get('visual_red_limit'))?' class="red"':$text;
	
	return $text;
}?>
<form class="block" id="newsum" action="index.php?p=new&amp;a=add" method="post">
	<input type="hidden" name="key" value="<?=Auth::key()?>">
	
	<h2>Registrer nye verdier</h2>
	
	<div id="instructions">
	<h3>Instruksjoner</h3>
	<?=DB::_get('instructions')?>

  <? $memo = DB::get("SELECT * FROM tr_memo WHERE user = {$user['id']} AND month = {$user['registermonth']}"); ?>
  <h3>Egne notater</h3>
   <textarea tabindex="6" name="text" rows="15" placeholder="Mine merknader"><?=$memo['text']?></textarea>
   
	<?
		$months = DB::get('SELECT name FROM tr_months');
		$days = DB::get('SELECT name FROM tr_days');
		$month = DB::get("SELECT tr_months.name FROM tr_users INNER JOIN tr_months ON tr_users.registermonth = tr_months.id WHERE tr_users.id = {$user['id']}");
		$budget = DB::get("SELECT total AS m, (SELECT SUM(total) FROM tr_uservalmonth WHERE user = {$user['id']}) AS y FROM tr_uservalmonth WHERE user = {$user['id']} AND month = {$user['registermonth']}");
	?>

</div>	<?php if(strlen($user['description']) > 0){
?><div class="line budget"><h5>Grunnlag for budsjett:</h5><?=$user['description']?></div>

	<div class="line"><hr/></div><? } ?>
<!--<div class="line"><?=$month['name']?>budsjett: <span class="bold"><?=$budget['m'].$user['unit']?></span> &nbsp;&nbsp;&nbsp;&nbsp; Årsbudsjett: <span class="bold"><?=$budget['y'].$user['unit']?></span></div>--><div class="line">
		<div class="inlinestats">
			<h6>Mnd/<span>år</span></h6>
			<h4<?=visual((int)$stat['monthpercent'])?>><?=(int)$stat['month'].$user['unit']?></h4>
			<h5<?=visual((int)$stat['totpercent'])?>><?=(int)$stat['total'].$user['unit']?></h5>
		</div>
		<div class="inlinestats">
			<h6>Mnd/<span>år</span></h6>
			<h4<?=visual((int)$stat['monthpercent'])?>><?=(int)$stat['monthpercent']?>%</h4>
			<h5<?=visual((int)$stat['totpercent'])?>><?=(int)$stat['totpercent']?>%</h5>
		</div>
		<div class="inlinestats">
			<h6>Budsjett (mnd/<span>år</span>)</h6>
			<h4><?=$budget['m'].$user['unit']?></h4>
			<h5><?=$budget['y'].$user['unit']?></h5>
		</div>
	</div>
	<div class="line"><hr/></div>
	<!--<? if($user['dept']==2){ ?><div class="line"><input id="customer" type="text" name="customer" placeholder="Kunde"></div><? } ?>-->
	<div class="line">
		<table id="inputs" class="small">
		<?php
			for($i=0;$i<date('t',$date);$i++){
			$date = mktime(0,0,0,$user['registermonth'],$i+1);
			$weekend = (date('N',$date)>=6)?'weekend':'';
			$even = ($i%2)?'even':'';
			
			$val = DB::get("SELECT id, value FROM tr_points WHERE user = {$user['id']} AND month = {$user['registermonth']} AND day = ".($i+1));
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
				<td><input autofocus tabindex="0" class="inputsum" name="value_<?=$i+1?>" type="text" value="<?=$val['value']?>"></td>
			</tr>
		<?php } ?>
		</table>
	</div>
		<div class="line"><input tabindex="6" name="month" type="checkbox" /> Ferdigregistrer <?= strtolower($month['name'])?></div>
	<div class="footer"><input tabindex="7" type="submit" onclick="_gaq.push(['_trackEvent', 'Admin', 'Save', '<?=$user['name']?>, <?=$month['name']?>']); /*alert(document.getElementById('month').value); return false; if(document.getElementById('month').value == 1){return confirm('Hello, hello. Hello, hello, hello!');}*/" value="Lagre"/><input tabindex="8" type="button" onclick="javascript:document.location = 'index.php?p=edit';" class="grey" value="Avbryt"/></div>
	
</form>
<? } ?>