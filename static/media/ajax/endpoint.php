<?php

header("Content-Type: application/json");

/*include_once('../lib/DB.php');
$db = new DB();*/

die();

if(isset($_GET['segment'])){
	switch($_GET['segment']){
		case "board":
			$q = ORM::raw_query("SELECT tr_users.id AS id, tr_users.lastname AS lname, CONCAT(SUBSTRING(tr_users.firstname,1,1),SUBSTRING(tr_users.lastname,1,1)) AS initials, IFNULL((SELECT (IFNULL(SUM(value),0)+IFNULL(SUM(correction_value),0)) as v FROM tr_points WHERE user = tr_users.id),0) AS value, IFNULL((SELECT IFNULL(stop,0) FROM tr_userval WHERE user = tr_users.id ORDER BY ABS(max-IFNULL(value,0)) LIMIT 1),1) AS stop, (SELECT x FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS x, (SELECT y FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS y, (SELECT z FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS z, (SELECT month FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS m, (SELECT month_part FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS mp, (SELECT slug as d FROM tr_dept WHERE id = tr_users.dept) AS dept, IFNULL((((SELECT (SUM(IFNULL(tr_points.value,0))+SUM(IFNULL(tr_points.correction_value,0))) AS v FROM tr_points WHERE user = tr_users.id)*100)/(SELECT SUM(tr_uservalmonth.total) FROM tr_uservalmonth WHERE tr_uservalmonth.user = tr_users.id)),0) AS percent, ((SELECT x FROM tr_stops WHERE tr_stops.id = stop LIMIT 1)*100)/656 AS xpercent, ((SELECT y FROM tr_stops WHERE tr_stops.id = stop LIMIT 1)*100)/1034 AS ypercent FROM tr_users WHERE tr_users.invisible IS NULL ORDER BY percent, stop, value ASC")->find_many();
			$nr = array();
			
			foreach($q as $row){
				$nr[] = $row->as_array();
			}
			
			/*while($r = $q->fetch_assoc()){
				$nnr = array();
				foreach($r as $rw){
					$nnr[] = $rw;
				}
				$nr[] = $nnr;
			}*/
			
			echo json_encode($nr);
			break;
		case "list":
			$c_month = $db->escape_string($_GET['month']);
			if($c_month=="total"){
				$q = $db->query("SELECT tr_users.id AS id, CONCAT(tr_users.firstname,' ',tr_users.lastname) AS name, (SELECT tr_dept.slug FROM tr_dept WHERE tr_dept.id = tr_users.dept) AS dept, (((SELECT (SUM(IFNULL(tr_points.value,0))+SUM(IFNULL(tr_points.correction_value,0))) AS v FROM tr_points WHERE user = tr_users.id)*100)/(SELECT SUM(tr_uservalmonth.total) FROM tr_uservalmonth WHERE tr_uservalmonth.user = tr_users.id)) AS percent FROM tr_users WHERE tr_users.invisible IS NULL ORDER BY percent DESC");
			}else{
				$q = $db->query("SELECT tr_users.id AS id, CONCAT(tr_users.firstname,' ',tr_users.lastname) AS name, (SELECT tr_dept.slug FROM tr_dept WHERE tr_dept.id = tr_users.dept) AS dept, (((SELECT (SUM(IFNULL(tr_points.value,0))+SUM(IFNULL(tr_points.correction_value,0))) AS v FROM tr_points WHERE user = tr_users.id AND tr_points.month = {$c_month})*100)/(SELECT tr_uservalmonth.total FROM tr_uservalmonth WHERE tr_uservalmonth.user = tr_users.id AND tr_uservalmonth.month = {$c_month})) AS percent FROM tr_users WHERE tr_users.invisible IS NULL ORDER BY percent DESC");
			}
			echo $db->error;
			$nr = array();
			while($r = $q->fetch_assoc()){
				$nnr = array();
				foreach($r as $rw){
					$nnr[] = $rw;
				}
				$nr[] = $nnr;
			}
			
			echo json_encode($nr);

			break;
		case "dept":	
			$q = $db->query("SELECT tr_dept.name AS name, SUM(tr_points.value) AS value FROM tr_dept LEFT JOIN tr_users ON tr_users.dept = tr_dept.id LEFT JOIN tr_points ON tr_users.id = tr_points.user WHERE tr_users.invisible IS NULL LIMIT 1");
			$nr = array();
			while($r = $q->fetch_assoc()){
				$nr[] = $r;
			}
			
			echo json_encode($nr);
			break;
		case "stops":	
			$q = $db->query("SELECT x, y, z, month AS m, month_part AS mp FROM tr_stops");
			$nr = array();
			while($r = $q->fetch_assoc()){
				$nr[] = $r;
			}
			
			echo json_encode($nr);
			break;
		case "dummy":
			echo "Hello";
			break;
		default:	
			break;
	}
}

?> 