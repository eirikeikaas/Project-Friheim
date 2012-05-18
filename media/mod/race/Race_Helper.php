<?php

/**
 * ======= Blog Class ======
 * 
 * Info
 * 
 * @version 1.0
 * @author Eirik Eikaas, Blest AS
 * @copyright Copyright (c) 2012, Blest AS
 * @uses DB
 * @uses Auth
 * @uses System
 */

class Race_Helper extends Helper{
	public function __construct($table){
		$this->info("Race", "race", "0.1a");
	}

	public function insert($user, $value, $week, $month, $next_month){
	}
	
	public function update($user, $id, $value, $month){
	
	}
	
	public function correction($id, $correction){
	
	}
	
	public function delete($id){
	
	}
	
	public function get($type, $vars = array()){
		switch($type){
			case "statlist":
				if($vars['month']=="total"){
					$r = ORM::raw_query("SELECT tr_users.id AS id, CONCAT(tr_users.firstname,' ',tr_users.lastname) AS name, (SELECT tr_dept.slug FROM tr_dept WHERE tr_dept.id = tr_users.dept) AS dept, (((SELECT (SUM(IFNULL(tr_points.value,0))+SUM(IFNULL(tr_points.correction_value,0))) AS v FROM tr_points WHERE user = tr_users.id)*100)/(SELECT SUM(tr_uservalmonth.total) FROM tr_uservalmonth WHERE tr_uservalmonth.user = tr_users.id)) AS percent FROM tr_users WHERE tr_users.invisible IS NULL ORDER BY percent DESC")->find_many();
				}else{
					$r = ORM::raw_query("SELECT tr_users.id AS id, CONCAT(tr_users.firstname,' ',tr_users.lastname) AS name, (SELECT tr_dept.slug FROM tr_dept WHERE tr_dept.id = tr_users.dept) AS dept, (((SELECT (SUM(IFNULL(tr_points.value,0))+SUM(IFNULL(tr_points.correction_value,0))) AS v FROM tr_points WHERE user = tr_users.id AND tr_points.month = :month)*100)/(SELECT tr_uservalmonth.total FROM tr_uservalmonth WHERE tr_uservalmonth.user = tr_users.id AND tr_uservalmonth.month = :month)) AS percent FROM tr_users WHERE tr_users.invisible IS NULL ORDER BY percent DESC", array('month' => $vars['month']))->find_many();
				}
				return "";
				break;
			case "historystats":
				
				break;
			case "board":
				$r = ORM::raw_query("SELECT tr_users.id AS id, tr_users.lastname AS lname, CONCAT(SUBSTRING(tr_users.firstname,1,1),SUBSTRING(tr_users.lastname,1,1)) AS initials, IFNULL((SELECT (IFNULL(SUM(value),0)+IFNULL(SUM(correction_value),0)) as v FROM tr_points WHERE user = tr_users.id),0) AS value, IFNULL((SELECT IFNULL(stop,0) FROM tr_userval WHERE user = tr_users.id ORDER BY ABS(max-IFNULL(value,0)) LIMIT 1),1) AS stop, (SELECT x FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS x, (SELECT y FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS y, (SELECT z FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS z, (SELECT month FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS m, (SELECT month_part FROM tr_stops WHERE tr_stops.id = stop LIMIT 1) AS mp, (SELECT slug as d FROM tr_dept WHERE id = tr_users.dept) AS dept, IFNULL((((SELECT (SUM(IFNULL(tr_points.value,0))+SUM(IFNULL(tr_points.correction_value,0))) AS v FROM tr_points WHERE user = tr_users.id)*100)/(SELECT SUM(tr_uservalmonth.total) FROM tr_uservalmonth WHERE tr_uservalmonth.user = tr_users.id)),0) AS percent, ((SELECT x FROM tr_stops WHERE tr_stops.id = stop LIMIT 1)*100)/656 AS xpercent, ((SELECT y FROM tr_stops WHERE tr_stops.id = stop LIMIT 1)*100)/1034 AS ypercent FROM tr_users WHERE tr_users.invisible IS NULL ORDER BY percent, stop, value ASC")->find_many();
				return "";
				break;
			case "user":
				break;
		}
	}
}