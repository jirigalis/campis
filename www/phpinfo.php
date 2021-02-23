<?php
		error_reporting(E_ALL & ~E_NOTICE);
		$username = "jirigalis";
		$password = "i62kgbtr";
		$host = "innodb.endora.cz";
		$database="campis";
		$conn = mysqli_connect($host, $username, $password, $database);
		//mysqli_set_charset($conn, "utf8");
		$camp_id = 7;
		
		//load teams
		$teamQuery = mysqli_query($conn, "SELECT id, CONVERT(CAST(name as BINARY) USING utf8) as name FROM team WHERE camp=" . $camp_id);
		$teams= array();	
		while ($row = mysqli_fetch_assoc($teamQuery)) {
			$teams[] = array("id" => $row["id"], "name" => $row["name"]);
		}		

		var_dump($teams);
		
		// prepare dates for chart	
		$campDates = mysqli_fetch_object(mysqli_query($conn, "SELECT start, end FROM camp WHERE id = ".$camp_id." LIMIT 1"));
		$endDate = $campDates->end;
		$today = date("d.m.Y");
		
		if (strtotime($endDate)>strtotime($today)) {
			$endDate = $today;
		}

		$dates = getDatesInRange($campDates->start, $endDate);
		
		
		//load data for each team separately
		$teamPoints = array();
		$personPoints = array();
		$teamPointsSpecial = array();


		foreach ($teams as $i => $teamArr) {
			$team = intval($teamArr["id"]);
			$teamPointsSql = "SELECT
				rt.team,
				SUBSTRING(rt.date, 1, 10) as datestr,
				(SELECT SUM(rt2.points) FROM results_team rt2 WHERE rt2.team = ".$team." and rt2.date <= rt.date ) as cumulative_sum
				from results_team rt
			where camp = ".$camp_id." and rt.team = ".$team." group by datestr, team";

			$personPointsSql = "SELECT
				cc.team_id as team,
				SUBSTRING(pc.date, 1, 10) as datestr,
				(SELECT
					SUM(pc2.points)
				FROM points_child pc2
					LEFT JOIN child_camp cc2 ON pc2.childId = cc2.child_id
				WHERE
					cc2.camp_id = ".$camp_id."
					and cc2.team_id = ".$team."
					and pc2.camp = ".$camp_id."
					and SUBSTRING(pc2.date, 1, 10) <= datestr    
				) as cumulative_sum
			FROM points_child pc
			LEFT JOIN child_camp cc on pc.childId = cc.child_id
			WHERE cc.camp_id = ".$camp_id." and pc.camp = ".$camp_id." and cc.team_id = ".$team."
			GROUP BY cc.team_id, datestr
			ORDER BY cc.team_id, datestr";
			
			$teamPointsSpecialSql = "SELECT 
				pt.team,
				sum(pt.points),
				SUBSTRING(pt.date, 1, 10) as datestr,
					(SELECT SUM(pt2.points) FROM points_team pt2 WHERE pt2.team = pt.team and pt2.camp = ".$camp_id." and SUBSTRING(pt2.date, 1, 10) <= datestr ) as cumulative_sum
			from points_team pt WHERE pt.camp = ".$camp_id." and pt.team = ".$team." group by datestr order by datestr";

			//fetch team data
			$query = mysqli_query($conn, $teamPointsSql);
			if (true) {
				while ($row = mysqli_fetch_assoc($query)) {
					$teamPoints[$team][] = $row;
				}
			}

			//fetch person data
			$query = mysqli_query($conn, $personPointsSql);
			if ($query) {
				while ($row = mysqli_fetch_assoc($query)) {
					$personPoints[$team][] = $row;
				}
			}
			
			//fetch team special data
			$query = mysqli_query($conn, $teamPointsSpecialSql);
			if ($query) {
				while ($row = mysqli_fetch_assoc($query)) {
					$teamPointsSpecial[$team][] = $row;
				}
			}
			
			
		}	
			
		$datesWithPoints = array();

		foreach ($teams as $i => $teamArr) {
			$team = intval($teamArr["id"]);
			$lastTeamPoints = 0;
			$lastPersonPoints = 0;
			$specialPoints = 0;
			foreach ($dates as $j => $date) {
				$teamDataForDate = getDataForDate($teamPoints[$team], $date);
				
				//TEAM points
				if (sizeof($teamDataForDate) == 0) {
					$teamDataForDate = array("team" => $teamArr["name"], "datestr" => $date, "cumulative_sum" => $lastTeamPoints);
				}			
				$lastTeamPoints = $teamDataForDate["cumulative_sum"];
				
				// PERSON Points
				$personDataForDate = getDataForDate($personPoints[$team], $date);
				if (sizeof($personDataForDate) == 0) {
					$personDataForDate = array("team" => $teamArr["name"], "datestr" => $date, "cumulative_sum" => $lastPersonPoints);
				}
				
				
				$teamDataForDate["cumulative_sum"] += $personDataForDate["cumulative_sum"];
				
				//save last person value
				$lastPersonPoints = $personDataForDate["cumulative_sum"];
					
				
				//ADD TEAM SPECIAL POINTS
				$teamSpecialDataForDate = getDataForDate($teamPointsSpecial[$team], $date);
				if (sizeof($teamSpecialDataForDate) > 0) {
					$specialPoints = $teamSpecialDataForDate["cumulative_sum"];
				}
				$teamDataForDate["cumulative_sum"] += $specialPoints;
							
				//add data to final array
				$dateWithPoints[] = $teamDataForDate;
			}
		}
				//var_dump($dateWithPoints);

		$jsonData = json_encode($dateWithPoints);
		echo "<pre>". $jsonData . "</pre>";

		$fp = fopen('teamGraph.json', 'w');
		fwrite($fp, $jsonData);
		fclose($fp);
		
		$fperror = fopen('jsonError.txt', 'w');
		fwrite($fperror, json_last_error_msg());
		fclose($fperror);

		mysqli_close($conn);

function getDataForDate($arrayData, $date) {
		$result = array();
		foreach ((array) $arrayData as $i => $value) {
			if ($value["datestr"] == $date) {
				$result = $value;
			}
		}
		return $result;
	}
function getDatesInRange($first, $last, $step = '+1 day', $output_format = 'd.m.Y' ) {

		$dates = array();
		$current = strtotime($first);
		$last = strtotime($last);

		while( $current <= $last ) {

			$dates[] = date($output_format, $current);
			$current = strtotime($step, $current);
		}

		return $dates;
	}
	
function utf8ize( $mixed ) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}