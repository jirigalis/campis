<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class RacePresenter extends BasePresenter
{

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}
	
	public function renderDefault() {
		$this->template->race = $this->database->table('race')
			->where('camp', CAMP)
			->where('active', 1)
			->order('date');

		$this->template->game = $this->database->table('game')
			->where('active', 1);

		$this->template->state = $this->database->table('race_state');
	}

	public function renderNewRace() {
	}

	public function renderTeam() {
		$this->template->race = $this->database->table('results_team')
			->where('camp', CAMP)
			->where('active', 1)
			->group('game');

		$this->template->game = $this->database->table('game')
			->where('active', 1);
			
		$this->template->team = $this->database->table('team')
			->where('camp', CAMP);
	}

	public function renderNewTeamRace() {
		$this->template->team = $this->database->table('team')
			->where(':child_camp.camp_id = ?', CAMP);
	}

	public function renderDetail($id) {
		$this->template->race = $this->database->table('race')->get($id);
		$this->template->spacing = $this->database->table('race')->get($id)->spacing;
		$this->template->start = "00:00:00";

		//XML file
		$this->template->xml_file = simplexml_load_file("./raceData/" . $id . ".xml");

		$this->template->tmp = new Race($this->database, $id, "./raceData/" . $id . ".xml");
		$this->template->tmp->loadCategories();
		$this->template->tmp->fillCategories();


		if (isset($_POST["send"])) {
		 	$xml_obj = new \DOMDocument();
	        $xml_obj->load("./raceData/" . $id . ".xml");

	        $child = $this->database->table('child')->where('active', 1)
				->where(':child_camp.camp_id = ?', CAMP)
				->order(':child_camp.category_id, surname')
				;
            
            //zapsání dětí
			$i = 0;
            foreach ($child as $n => $ch) {
	            //vyběhl závodník?
	            if (!isset($_POST["vybehl_" . $ch['id']])) $vybehl=0; else $vybehl=1;

	            $xml_obj->getElementsByTagName('VYBEHL')->item($i)->nodeValue = $vybehl;                
	            //zápis nových hodnot - časů
	            $xml_obj->getElementsByTagName('START')->item($i)->nodeValue = $_POST["start_" . $ch['id']];
	            $xml_obj->getElementsByTagName('CIL')->item($i)->nodeValue = $_POST["cil_" . $ch['id']];
	            $xml_obj->getElementsByTagName('TRMIN')->item($i)->nodeValue = $_POST["trmin_" . $ch['id']];
	            $xml_obj->getElementsByTagName('STOPCAS')->item($i)->nodeValue = $_POST["stopcas_" . $ch['id']];
	            $xml_obj->getElementsByTagName('VYSLEDNY')->item($i)->nodeValue = $_POST["vysledny_" . $ch['id']];
	            $i++;
	        }               
	        $xml_obj->save("./raceData/" . $id . ".xml");

	        $this->database->table('race')->where('id', $id)->update(Array('state' => 2));

			$this->flashMessage("Data byla odeslána.");
			$this->redirect("Race:");
		}
	}

	public function renderTeamResult($id) {
		$this->template->teamRace = $this->database->table('results_team')->get($id);
	}

	public function renderResult($id, $insertPoints) {
		$this->template->race = $this->database->table('race')->get($id);
		$this->template->raceName = $this->database->table('game')
			->get($this->template->race->game)->name;

		//XML file
		$xml_file = simplexml_load_file("./raceData/" . $id . ".xml");

		$race = new Race($this->database, $id, "./raceData/" . $id . ".xml");
		
		//$this->template->race->loadCategories();
		//$this->template->race->
		$this->template->raceData = $race->evalRace(0);
		$this->template->results = $this->template->raceData->getCategories();

		if ($insertPoints) {
			foreach($this->template->results as $item) {
				$item->sort();

				for($i=1; $i<=RANKED; $i++) {
					$child = $item->getPlace($i);
					//vyběhla osoba s daným ID?
					if ($child != false && $child->getVybehl() == 1) {

						$values['id'] = "";
						$values['active'] = "1";
						$values['childId'] = $child->getId();
						$values['points'] = constant("POINTS_" . $i);;
						$values['game'] = $this->template->race->game;
						$values['date'] = date('d.m.Y H:i:s', Time());
						$values['camp'] = CAMP;

						$this->database->table('points_child')->insert($values);
					}
				}
			}
	        $this->database->table('race')->where('id', $id)->update(Array('state' => 3));
	        $this->flashMessage('Body byly úspěšně zapsány do databáze.');
		}
	}

	public function createComponentNewRaceForm() {
		$form = new Nette\Application\UI\Form;
		
		$form->addSelect('game', 'Závod', $this->database->table('game')->where('type', 1)->fetchPairs('id','name'))			
			->setRequired('Vyberte závod!');

		$form->addText('note', 'Poznámka');

		$form->addText('spacing', 'Rozestup (ve formátu MM:SS)')
			->setAttribute('value', '02:00');

		$form->addHidden('state', 1);
		$form->addHidden('date', date('d.m.Y H:i:s', Time()));

		$form->addSubmit('send', 'Uložit');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array($this, 'newRaceFormSucceeded');
		return $form;
	}

	public function newRaceFormSucceeded($form, $values) {
		try {			
			$values["id"] = "";
			$values["active"] = 1;
			$values["camp"] = CAMP;
			
			//insert data into db
			$this->database->table("race")->insert($values);
			$maxid = $this->database->table("race")->max('id');

			//zpracování dat a vytvoření souborů k závodu
			//vytvoření souboru, do kterého budem zapisovat nový závod
            $filename = "./raceData/" . $maxid . ".xml";
            $file = fopen($filename, "a"); //otevření souboru pro přidávání, soubor se vytvoří, pokud neexistuje
            //chmod($filename, 777);

            $xml_hlavicka = "<zavod rozestup = \"" . "00:" . $values["spacing"] . "\">";
            $xml_paticka = "</zavod>";
            $start = "00:00:00";

            $return  = fwrite($file, $xml_hlavicka);

            $child = $this->database->table('child')->where('active', 1)
            	->select('*, :child_camp.category_id AS category')
							->where(':child_camp.camp_id = ?', CAMP)
							->order(':child_camp.category_id, surname')
							;
            
            //zapsání dětí
            foreach ($child as $id => $ch) {

                fwrite($file, "
					<OSOBA id=\"" . $ch["id"] . "\">
						<JMENO>" . $ch["surname"] . " " . $ch["name"] . "</JMENO>
						<KATEGORIE>" . $ch["category"] . "</KATEGORIE>
						<VYBEHL></VYBEHL>
		                                <CASY>
						  <START>" . $start . "</START>
						  <CIL></CIL>
						  <STOPCAS></STOPCAS>
						  <TRMIN></TRMIN>
						  <VYSLEDNY></VYSLEDNY>
						</CASY>
						<PORADI>0</PORADI>
					</OSOBA>
		                        ");
                $start = $this->AddTime($start, "00:" . $values["spacing"]);
    		}

            fwrite($file, $xml_paticka);
            fclose($file);

			$this->flashMessage('Závod byl úspěšně vytvořen.');
			$this->redirect('Race:');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení závodu se nezdařilo, zkuste to prosím znovu.");
		}
	}

	public function createComponentNewTeamRaceForm() {
		$form = new Nette\Application\UI\Form;
		
		$form->addSelect('game', 'Závod', $this->database->table('game')->where('(type = ? OR type = ? OR type = ?) AND (camp = ? OR camp = ?)', 2, 3, 4, CAMP, 0)->fetchPairs('id','name'))			
			->setRequired('Vyberte hru!')
			->setPrompt('Vyberte hru');
			
		//vytvoření prvků pro zadávání bodů
		$team = $this->database->table('team')->where('camp', CAMP);
		$rankContainer = $form->addContainer('rankArray');
		
		foreach ($team as $id => $t) {
			$rankContainer->addText('rank_team_' . $id, 'Oddíl ' . $t->number . ' Pořadí')
				->setRequired('Je třeba vyplnit pořadí!');
		}

		$pointsContainer = $form->addContainer('pointsArray');
		foreach ($team as $id => $t) {
			$pointsContainer->addText('points_team_' . $id, 'Oddíl ' . $t->number . ' Body')
				->setRequired('Je třeba vyplnit body!')
				->setAttribute('value', constant("TEAM_POINTS_" . $t->number));
		}

		$form->addTextArea('note', 'Poznámka');
		$form->addHidden('date', rand(15, 27) . ".07." . $this->database->table('camp')->get(CAMP)->year . " " . date('H:i:s', Time()));
		//$form->addHidden('date', date('d.m.Y H:i:s', Time()));

		$form->addSubmit('send', 'Uložit');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array($this, 'newTeamRaceFormSucceeded');
		return $form;
	}

	public function newTeamRaceFormSucceeded($form, $values) {
		try {			
			$values["id"] = "";
			$values["active"] = 1;
			$values["camp"] = CAMP;

			//kopie pole s výsledky
			$rankArray = $values['rankArray'];
			$pointsArray = $values['pointsArray'];
			unset($values['rankArray']);
			unset($values['pointsArray']);
			
			//$team = 1;
			$team = $this->database->table('team')
				->where('camp', CAMP);

			//insert data into db
			foreach ($team as $t) {
				$values['team'] = $t->id;
				$values['rank'] = $rankArray["rank_team_" . $t->id];
				$values['points'] = $pointsArray["points_team_" . $t->id];
				$this->database->table("results_team")->insert($values);
			}

			//$this->flashMessage(var_dump($values));
			$this->flashMessage('Závod byl úspěšně vytvořen.');
			//$this->redirect('Race:');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení závodu se nezdařilo, zkuste to prosím znovu.");
		}
	}

	public function createComponentEditTeamRaceForm() {
		$form = new Nette\Application\UI\Form;

		$gameId = $this->getParameter('id');
		$teamResult = $this->database->table('results_team')->where('(game = ? AND camp = ?)', $gameId, CAMP);
		$team = $this->database->table('team');
		
		$form->addSelect('game', 'Závod', $this->database->table('game')->where('(camp = ? OR camp = ?)', CAMP, 0)->fetchPairs('id','name'))			
			->setRequired('Vyberte hru!')
			->setDisabled('true')
			->setDefaultValue($gameId)
			->setPrompt('Vyberte hru');
			
		//vytvoření prvků pro zadávání bodů
		$team = $this->database->table('team')->where('camp', CAMP);
		$rankContainer = $form->addContainer('rankArray');
		
		foreach ($teamResult as $id => $t) {
			$rankContainer->addText('rank_result_' . $t->id, 'Oddíl ' . $team->get($t->team)->number . ' Pořadí')
				->setValue($t->rank)
				->setRequired('Je třeba vyplnit pořadí!');
		}

		$pointsContainer = $form->addContainer('pointsArray');
		foreach ($teamResult as $id => $t) {
			$pointsContainer->addText('points_result_' . $id, 'Oddíl ' .  $team->get($t->team)->number . ' Body')
				->setRequired('Je třeba vyplnit body!')
				->setValue($t->points)
				->setAttribute('value', constant("TEAM_POINTS_" .  $team->get($t->team)->number));
		}

		$form->addTextArea('note', 'Poznámka');

		$form->addSubmit('send', 'Uložit');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array($this, 'editTeamRaceFormSucceeded');
		return $form;
	}

	public function editTeamRaceFormSucceeded($form, $values) {
		try {

			$gameId = $this->getParameter('id');

			$teamResult = $this->database->table('results_team')->where('(game = ? AND camp = ?)', $gameId, CAMP);
			$rankArray = $values['rankArray'];
			$pointsArray = $values['pointsArray'];
			unset($values['rankArray']);
			unset($values['pointsArray']);
			
			foreach ($teamResult as $key => $tr) {
				$values["rank"] = $rankArray['rank_result_'.$tr->id];
				$values["points"] = $pointsArray['points_result_'.$tr->id];
				$values["id"] = $tr->id;
				$values["game"] = $tr->game;
				
				$tr->update($values);
			}

			$this->flashMessage('Oddílový závod byl úspěšně změněn.');
			//$this->redirect('Race:');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení závodu se nezdařilo, zkuste to prosím znovu.");
		}
	}

	/**
	 * Funkce pro připočítávání časů v závodech. Na vstupu je čas a hodnota, která se
	 * k němu bude přičítat. Na výstupu je nová hodnota času (string).
	 * Funkce neověřuje správnost dat.
	 */
	public function addTime($time, $add) {
		$new_time = localtime(strtotime($time), 1);
		$new_add = localtime(strtotime($add), 1);;
		
		$new_time["tm_sec"] += $new_add["tm_sec"];
		
		//úprava hodnot, když dojde v přičítání k přetečení
		if ($new_time["tm_sec"] >= 60) {
	     $new_time["tm_min"]++;
	     $new_time["tm_sec"] -= 60;
		}
		if ($new_time["tm_min"] >= 60) {
	     $new_time["tm_hour"]++;
	     $new_time["tm_min"] -= 60;
		}
		$new_time["tm_min"] += $new_add["tm_min"];
		if ($new_time["tm_min"] >= 60) {
	     $new_time["tm_hour"]++;
	     $new_time["tm_min"] -= 60;
		}
		$new_time["tm_hour"] += $new_add["tm_hour"];

		//rozšíření délky hodnot na dvojciferné - doplnění nul na začátek
		if (strlen($new_time["tm_sec"]) == 1)
		  $new_time["tm_sec"] = "0" . $new_time["tm_sec"];
		if (strlen($new_time["tm_min"]) == 1)
		  $new_time["tm_min"] = "0" . $new_time["tm_min"];
		if (strlen($new_time["tm_hour"]) == 1)
		  $new_time["tm_hour"] = "0" . $new_time["tm_hour"];

		//spojení do výsledného řetězce
		$vysl = $new_time["tm_hour"] . ":" . $new_time["tm_min"]  . ":" . $new_time["tm_sec"];

		return $vysl;
	} // END function AddTime

}