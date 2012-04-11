<?

/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/

$a = new Auth();
if(isset($_GET['a']) && $_GET['a'] == 'edit'){
	echo getPage('users_edit');
}else if(isset($_GET['a']) && $_GET['a'] == 'new'){
	echo getPage('users_new');
}else{
	if(isset($_GET['a']) && $_GET['a'] == 'save'){
		
		// Check the validity of the key
		if(Auth::key($_POST['key'])){
			$db = &DB::getInstance();
			
			// Cleaning
			$c_id	    = DB::escape($_GET['id']);
			$c_fname    = DB::escape($_POST['fname']);
			$c_lname    = DB::escape($_POST['lname']);
			$c_email    = DB::escape($_POST['email']);
			$c_dept     = DB::escape($_POST['dept']);
			$c_unit		= DB::escape($_POST['unit']);
			$c_month    = DB::escape($_POST['month']);
			$c_oldpswd  = DB::escape($_POST['oldpswd']);
			$c_newpswd  = DB::escape($_POST['newpswd']);
			$c_cnfpswd  = DB::escape($_POST['cnfpswd']);
			$c_correction = DB::escape($_POST['correction']);
			$c_desc 	= DB::escape($_POST['desc']);
			// --------

			if($_POST['new'] == "false" && !$a->isAdmin()){ // OK
				if(strlen($c_oldpswd) > 0 || strlen($c_newpswd) > 0 || strlen($c_cnfpswd) > 0){
					// If one or more pswd fields are set, require all of them to be more than 6 chars lon
					if(strlen($c_oldpswd) >= 6 && strlen($c_newpswd) >= 6 && strlen($c_cnfpswd) >= 6){
						// Now check and/or update the password
						$a = new Auth();
						
						if($a->updatePassword($c_id, $c_oldpswd, $c_newpswd, $c_cnfpswd) === false){
							header("location: ?p=users&a=edit&id={$c_id}&msg=pswderrgen");
						}else{
							if(DB::set("UPDATE tr_users SET firstname = '{$c_fname}', lastname = '{$c_lname}', email = '{$c_email}' WHERE id = {$c_id}")){
								header("location: ?p=users&a=edit&id={$c_id}&msg=done");
							}else{
								die(DB::error());
								header("location: ?p=users&a=edit&id={$c_id}&msg=gen");
							}
						}
					}else{
						// Error
						header("location: ?p=users&a=edit&id={$c_id}&msg=pswderrfi");
					}
				}
				// --------
				
			}else if($_POST['new'] == "true" && $a->isAdmin()){ // OK
			    
			    if($c_newpswd === $c_cnfpswd){
			    	if(DB::set("INSERT INTO tr_users(firstname,lastname,email,dept) VALUES('{$c_fname}','{$c_lname}','{$c_email}',{$c_dept})")){
			    		$id = DB::get("SELECT id FROM tr_users ORDER BY id DESC LIMIT 1");
			    		$id = $id['id'];
			    	
			    		if($a->updatePassword($id, $c_oldpswd, $c_newpswd, $c_cnfpswd) === false){
			    			DB::set('DELETE FROM tr_users WHERE id = '.$id);
			    			header("location: ?p=users&msg=createfailpswd");
			    		}else{
			    			$months = array('Januar', 'Februar', 'Mars', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Desember');
			        		$cumu = 1;
			    			$totm = 0;
			    	
			    	
			    	    	DB::set("DELETE FROM tr_userval WHERE user = {$id}");
			    	    	DB::set("DELETE FROM tr_uservalmonth WHERE user = {$id}");
			        		DB::set("INSERT INTO tr_userval(user,max,stop) VALUES({$id},0, 1)");
			        		for($i=1;$i<=12;$i++){
			        			$m = DB::escape($_POST[$months[$i-1]]);
			        	
			    	    		DB::set("INSERT INTO tr_uservalmonth(user,month,total) VALUES({$id},{$i},$m)");
			    	    
			    	    		$eight = $m/8;
			    	    
			    	    		for($ii=1;$ii<=8;$ii++){
			        				$thm = ($eight*$ii)+$totm;
			        				$cumu++;
			        				DB::set("INSERT INTO tr_userval(user,max,stop) VALUES({$id},{$thm}, {$cumu})");
			        			}
			        			$totm += $m;
			        		}
			        		header("location: ?p=users&msg=saved");
			    		}
			    	}else{
			    		header("location: ?p=users&msg=createfail");
			    	}
			    }else{
			    	header("location: ?p=users&msg=createfail");
			    }
			}else if($_POST['new'] == "false" && $a->isAdmin()){
			    if(strlen($c_newpswd) > 0 || strlen($c_cnfpswd) > 0){
			    	// If one or more pswd fields are set, require all of them to be more than 6 chars lon
			    	if(strlen($c_newpswd) >= 4 && strlen($c_cnfpswd) >= 4){
			    		// Now check and/or update the password
			    		$a = new Auth();
			    		if($a->updatePassword($c_id, $c_oldpswd, $c_newpswd, $c_cnfpswd) === false){
			    			header("location: ?p=users&a=edit&id={$c_id}&msg=pswderrgen");
			    		}else{
			    			
			    		}
			    	}else{
			    		// Error
			    		header("location: ?p=users&a=edit&id={$c_id}&msg=pswderrfi");
			    	}
			    }
			    
			    if(DB::set("UPDATE tr_users SET firstname = '{$c_fname}', lastname = '{$c_lname}', email = '{$c_email}', dept = {$c_dept}, unit = '{$c_unit}', description = '{$c_desc}', registermonth = {$c_month} WHERE id = {$c_id}")){
				    $months = array('Januar', 'Februar', 'Mars', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Desember');
					$cumu = 1;
				    $totm = 0;
					
				    DB::set("DELETE FROM tr_userval WHERE user = {$c_id}");
				    DB::set("DELETE FROM tr_uservalmonth WHERE user = {$c_id}");
				    DB::set("INSERT INTO tr_userval(user,max,stop) VALUES({$c_id},0, 1)");
				    
				    for($i=1;$i<=12;$i++){
				    	$m = DB::escape($_POST[$months[$i-1]]);
				
				    	DB::set("INSERT INTO tr_uservalmonth(user,month,total) VALUES({$c_id},{$i},$m)");
				
				    	$eight = $m/8;
				
				    	for($ii=1;$ii<=8;$ii++){
				    		$thm = ($eight*$ii)+$totm;
				    		$cumu++;
				    		DB::set("INSERT INTO tr_userval(user,max,stop) VALUES({$c_id},{$thm}, {$cumu})");
				    	}
				    	$totm += $m;
				    }
				    
				    $c_dim = DB::escape($_POST['dim']);
				    $c_mon = DB::escape($_POST['history']);
				    //die($c_mon);
				    $user = Auth::userData($c_id);
				    foreach($_POST as $key => $value){
				    	if(!preg_match("/(new|key|fname|lname|email|desc|dept|month|newpswd|cnfpswd|unit|total|history|dim)/",$key)){
				    		//echo $key."\n";
				    		$xkey = explode("_", $key);
				    		if(isset($xkey[2]) && !empty($xkey[2]) && isset($value) && is_numeric($value)){
				    			/*echo "SHOULD INSERT DATA\n";
				    			echo "user: ".$c_id."\n";
				    			echo "correction: ".$value."\n";
				    			echo "month: ".$user['registermonth']."\n";
				    			echo "day: ".$xkey[1]."\n";*/

				    			$c_value = DB::escape($value);
				    			DB::set("UPDATE tr_points SET correction_value = {$c_value} WHERE user = {$c_id} AND month = {$xkey[3]} AND day = {$xkey[1]}");
				    		}else if(isset($xkey[1]) && is_numeric($xkey[1]) && isset($value) && is_numeric($value)){
				    			$c_value = DB::escape($value);
				    			DB::set("INSERT INTO tr_points (correction_value, user, month, day, timestamp) VALUES ({$c_value}, {$c_id}, {$xkey[3]}, {$xkey[1]}, NOW())");
				    		}
				    		/*if(isset($_POST['value_'.$i]) && !empty($_POST['value_'.$i])){
				    			//$val = explode("_", $)
				    			$c_value = DB::escape($_POST['value_'.$i]);
				    			//echo "REPLACE tr_points (user, correction_value, month, timestamp, day) VALUES ({$c_id},{$c_value}, {$user['registermonth']}, NOW(), {$i})\n";
				    			DB::set("REPLACE tr_points (user, correction_value, month, timestamp, day) VALUES ({$user['id']},{$c_value}, {$user['registermonth']}, NOW(), {$i})\n");
				    		}*/
				    	}
				    }

				    //die("---- END ----");
				    
				    if(strlen($c_correction) > 0){
				    	$me = Auth::userData();
				    	DB::set("INSERT INTO tr_points (value, user, timestamp, month, correction, corrector) VALUES({$c_correction},{$c_id},NOW(),{$c_month},1,{$me['id']})");
				    }
				    
				    header("location: ?p=users&msg=saved");
				}else{
				    header("location: ?p=users&a=edit&id={$c_id}&msg=gen");
				}		
			}
			
		}
	}
	if($a->isAdmin()){
?>
<form class="block">
	<h2>Brukere<a href="?p=users&amp;a=new" class="hbtn">Legg til ny bruker</a></h2>
	<?php if($_GET['msg'] == "saved"){ ?><div class="msg success">Brukeren ble oppdatert</div><? } ?>
	<table>
		<?php
		
		$user = Auth::userData();
		$data = DB::get("SELECT tr_users.id, tr_users.unit, CONCAT(tr_users.firstname, ' ', tr_users.lastname) AS name, IFNULL((SELECT (IFNULL(SUM(value),0)+IFNULL(SUM(correction_value),0)) FROM tr_points WHERE tr_points.user = tr_users.id),0) AS curr, IFNULL((SELECT SUM(total) FROM tr_uservalmonth WHERE tr_uservalmonth.user = tr_users.id),0) AS total, IFNULL(ROUND(((SELECT SUM(tr_points.value) FROM tr_points WHERE tr_points.user = tr_users.id)*100)/(SELECT SUM(total) FROM tr_uservalmonth WHERE tr_uservalmonth.user = tr_users.id),1),0) AS tp, IFNULL(ROUND(((SELECT SUM(value) FROM tr_points WHERE user = tr_users.id AND month = tr_users.registermonth)*100/(SELECT total FROM tr_uservalmonth WHERE month = tr_users.registermonth AND user = tr_users.id)),1),0) AS mp, tr_dept.name AS dept, tr_dept.slug AS depts, tr_months.name AS month FROM tr_users INNER JOIN tr_dept ON tr_dept.id = tr_users.dept LEFT JOIN tr_months ON tr_months.id = tr_users.registermonth WHERE tr_users.invisible IS NULL ORDER BY tr_users.lastname");
		
		$dlen = count($data);
		
		for($i=0;$i<$dlen;$i++){
			$even = ($i%2)?' class="even"':'';
			?>
			<tr<?=$even?>>
				<td><?=$data[$i]['name']?></td>
				<td><?= (int)$data[$i]['curr'].$data[$i]['unit'] ?> (<?=$data[$i]['total'].$data[$i]['unit']?>) <?=$data[$i]['mp']?>% <?=$data[$i]['tp']?>%</td>
				<td><?=$data[$i]['month']?></td>
				<td><div class="tdwrap"><div class="tag <?=$data[$i]['depts']?>"><?=$data[$i]['dept']?></div><a href="?p=users&amp;a=edit&amp;id=<?=$data[$i]['id']?>" class="editbtn"></a></div></td>
			</tr>
		<? } ?>
	</table>
	<div class="footer">
		<a href="?p=users&amp;a=new" class="hbtn">Legg til ny bruker</a>
	</div>
</form>
<? }} ?>