<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class SettingsPresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
	//private $database;

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderDefault() {
		$this->template->camp = $this->database->table('camp');
		$this->template->child = $this->database->table('child');		

		//body za bobříky
		$children = $this->database->table('child')
			->where(':child_camp.camp_id = ?', CAMP);

		/*$values = Array();
		//for ($i=0; $i<13; $i++) {	// pro každý den přidej body
			foreach ($children as $child) {

				if (rand(1,10) > 7) {
					$values['id'] = "";
					$values['active'] = "1";
					$values['childId'] = $child->id;
					$values['points'] = rand(2,5)*10; //body za bobříky
					//$values['points'] = rand(0,10); //body za hodnocení jednotlivců
					//$values['game'] = "15"; //osobní hodnocení
					$values['game'] = "65"; //bobříci
					//$values['date'] = date('d.m.Y H:i:s', Time());						
					$values['camp'] = CAMP;

					//random date
					$values['date'] = rand(15, 26) . ".07." . $this->database->table('camp')->get(CAMP)->year . " " . date('H:i:s', Time());
					
					//date pro hodnocení jednotlivců
					//$day = $i+13;
					//$values['date'] =  $day . ".07." . $this->database->table('camp')->get(CAMP)->year . " " . date('H:i:s', Time());
					//dump($values['date']);
					$this->database->table('points_child')->insert($values);
				}
			}
		//}/**/

		//nějaký update
		//$this->database->table('points_child')->where('game', 
	}

	public function renderNewCamp() {
		$this->template->camp = $this->database->table('camp');
	}

	public function renderNewChild() {
		$this->template->team = $this->database->table('team');
	}

	public function renderNewLead() {
		$this->template->function = $this->database->table('function');		

		$this->template->lead = $this->database->table('lead')
			->select('*')
			->select(':lead_camp.function_id AS function')
			->where(':lead_camp.camp_id = ?', CAMP);
	}

	public function renderNewGame() {
	}

	public function renderTeam() {
		$this->template->teamNumbers = $this->database->table('team')
					->select('id')
					->where('camp', CAMP)
					->where('active', 1);
	}


	public function renderSetRanked() {	
	}

	/**
	 * Child parser form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentChildParserForm() {
		$form = new Nette\Application\UI\Form;
		
		$form->addText('file', "Název souboru")
			->setRequired('Zadejte název souboru!');

		$form->addSubmit('send', 'Uložit');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array($this, 'childParserFormSucceeded');
		return $form;	
	}
	
	public function childParserFormSucceeded($form, $values) {
		
		try {			
			$file = fopen($values['file'], "r");

			$i = 0;
			$record = array();
			while (!feof ($file)) {
			    $row = fgets($file, 4096);
			    $record[$i] = explode(";", $row);
			    $name = explode(" ", $record[$i][0]);
			    //vložení dat do DB


				$insert["id"] = "";
				$insert["active"] = 1;
				$insert["rc"] = $record[$i][2];
				$insert["adress"] = $record[$i][1];
				$insert["name"] = $name[1];
				$insert["surname"] = $name[0];
				$insert["rc"] = str_replace("/", "", $insert["rc"]);
				$insert["rc"] = substr($insert["rc"], 0,10);

				$category = $this->getCategory($insert["rc"]);
				
				$exists = $this->database->table('child')->where('rc', $insert['rc'])->count('id');
				//insert data into db
				if ($exists == 0)
					$this->database->table('child')->insert($insert);

				$child_id = $this->database->table('child')->select('id')->where('rc', $insert['rc']);
				//dump($exists);

				//vložení vztahu osoba-tábor
				//$tmp["team_id"] = rand(6,9);     //dobyvatelé
				//$tmp["team_id"] = rand(14,17); //cesta
				//$tmp["team_id"] = rand(10,13); //stroj času
				//$tmp["team_id"] = rand(23,27); //test
				$tmp["team_id"] = trim($record[$i][3]);
				$tmp['child_id'] = $child_id;
				$tmp['camp_id'] = CAMP;
				$tmp['category_id'] = $category;
				$this->database->table('child_camp')->insert($tmp);

				$i++;
			}
			$this->template->record = $record; 
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení záznamů se nezdařilo, zkuste to prosím znovu.");
		}
	}

	/**
	 * New Camp form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentNewCampForm() {
		$form = new Nette\Application\UI\Form;
		
		$form->addText('name', "Název tábora")
			->setRequired('Zadejte název tábora!');

		$form->addText('year', 'Rok tábora')
			->setRequired('Zadejte rok tábora!');

		$form->addSubmit('send', 'Uložit');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = array($this, 'newCampFormSucceeded');
		return $form;
		
	}
	
	public function newCampFormSucceeded($form, $values) {
		
		try {			
			$values["id"] = "";
			$values["active"] = 1;
			//insert data into db
			$this->database->table("camp")->insert($values);

			$this->flashMessage('Nový tábor byl úspěšně založen.');
			$this->redirect('Settings:newCamp');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení tábora se nezdařilo, zkuste to prosím znovu.");
		}
	
	}

	protected function createComponentNewChildForm() {
		$form = new Nette\Application\UI\Form;

		$form->addText('name', "Jméno")
			->setRequired('Zadejte jméno!');

		$form->addText('surname', "Příjmení")
			->setRequired('Zadejte příjmení!');

		$form->addText('rc', 'Rodné číslo')
			->setRequired('Zadejte rodné číslo')
			->addRule(\Nette\Forms\Form::LENGTH, 'Délka musí být 10 znaků', 10);
		
		$form->addText('adress', 'Adresa');

		$form->addSelect('team', 'Oddíl:', $this->database->table('team')
				->where('camp', CAMP)
				->fetchPairs('id','name'))
            ->setPrompt('Zvolte oddíl');

        $form->addSubmit('send', 'Vložit data');

		$form->onSuccess[] = array($this, 'newChildFormSucceeded');
        return $form;
	}

	public function newChildFormSucceeded($form, $values) {
		try {
			$tmp["category_id"] = $this->getCategory($values['rc']);
			$values["id"] = "";
			$values["active"] = 1;
			$tmp['team_id'] = $values["team"];
			$tmp['camp_id'] = CAMP;
			
			//insert data into db			
			unset($values["team"]);
			
			$tmp['child_id'] = $this->database->table('child')->max('id');			
			$this->database->table("child")->insert($values);
			$this->database->table('child_camp')->insert($tmp);
			
			
			//přiřazení dítěte k táboru
			// $tmp
			
			

			$this->flashMessage('Data byla úspěšně vložena.');
			$this->redirect('Settings:newChild');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení dítěte se nezdařilo, zkuste to prosím znovu.");
		}
	}

	protected function createComponentSetRankedForm() {
		$form = new Nette\Application\UI\Form;

		$ranked = range(1, 15);
		$form->addSelect('ranked', 'Počet: ')->setItems($ranked, false)->setDefaultValue(RANKED);

		$form->addSubmit('send', 'Provést změnu');

		$form->onSuccess[] = array($this, 'setRankedFormSucceeded');
        return $form;
	}

	public function setRankedFormSucceeded($form, $values) {
		try {			
			$fname = __DIR__ . "/const.php";
            $out = file_get_contents(htmlspecialchars($fname), NULL, NULL, 5);

            $out = str_replace('("RANKED", "' . RANKED . '")', '("RANKED", "' . $values['ranked'] . '")', $out);

            $out = "<?php" . $out;
            $file = fopen($fname, "w");
            fwrite($file, $out);
            fclose($file);
			
			$this->flashMessage('Změna byla úspěšně provedena.');
			$this->redirect('Settings:setRanked');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Nepodařilo se změnit počet hodnocených míst!");
		}
	}

	protected function createComponentRacePointsForm() {
		$form = new Nette\Application\UI\Form;

		for($i = 1; $i<=RANKED; $i++) {
			$form->addText('points_' . $i, $i . ". ")
    			->addRule(Nette\Application\UI\Form::NUMERIC, 'Pouze číselné hodnoty!')
				->setValue(constant("POINTS_" . $i));
		}

		$form->addSubmit('send', "Uložit změny");

		$form->onSuccess[] = array($this, 'racePointsFormSucceeded');
		return $form;
	}

	public function racePointsFormSucceeded($values) {
		try {			
			$fname = __DIR__ . "/const.php";
            $out = file_get_contents(htmlspecialchars($fname), NULL, NULL, 5);

            for($i = 1; $i<=RANKED; $i++) {
	            $out = str_replace('("POINTS_' . $i . '", "' . constant("POINTS_" . $i) . '")',
	            				   '("POINTS_' . $i . '", "' . $values['points_' . $i]->getValue() . '")', $out);
	        }
            $out = "<?php" . $out;
            $file = fopen($fname, "w");
            fwrite($file, $out);
            fclose($file);
			
			$this->flashMessage('Změny byly úspěšně provedeny.');
			$this->redirect('Settings:racePoints');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Nepodařilo se provést změny!");
		}
	}

	protected function createComponentTeamRacePointsForm() {
		$form = new Nette\Application\UI\Form;

		for($i = 1; $i<=TEAM_COUNT; $i++) {
			$form->addText('team_points_' . $i, $i . ". ")
    			->addRule(Nette\Application\UI\Form::NUMERIC, 'Pouze číselné hodnoty!')
				->setValue(constant("TEAM_POINTS_" . $i));
		}

		$form->addSubmit('send', "Uložit změny");

		$form->onSuccess[] = array($this, 'teamRacePointsFormSucceeded');
		return $form;
	}

	public function TeamRacePointsFormSucceeded($values) {
		try {			
			$fname = __DIR__ . "/const.php";
            $out = file_get_contents(htmlspecialchars($fname), NULL, NULL, 5);

            for($i = 1; $i<=TEAM_COUNT; $i++) {
	            $out = str_replace('("TEAM_POINTS_' . $i . '", "' . constant("TEAM_POINTS_" . $i) . '")',
	            				   '("TEAM_POINTS_' . $i . '", "' . $values['team_points_' . $i]->getValue() . '")', $out);
	        }
            $out = "<?php" . $out;
            $file = fopen($fname, "w");
            fwrite($file, $out);
            fclose($file);
			
			$this->flashMessage('Změny byly úspěšně provedeny.');
			$this->redirect('Settings:teamRacePoints');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Nepodařilo se provést změny!");
		}
	}

	protected function createComponentSelectCampForm() {
		$form = new Nette\Application\UI\Form;

		$form->addSelect('camp', 'Tábor', $this->database->table('camp')->where('active', 1)->fetchPairs('id','name'))
			->setRequired('Je třeba zvolit nějaký tábor!')
			->setPrompt('Vyberte tábor')
			->setAttribute('class', 'selectCampForm');

		$form->addSubmit('send', 'Provést změnu');

		$form->onSuccess[] = array($this, 'selectCampFormSucceeded');
        return $form;
	}

	public function selectCampFormSucceeded($form, $values) {
		try {			
			$fname = __DIR__ . "/const.php";
            $out = file_get_contents(htmlspecialchars($fname), NULL, NULL, 5);

            $out = str_replace('("CAMP", "' . CAMP . '")', '("CAMP", "' . $values['camp'] . '")', $out);
            $out = "<?php" . $out;
            $file = fopen($fname, "w");
            fwrite($file, $out);
            fclose($file);
			
			$this->flashMessage('Změna byla úspěšně provedena.');
			$this->redirect('Settings:selectCamp');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Nepodařilo se zvolit výchozí tábor!");
		}
	}

	protected function createComponentNewLeadForm() {
		$form = new Nette\Application\UI\Form;

		$form->addText('name', 'Jméno')
			->setRequired('Zadejte jméno!');

		$form->addText('surname', 'Příjmení')
			->setRequired('Zadejte příjmení!');

		$form->addSelect('function', 'Funkce:', $this->database->table('function')->fetchPairs('id','name'))
			->setRequired('Je třeba zvolit funkci!')
			->setPrompt('Vyberte funkci');

		$form->addText('rc', 'Rodné číslo')
			->addRule(\Nette\Forms\Form::LENGTH, 'Délka musí být 10 znaků', 10);

		$form->addSubmit('send', 'Uložit data');

		$form->onSuccess[] = array($this, 'newLeadFormSucceeded');
        return $form;
	}

	public function newLeadFormSucceeded($form, $values) {
		try {
			$values["id"] = "";
			$values["active"] = 1;
			$values["rc"] =  str_replace("/", "", $values["rc"]);
			$tmp['function_id'] = $values['function'];
			unset($values['function']);
			//insert data into db
			$exists = $this->database->table('lead')->where('rc', $values['rc'])->count('id');

			if ($exists == 0)
				$this->database->table('lead')->insert($values);

			$lead_id = $this->database->table('lead')->select('id')->where('rc', $values['rc']);

			//vložení vztahu osoba-tábor
			$tmp['lead_id'] = $lead_id;
			$tmp['camp_id'] = CAMP;
			$tmp['function_id'];
			//dump($tmp);
			//dump($lead_id);
			$this->database->table('lead_camp')->insert($tmp);

			$this->flashMessage('Data byla úspěšně vložena.');
			$this->redirect('Settings:newLead');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení dítěte se nezdařilo, zkuste to prosím znovu.");
		}
	}

	public function createComponentNewGameForm() {
		$form = new Nette\Application\UI\Form;

		$form->addText('name', 'Název')
			->setRequired('Zadejte název!');

		$form->addSelect('type', 'Typ hry:', $this->database->table('game_type')->fetchPairs('id','name'))
			->setRequired('Je třeba zvolit typ hry!')
			->setPrompt('Vyberte typ hry');	

		$form->addTextArea('description', 'Popis hry:')
    		->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Popis hry je příliš dlouhý!', 1000)
    		->setAttribute('cols', '40')
			->setAttribute('rows', '5');
			
		$gameCamp = [
			0 => 'Obecná hra bez přiřazení ke konkrétnímu táboru',
			CAMP => 'Hra spojená s aktuálním táborem',
		];
		
		$form->addRadioList('camp', 'Přiřazení hry k táboru:', $gameCamp)
			->setDefaultValue(0);

		$form->addSubmit('send', 'Uložit data');

		$form->onSuccess[] = array($this, 'newGameFormSucceeded');
        return $form;
	}

	public function newGameFormSucceeded($form, $values) {
		try {			
			
			$values["id"] = "";
			$values["active"] = 1;
			//insert data into db
			$this->database->table("game")->insert($values);

			$this->flashMessage('Data byla úspěšně vložena.');
			$this->redirect('Settings:newGame');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení dítěte se nezdařilo, zkuste to prosím znovu.");
		}
	}

	public function createComponentTeamAdminForm() {
		$form = new Nette\Application\UI\Form;
		
		$teams = $this->database->table('team')
				->where('camp', CAMP);

		foreach ($teams as $t) {
			$form->addText('name_' . $t->id, "Název oddílu")
			->setRequired('Zadejte název oddílu!')
			->setValue($t->name);	

			
			$head = $this->database->table('lead')
					->select('id, CONCAT(name," ", surname) AS wholeName')
					->where(':lead_camp.function_id', 3)
					->where(':lead_camp.camp_id', CAMP)
					->fetchPairs('id','wholeName');

			$form->addSelect('headId_' . $t->id, 'Vedoucí:', $head)
				->setRequired('Je třeba zvolit vedoucího!')
				->setDefaultValue($t->headId)
				;

			$instr = $this->database->table('lead')
					->select('id, CONCAT(name," ", surname) AS wholeName')
					->where(':lead_camp.function_id', 4)
					->where(':lead_camp.camp_id', CAMP)
					->fetchPairs('id','wholeName');

			$form->addSelect('instrId_' . $t->id, 'Instruktor:', $instr)
				// ->setRequired('Je třeba zvolit instruktora!')
				->setDefaultValue($t->instrId);
		}	

		$form->addSubmit('send', 'Uložit data');

		$form->onSuccess[] = array($this, 'TeamAdminFormSucceeded');
        return $form;
	}

	public function TeamAdminFormSucceeded($form, $values) {
		try {			
			
			$teamNumbers = $this->database->table('team')
					->where('active', 1)
					->where('camp', CAMP);

			foreach ($teamNumbers as $key => $t) {
				$t->update(Array('name' => $values["name_" . $t->id]));
				$t->update(Array('headId' => $values["headId_" . $t->id]));
				$t->update(Array('instrId' => $values["instrId_" . $t->id]));
			}



			$this->flashMessage('Změny byly úspěšně provedeny.');
			$this->redirect('Settings:team');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení dítěte se nezdařilo, zkuste to prosím znovu.");
		}
	}

	public function createComponentNewTeamForm() {
		$form = new Nette\Application\UI\Form;
		
		$form->addText('number', "Číslo oddílu")
			->addRule(\Nette\Forms\Form::INTEGER, 'Pouze číslo!')
			->setRequired('Zadejte číslo oddílu!');

		$form->addText('name', 'Název')
			->setRequired('Zadejte název!');

		$form->addSelect('headId', 'Vedoucí:', $this->database->table('lead')
				->select('id, CONCAT(name," ", surname) AS wholeName')
				->where(':lead_camp.function_id', 3)
				->where(':lead_camp.camp_id', CAMP)
				->fetchPairs('id','wholeName'))
			->setRequired('Je třeba zvolit vedoucího!')
			->setPrompt('Vyberte vedoucího');

		$form->addSelect('instrId', 'Instruktor:', $this->database->table('lead')
				->select('id, CONCAT(name," ", surname) AS wholeName')
				->where(':lead_camp.function_id', 4)
				->where(':lead_camp.camp_id', CAMP)
				->fetchPairs('id','wholeName'))
			// ->setRequired('Je třeba zvolit instruktora!')
			->setPrompt('Vyberte instruktora');	

		$form->addSubmit('send', 'Uložit data');

		$form->onSuccess[] = array($this, 'newTeamFormSucceeded');
        return $form;
	}

	public function newTeamFormSucceeded($form, $values) {
		try {			
			
			$values["id"] = "";
			$values["active"] = 1;
			$values["camp"] = CAMP;
			//insert data into db
			$this->database->table("team")->insert($values);

			$this->flashMessage('Data byla úspěšně vložena.');
			$this->redirect('Settings:newTeam');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení dítěte se nezdařilo, zkuste to prosím znovu.");
		}
	}

	protected function createComponentPointsLeadForm() {
		$form = new Nette\Application\UI\Form;

		$form->addCheckbox('usePointsLead', 'Zapnout bodování vedoucích')
			->setDefaultValue(POINTS_LEAD);

		$form->addSubmit('send', 'Uložit');

		$form->onSuccess[] = array($this, 'pointsLeadFormSucceeded');
        return $form;
	}

	public function pointsLeadFormSucceeded($form, $values) {
		try {
			$fname = __DIR__ . "/const.php";
            $out = file_get_contents(htmlspecialchars($fname), NULL, NULL, 5);

            $out = str_replace('("POINTS_LEAD", "' . POINTS_LEAD . '")', '("POINTS_LEAD", "' . $values['usePointsLead'] . '")', $out);
            $out = "<?php" . $out;
            $file = fopen($fname, "w");
            fwrite($file, $out);
            fclose($file);
			
			$this->flashMessage('Změna byla úspěšně provedena.');
			$this->redirect('Settings:pointsLead');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Nepodařilo se uložit změny!");
		}
	}

	protected function createComponentReloadLivePointsPageForm() {
		$form = new Nette\Application\UI\Form;

		$form->addCheckbox('livePointsReload', 'Odeslat požadavek na refresh')
			->setDefaultValue(LIVE_POINTS_RELOAD);

		$form->addSubmit('send', 'Uložit');

		$form->onSuccess[] = array($this, 'livePointsReloadFormSucceeded');
        return $form;
	}

	public function livePointsReloadFormSucceeded($form, $values) {
		try {
			$fname = __DIR__ . "/const.php";
            $out = file_get_contents(htmlspecialchars($fname), NULL, NULL, 5);

            $out = str_replace('("LIVE_POINTS_RELOAD", "' . LIVE_POINTS_RELOAD . '")', '("LIVE_POINTS_RELOAD", "' . $values['livePointsReload'] . '")', $out);
            $out = "<?php" . $out;
            $file = fopen($fname, "w");
            fwrite($file, $out);
            fclose($file);
			
			$this->flashMessage('Změna byla úspěšně provedena.');
			$this->redirect('Settings:livePoints');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Nepodařilo se uložit změny!");
		}
	}

}