<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class TeamPresenter extends BasePresenter
{

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
		error_reporting(E_ALL & ~E_NOTICE);
	}

	public function renderDefault()
	{
		$this->template->team = $this->database->table('team')
			->where('camp', CAMP)
			->where('active', 1);
		$this->template->points = $this->database->table('points_team')
			->where('active', 1);
		$this->template->results = $this->database->table('results_team')
			->where('active', 1)
			->where('camp', CAMP);

		$this->template->child = $this->database->table('child')
			->select('SUM(:points_child.points) AS sumPoints')
			->where(':child_camp.camp_id = ?', CAMP)
			->where(':points_child.active = ?', 1)
			->where(':points_child.camp = ?', CAMP)
			->group(':child_camp.team_id')
			;
		
		$this->prepareDataForGraph();
	}

	public function renderPersonal() {
		// $this->template->children = $this->database->table('child_points');
		//součet bodů u dané osoby
		/*$this->template->sumPoints = $this->database->table('child')
			->select('child.id, name, surname, SUM(points) AS sum')
			->where(':points_child.active = ?', 1)
			->where(':points_child.camp = ?', CAMP)
			->group('child.id')
			->order('sum DESC');*/

		$this->template->sumPoints = $this->database->query("SELECT
		child.id, name, surname , IFNULL(t2.sum, 0) as sum
			FROM child
			LEFT JOIN child_camp cc on cc.child_id = child.id
			LEFT JOIN 
				(
					SELECT childId, sum(points) as sum FROM points_child where camp = " . CAMP . " group by childId
				) as t2 on child.id = t2.childId
			WHERE cc.camp_id = " . CAMP ."
			group by child.id
			ORDER BY `sum`  DESC, surname");
	}

	public function renderDetail($id)
	{
		$this->template->team = $this->database->table('team')->get($id);
		$this->template->results = $this->database->table('results_team')
			->select('*')
			->where('active', 1)
			->where('team', $id) 
			->where('camp', CAMP)
			->order('date');

		$this->template->points = $this->database->table('points_team')
			->where('active', 1)
			->where('team', $id);

		$this->template->lead = $this->database->table('lead');
		$this->template->child = $this->database->table('child')
			->where(':child_camp.team_id = ?', $id)
			->where('active', 1)
			->order('surname ');

		$this->template->game = $this->database->table('game');

		$this->template->sumPoints = $this->database->query("SELECT
		pc.childId as id, name, surname, SUM(pc.points) AS sum
			FROM points_child pc
				LEFT JOIN child_camp cc ON pc.childId = cc.child_id
				LEFT JOIN child c ON pc.childId = c.id
			WHERE cc.team_id = " . $id . " AND cc.camp_id = " . CAMP . " AND pc.camp = " . CAMP . "
			GROUP BY pc.childId ORDER BY pc.childId");
 
	}

	public function renderPeople($id) {
		$this->template->team = $this->database->table('team')->get($id);

		$this->template->children = $this->database->table('child')
			->select('id, name, surname')
			->where(':child_camp.camp_id', CAMP)
			->where(':child_camp.team_id', $id)
			->where('active', 1)
			->order('surname');
	}

	public function renderAddPoints($id) {
		$this->template->team = $this->database->table('team')->get($id);
	}

	public function renderLead() {
		$this->template->leadPoints = $this->database->query("SELECT
		l.id, l.name, l.surname , IFNULL(t2.sum, 0) as sum
			FROM lead l
			LEFT JOIN lead_camp lc on lc.lead_id = l.id
			LEFT JOIN 
				(
					SELECT lead_id, sum(points) as sum FROM points_lead where camp = " . CAMP . " group by lead_id
				) as t2 on l.id = t2.lead_id
			WHERE lc.camp_id = " . CAMP . "
			group by l.id
			ORDER BY `sum`  DESC, surname");
	}

	public function createComponentAddPointsForm() {
		$form = new Nette\Application\UI\Form;
		
		$form->addText('points', "Počet bodů")
			->addRule(\Nette\Forms\Form::INTEGER, 'Počet bodů musí být číslo!')
			->setRequired('Zadejte počet bodů!');

		$form->addSelect('game', 'Hra', $this->database->table('game')->where('camp = ? OR camp = ?', CAMP, 0)->fetchPairs('id','name'))
			->setAttribute('size', 6)			
			->setRequired('Vyberte hru!');

		$form->addHidden('team', $this->getParameter('id'));
		//$form->addHidden('date', mt_rand(15, 27) . ".07." . $this->database->table('camp')->get(CAMP)->year . " " . date('H:i:s', Time()));
		$form->addHidden('date', date('d.m.Y H:i:s', Time()));

		$form->addSubmit('send', 'Uložit');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array($this, 'addPointsFormSucceeded');
		return $form;
	}

	public function addPointsFormSucceeded($form, $values) {
		try {			
			$values["id"] = "";
			$values["active"] = 1;
			$values["camp"] = CAMP;
			
			//insert data into db
			$this->database->table("points_team")->insert($values);

			$this->flashMessage('Body byly úspěšně vloženy.');
			$this->redirect('Team:detail', $this->getParameter('id'));
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení bodů se nezdařilo, zkuste to prosím znovu.");
		}
	}

	public function createComponentAddPointsLeadForm() {
		$form = new Nette\Application\UI\Form;
		
		$form->addSelect('lead_id', 'Vedoucí', 
							$this->database->table('lead')
								->select('lead.*, concat(lead.name, " ", lead.surname) AS `fullname`')
								->where(':lead_camp.camp_id = ?', CAMP)
								->fetchPairs('id', 'fullname'))
				->setRequired('Vyberte člena vedení');
		
		$form->addText('points', "Počet bodů")
			->addRule(\Nette\Forms\Form::INTEGER, 'Počet bodů musí být číslo!')
			->setRequired('Zadejte počet bodů!');

		$form->addSelect('game', 'Hra', $this->database->table('game')->where('camp = ? OR camp = ?', CAMP, 0)->fetchPairs('id','name'))
			->setAttribute('size', 6)			
			->setRequired('Vyberte hru!');

		$form->addTextArea('note', 'Poznámka:')
			->addRule($form::MAX_LENGTH, 'Poznámka je příliš dlouhá', 500);

		//$form->addHidden('date', mt_rand(15, 27) . ".07." . $this->database->table('camp')->get(CAMP)->year . " " . date('H:i:s', Time()));
		$form->addHidden('date', date('d.m.Y H:i:s', Time()));

		$form->addSubmit('send', 'Uložit');

		// call method on success
		$form->onSuccess[] = array($this, 'addPointsLeadFormSucceeded');
		return $form;
	}

	public function addPointsLeadFormSucceeded($form, $values) {
		try {			
			$values["id"] = "";
			$values["active"] = 1;
			$values["camp"] = CAMP;
			
			//insert data into db
			$this->database->table("points_lead")->insert($values);

			$this->flashMessage('Body byly úspěšně vloženy.');
			$this->redirect('Team:lead');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení bodů se nezdařilo, zkuste to prosím znovu.");
		}
	}

	private function getDatesInRange($first, $last, $step = '+1 day', $output_format = 'd.m.Y' ) {

		$dates = array();
		$current = strtotime($first);
		$last = strtotime($last);

		while( $current <= $last ) {

			$dates[] = date($output_format, $current);
			$current = strtotime($step, $current);
		}

		return $dates;
	}
	
	
	private function utf8ize( $mixed ) {
		if (is_array($mixed)) {
			foreach ($mixed as $key => $value) {
				$mixed[$key] = $this->utf8ize($value);
			}
		} elseif (is_string($mixed)) {
			return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
		}
		return $mixed;
	}

	private function prepareDataForGraph() {
		$username = "jirigalis";
		$password = "i62kgbtr";
		$host = "innodb.endora.cz";
		$database="campis";
		$conn = mysqli_connect($host, $username, $password, $database);
		$camp_id = CAMP;
		
		//load teams
		$teamQuery = mysqli_query($conn, "SELECT id, CONVERT(CAST(name as BINARY) USING utf8) as name FROM team WHERE camp=" . $camp_id);
		$teams= array();	
		while ($row = mysqli_fetch_assoc($teamQuery)) {
			$teams[] = array("id" => $row["id"], "name" => $row["name"]);
		}

		
		// prepare dates for chart	
		$campDates = mysqli_fetch_object(mysqli_query($conn, "SELECT start, end FROM camp WHERE id = ".$camp_id." LIMIT 1"));
		$endDate = $campDates->end;
		$today = date("d.m.Y");
		
		if (strtotime($endDate)>strtotime($today)) {
			$endDate = $today;
		}
		
		$dates = $this->getDatesInRange($campDates->start, $endDate);
		
		
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

		foreach ($teams as $i => $teamArr) {
			$team = intval($teamArr["id"]);
			$lastTeamPoints = 0;
			$lastPersonPoints = 0;
			$specialPoints = 0;
			foreach ($dates as $j => $date) {
				$teamDataForDate = $this->getDataForDate($teamPoints[$team], $date);
				
				//TEAM points
				if (sizeof($teamDataForDate) == 0) {
					$teamDataForDate = array("team" => $teamArr["name"], "datestr" => $date, "cumulative_sum" => $lastTeamPoints);
				}			
				$lastTeamPoints = $teamDataForDate["cumulative_sum"];
				
				
				// PERSON Points
				$personDataForDate = $this->getDataForDate($personPoints[$team], $date);
				if (sizeof($personDataForDate) == 0) {
					$personDataForDate = array("team" => $teamArr["name"], "datestr" => $date, "cumulative_sum" => $lastPersonPoints);
				}
				
				
				$teamDataForDate["cumulative_sum"] += $personDataForDate["cumulative_sum"];
				
				//save last person value
				$lastPersonPoints = $personDataForDate["cumulative_sum"];
					
				
				//ADD TEAM SPECIAL POINTS
				$teamSpecialDataForDate = $this->getDataForDate($teamPointsSpecial[$team], $date);
				if (sizeof($teamSpecialDataForDate) > 0) {
					$specialPoints = $teamSpecialDataForDate["cumulative_sum"];
				}
				$teamDataForDate["cumulative_sum"] += $specialPoints;
							
				//add data to final array
				$dateWithPoints[] = $teamDataForDate;
			}
		}

		$jsonData = json_encode($this->utf8ize($dateWithPoints));

		$fp = fopen('teamGraph.json', 'w');
		fwrite($fp, $jsonData);
		fclose($fp);
		
		mysqli_close($conn);
	}

	private function getDataForDate($arrayData, $date) {
		$result = array();
		foreach ((array) $arrayData as $i => $value) {
			if ($value["datestr"] == $date) {
				$result = $value;
			}
		}
		return $result;
	}


}