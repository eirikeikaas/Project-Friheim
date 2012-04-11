<div class="block">
	<h2>Historikk</h2>
	<?php if($_GET['msg'] == "done"){ ?><div class="msg success">Lagret!</div><? } ?>
	<?php if($_GET['msg'] == "keyerr"){ ?><div class="msg error">Kunne ikke bekrefte nøkkelen. Logg ut og deretter inn igjen for å prøve på nytt</div><? } ?>
	Verdi i parantes er hvor mye som er blitt korrigert av administrator
	<table>
		<?
		$user = Auth::userData();
		$data = DB::get("SELECT IFNULL(tr_points.value,0) AS value, tr_points.customer AS customer, (SELECT tr_months.name FROM tr_months WHERE id = tr_points.month) AS month, day, IFNULL(correction_value,0) as corrected FROM tr_points WHERE tr_points.user = {$user['id']} ORDER BY timestamp DESC");
		$dlen = count($data);
		if(!isset($data['month'])){
			for($i=0;$i<$dlen;$i++){
				$date = (isset($data[$i]['day']))?$data[$i]['day'].". ".$data[$i]['month']:$data[$i]['month'];
				?>
				<tr>
					<td><?=$data[$i]['value'].$user['unit']?> (<?=$data[$i]['corrected'].$user['unit']?>)</td>
					<td><?=$date?></td>
					<td><?=$user['name']?></td>
				</tr>
				<?
			}
		}else{
			?>
				<tr>
					<td><?=$data['value'].$user['unit']?></td>
					<td><?=$data['day'].". ".$data['month']?></td>
					<td><?=$user['name']?></td>
				</tr>
			<?
		}
		?>
	</table>
</div>