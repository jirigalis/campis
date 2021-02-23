<style>
div {
    display: none;
}
</style>
<?php


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

	$username = "jirigalis";
	$password = "i62kgbtr";
	$host = "innodb.endora.cz";
	$database="campis";
	$conn = mysqli_connect($host, $username, $password, $database);
    $camp_id = $_GET["camp"];
	
	//load teams
	$teamQuery = mysqli_query($conn, "SELECT id FROM team WHERE camp=" . $camp_id);
	$teams= array();	
	while ($row = mysqli_fetch_assoc($teamQuery)) {
		$teams[] = $row["id"];
	}

	
	// prepare dates for chart	
	$campDates = mysqli_fetch_object(mysqli_query($conn, "SELECT start, end FROM camp WHERE id = ".$camp_id." LIMIT 1"));
	$dates = getDatesInRange($campDates->start, $campDates->end);
	
	
	//load data for each team separately
	$teamPoints = array();
	$personPoints = array();
	$teamPointsSpecial = array();


    foreach ($teams as $i => $team) {		
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
				(SELECT SUM(pt2.points) FROM points_team pt2 WHERE pt2.team = pt.team and pt2.camp = 6 and SUBSTRING(pt2.date, 1, 10) <= datestr ) as cumulative_sum
		from points_team pt WHERE pt.camp = ".$camp_id." and pt.team = ".$team." group by datestr order by datestr";

		//fetch team data
		$query = mysqli_query($conn, $teamPointsSql);
		while ($row = mysqli_fetch_assoc($query)) {
			$teamPoints[$team][] = $row;
		}

		//fetch person data
		$query = mysqli_query($conn, $personPointsSql);
		while ($row = mysqli_fetch_assoc($query)) {
			$personPoints[$team][] = $row;
		}
		
		//fetch team special data
		$query = mysqli_query($conn, $teamPointsSpecialSql);
		while ($row = mysqli_fetch_assoc($query)) {
			$teamPointsSpecial[$team][] = $row;
		}
		
	}	
		
	$datesWithPoints = array();

	foreach ($teams as $i => $team) {
		$lastTeamPoints = 0;
		$lastPersonPoints = 0;
		$specialPoints = 0;
		foreach ($dates as $j => $date) {
			$teamDataForDate = getDataForDate($teamPoints[$team], $date);
			
			//TEAM points
			if (sizeof($teamDataForDate) == 0) {
 				$teamDataForDate = array("team" => $team, "datestr" => $date, "cumulative_sum" => $lastTeamPoints);
			}			
			$lastTeamPoints = $teamDataForDate["cumulative_sum"];
			
			
			// PERSON Points
			$personDataForDate = getDataForDate($personPoints[$team], $date);
			if (sizeof($personDataForDate) == 0) {
				$personDataForDate = array("team" => $team, "datestr" => $date, "cumulative_sum" => $lastPersonPoints);
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

	$jsonData = json_encode($dateWithPoints);

	$fp = fopen('./teamGraph.json', 'w');
	fwrite($fp, $jsonData);
	fclose($fp);

	mysql_close($server);

	/////////////////////////////////////////////////////////////

	function getDataForDate($arrayData, $date) {
		$result = array();
		foreach ($arrayData as $i => $value) {
			if ($value["datestr"] == $date) {
				$result = $value;
			}
		}
		return $result;
	}

	function loadTeamSpecialPoints($data, $team) {
		
	}

?>