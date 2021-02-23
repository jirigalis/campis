<?php
    include "../../app/presenters/const.php";

    $servername = "innodb.endora.cz";
    $username = "jirigalis";
    $password = "i62kgbtr";
    $database = "campis";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // parse params
    if (isset($_GET["action"])) {
        if ($_GET["action"] == "teamCount") {
            getTeamCount($conn);
        } else if ($_GET["action"] == "getTeams") {
            getTeams($conn);
        } else if ($_GET["action"] == "getChildren") {
            getChildren($conn);
        } else if ($_GET["action"] == "getLead") {
            getLead($conn);
        } else if ($_GET["action"] == "getTopChildrenForToday") {
            getTopChildrenForToday($conn);
        } else if ($_GET["action"] == "getTopLeadForToday") {
            getTopLeadForToday($conn);
        } else if ($_GET["action"] == "getReloadFlag") {
            getReloadLivePointsFlag();
        }

    } else if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if (isset($_POST['reload']) && $_POST["reload"] == "false") {
            setReloadFlagFalse();
        } else { 
            savePoints($conn);
        }
    } else {
        getTeamPoints($conn);
    }

    /////////// FUNCTIONS ////////////

    function getTeamPoints($conn) {
        $teamArray = [];
        $finalTeamArray = [];
        $teamPointsArray = [];
        
        $teamQuery = "SELECT id, number, name FROM team WHERE camp = " . CAMP;
        $resultTeam = $conn->query($teamQuery);

        $teamPointsQuery = "SELECT team, name, SUM(points) as sumPoints FROM `points_team` "
            . " left join team on team.id = points_team.team WHERE points_team.camp = " . CAMP . " group by team";
        $resultTeamPoints = $conn->query($teamPointsQuery);

        $teamResultsQuery = "SELECT rt.team as team, t.name, sum(rt.points) as sumPoints FROM `results_team` rt"
            . " left join team t on t.id = rt.team where rt.camp = " . CAMP . " group by rt.team";
        $resultTeamResults = $conn->query($teamResultsQuery);

        $childPointsQuery = "SELECT sum(pt.points) as sumPoints, cc.team_id as team FROM `points_child` pt" 
            . " left join child_camp cc on cc.child_id = pt.childId WHERE pt.camp = " . CAMP 
            . " and cc.camp_id = " . CAMP . " group by cc.team_id";
        $resultChildPoints = $conn->query($childPointsQuery);
        
        // prepare team array
        while ($teamRow = $resultTeam->fetch_assoc()) {
            array_push($teamArray, ["id" => $teamRow["id"], "name" => $teamRow["name"], "sum" => 0]);
        }

        //prepare teamPoints array
        while ($teamPointsRow = $resultTeamPoints->fetch_assoc()) {
            array_push($teamPointsArray, ["id" => $teamPointsRow["team"], "sum" => $teamPointsRow["sumPoints"]]);
        }

        //prepare teamResults array
        while ($teamResultsRow = $resultTeamResults->fetch_assoc()) {
            array_push($teamPointsArray, ["id" => $teamResultsRow["team"], "sum" => $teamResultsRow["sumPoints"]]);
        }

        //prepare childPoints array
        while ($childPointsRow = $resultChildPoints->fetch_assoc()) {
            array_push($teamPointsArray, ["id" => $childPointsRow["team"], "sum" => $childPointsRow["sumPoints"]]);
        }

        foreach ($teamArray as $key => $teamArray) {
            foreach ($teamPointsArray as $key => $val) {
                if ($teamArray["id"] == $val["id"]) {
                    $teamArray["sum"] += ($val["sum"]);
                }
            }
            array_push($finalTeamArray, $teamArray);
        }
        
        header('Content-Type: application/json');
        echo json_encode($finalTeamArray);
    }

    function getTeamCount($conn) {
        $query = "SELECT count(id) as `count` from team where camp = " . CAMP;
        $result = $conn->query($query);

        echo $result->fetch_object()->count;
    }

    function getTeams($conn) {
        $teamsQuery = "SELECT id, number, name FROM team WHERE camp = " . CAMP;
        $resultTeams = $conn->query($teamsQuery);

        $teamsArray = [];
        while($teamRow = $resultTeams->fetch_assoc()) {
            array_push($teamsArray, ["id" => $teamRow["id"], "name" => $teamRow["name"], "number" => $teamRow["number"]]);
        }

        header('Content-Type: application/json');
        echo json_encode($teamsArray);
    }

    function getChildren($conn) {
        $teamId = $_GET["teamId"];
        $childrenQuery = "SELECT c.id as id, c.name as name, c.surname as surname FROM child c LEFT JOIN child_camp cc ON c.id = cc.child_id WHERE cc.team_id = " . $teamId;
        $resultsChildren = $conn->query($childrenQuery);

        $childrenArray = [];
        while($childRow = $resultsChildren->fetch_assoc()) {
            array_push($childrenArray, ["id" => $childRow["id"], "name" => $childRow["name"] . " " . $childRow["surname"]]);
        }

        header('Content-Type: application/json');
        echo json_encode($childrenArray);
    }

    function getLead($conn) {
        $leadQuery = "SELECT l.id as id, l.name as name, l.surname as surname FROM lead l LEFT JOIN lead_camp lc ON l.id = lc.lead_id WHERE lc.camp_id = " . CAMP . " ORDER BY surname";
        $resultsLead = $conn->query($leadQuery);

        $leadArray = [];
        while($leadRow = $resultsLead->fetch_assoc()) {
            array_push($leadArray, ["id" => $leadRow["id"], "name" => $leadRow["name"] . " " . $leadRow["surname"]]);
        }

        header('Content-Type: application/json');
        echo json_encode($leadArray);
    }

    function getTopChildrenForToday($conn) {
        $query = "SELECT pc.childId, c.name, c.surname, sum(pc.points) as sum
            FROM points_child pc left join child c on pc.childId = c.id
            WHERE pc.camp = " . CAMP . " AND date like CONCAT(DATE_FORMAT(CURDATE(),'%e.%m.%Y'), '%')
            group by pc.childId
            having sum > 0
            order by sum desc
            limit 3";

        $result = $conn->query($query);

        $childrenArray = [];
        while($row = $result->fetch_assoc()) {
            array_push($childrenArray, [
                "id" => $row["childId"],
                "name" => $row["name"] . " " . $row["surname"],
                "sum" => $row["sum"]
            ]);
        }
        header('Content-Type: application/json');
        echo json_encode($childrenArray);
    }

    function getTopLeadForToday($conn) {
        $query = "SELECT
            l.id, l.name, l.surname , IFNULL(t2.sum, 0) as sum
                FROM lead l
                LEFT JOIN lead_camp lc on lc.lead_id = l.id
                LEFT JOIN 
                    (
                        SELECT lead_id, sum(points) as sum FROM points_lead where camp = " . CAMP . "
                         AND date like CONCAT(DATE_FORMAT(CURDATE(),'%e.%m.%Y'), '%')
                        group by lead_id
                    ) AS t2 ON l.id = t2.lead_id
                WHERE lc.camp_id = " . CAMP . "
                GROUP by l.id
                HAVING sum < 0
                ORDER BY `sum` LIMIT 3";

        $leadArray = [];
        
        if (POINTS_LEAD) {
            $result = $conn->query($query);        
            while($row = $result->fetch_assoc()) {
                array_push($leadArray, [
                    "id" => $row["id"],
                    "name" => $row["name"] . " " . $row["surname"],
                    "sum" => $row["sum"]
                ]);
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($leadArray);
    }

    function getReloadLivePointsFlag() {
        echo LIVE_POINTS_RELOAD;
    }

    function savePoints($conn) {
        $data = $_POST;
        $data["active"] = 1;
        $data["game"] = 80; // ID speciální hry pro tento případ

        if (is_numeric($data["points"])) {

            if (isset($data["teamId"]) && $data["teamId"] > 0) {
                $query = "INSERT INTO points_team (active, team, game, points, date, camp, note)
                            VALUES (
                            '". $data['active'] ."', 
                            '". $data['teamId'] ."', 
                            '". $data['game'] ."', 
                            '". $data['points'] ."', 
                            '". date('d.m.Y H:i:s', Time()) ."', 
                            '". CAMP ."',
                            '". $data['note'] ."')";
            } else if (isset($data["leadId"]) && $data["leadId"] > 0) {
                $query = "INSERT INTO points_lead (active, lead_id, game, points, date, camp, note)
                            VALUES (
                            '". $data['active'] ."', 
                            '". $data['leadId'] ."', 
                            '". $data['game'] ."', 
                            '". $data['points'] ."', 
                            '". date('d.m.Y H:i:s', Time()) ."', 
                            '". CAMP ."',
                            '". $data['note'] ."')";
            } else {
                $query = "INSERT INTO points_child (active, childId, points, game, date, camp, note)
                            VALUES (
                            '". $data['active'] ."', 
                            '". $data['childId'] ."', 
                            '". $data['points'] ."', 
                            '". $data['game'] ."', 
                            '". date('d.m.Y H:i:s', Time()) ."', 
                            '". CAMP ."',
                            '". $data['note'] ."')";
            }


            $res = $conn->query($query);
            if ($res === TRUE) {
                echo "New record created successfully";
            } else {
                echo "Error: " . $query . "<br>" . $conn->error;
            }
        }
    }

    function setReloadFlagFalse() {
        $fname = __DIR__ . "/../../app/presenters/const.php";
        $out = file_get_contents(htmlspecialchars($fname), NULL, NULL, 5);

        $out = str_replace('("LIVE_POINTS_RELOAD", "' . LIVE_POINTS_RELOAD . '")', '("LIVE_POINTS_RELOAD", "0")', $out);
        $out = "<?php" . $out;
        $file = fopen($fname, "w");
        fwrite($file, $out);
        fclose($file);
    }
    
    $conn->close();

?>

