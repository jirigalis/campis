<?php

namespace App\Presenters;

use Nette,
	Nette\Application\UI\Form,
	App\Model;


/**
 * Homepage presenter.
 */
class PersonPresenter extends BasePresenter
{
	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderDefault($id)
	{		
		$this->template->person = $this->database->table('child')->get($id);
		$this->template->age = $this->getAge($this->template->person->rc);

		$this->template->category = $this->database->table('category')
			->where(':child_camp.child_id = ?', $id)->fetch();

			
		$this->template->team = $this->database->table('team')
			->where(':child_camp.child_id', $id)
			->where(':child_camp.camp_id' , CAMP)->fetch();
			
		$this->template->points = $this->database->table('points_child')
			->where('childId', $id)
			->where('camp', CAMP);

		$this->template->game = $this->database->table('game');
		$this->template->gameType = $this->database->table('game_type');
	}

	public function renderAddPoints($childId) {
		$this->template->person = $this->database->table('child')->get($childId);
	}

	public function renderEdit($childId) {
		if (!isset($childId) || !is_numeric($childId)) {
			$this->redirect("People:");
		}
		$this->template->person = $this->database->table('child')->get($childId);
	}

	public function createComponentEditPersonForm() {
		$form = new Nette\Application\UI\Form;

		$id = $this->getParameter('childId');
		$person = $this->database->table('child')->get($id);
		
		$team = $this->database->table('child')
			->select("id, name, surname, adress, rc, :child_camp.camp_id, :child_camp.team_id, :child_camp.category_id")
			->where("id", $id)
			->where(":child_camp.camp_id", CAMP)
			->fetch();	

		$form->addText('name', "Jméno")
			->setRequired('Jméno nesmí být prázdné.')
			->setValue($person->name);
			
		$form->addText('surname', "Příjmení")
		->setRequired('Příjmení nesmí být prázdné.')
		->setValue($person->surname);

		$form->addText('adress', "Adresa")
			->setValue($person->adress);
			
		$form->addText('rc', "Rodné číslo")
			->setRequired('Rodné číslo nesmí být prázdné.')
			->addRule(Form::MIN_LENGTH, 'Rodné číslo musí mít %d znaků', 10)
			->addRule(Form::MAX_LENGTH, 'Rodné číslo musí mít %d znaků', 10)
			->setValue($person->rc);

		$teams = $this->database->table('team')
					->select('id, CONCAT(number, ": ", name) AS teamLabel')
					->where('camp', CAMP)
					->fetchPairs('id', 'teamLabel');

		$form->addSelect('team_id', "Oddíl:", $teams)
			->setRequired('Oddíl musí být vždy nastaven!')
			->setDefaultValue($team->team_id)
			;

		$categories = $this->database->table('category')
					->fetchPairs('id', 'name');

		$form->addSelect('category_id', "Kategorie:", $categories)
			->setRequired('Kategorie musí být vždy nastavena!')
			->setDefaultValue($team->category_id)
			;

		$form->addHidden('child_id', $id);
		$form->addHidden('date', date('d.m.Y H:i:s', Time()));
		$form->addSubmit('send', 'Uložit');

		$form->onSuccess[] = array($this, 'editPersonFormSucceeded');
		return $form;
	}

	public function editPersonFormSucceeded($form, $values) {
		try {
			$person = Array();
			$person["active"] = 1;
			$person["name"] = $values["name"]; 
			$person["surname"] = $values["surname"]; 
			$person["adress"] = $values["adress"]; 
			$person["rc"] = $values["rc"];

			$this->flashMessage("Změny byly úspěšně uloženy");
			$this->database->table('child')->where("id", $values["child_id"])->update($person);
			$this->database->table('child_camp')->where("child_id", $values["child_id"])->update(Array("team_id" => $values["team_id"], "category_id" => $values["category_id"]));
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Úprava dat se nezdařila, zkuste to prosím znovu.");
		}
	}

	public function createComponentAddPointsForm() {
		$form = new Nette\Application\UI\Form;
		
		$form->addText('points', "Počet bodů")
			->addRule(\Nette\Forms\Form::INTEGER, 'Počet bodů musí být číslo!')
			->setRequired('Zadejte počet bodů!');

		$form->addSelect('game', 'Hra', $this->database->table('game')->where('camp = ? OR camp = ?', CAMP, 0)->fetchPairs('id','name'))
			->setAttribute('size', 6)			
			->setRequired('Vyberte hru!');
		
		$form->addText('note', "Poznámka")
			->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Maximální délka poznámky je %d znaků.', 500);

		$form->addHidden('childId', $this->getParameter('childId'));
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
			$this->database->table("points_child")->insert($values);


			$childId = $values["childId"];
			$this->flashMessage('Body byly úspěšně vloženy.');
			$this->redirect('People:');
		
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Vložení bodů se nezdařilo, zkuste to prosím znovu.");
		}
	}

	

}