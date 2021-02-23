<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class PeoplePresenter extends BasePresenter
{
	

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderDefault($order)
	{
		if (!isset($order)) $order = 'surname';
		
		//if ($order == 'category')

		$this->template->children = $this->database->table('child')
			->select('*, :child_camp.team_id AS team_id, :child_camp.category_id AS category')
			->where('active', '1')
			->where(':child_camp.camp_id = ?', CAMP)
			->order($order);

		$this->template->category = $this->database->table('category');
		$this->template->team = $this->database->table('team')
			->where('camp', CAMP)
			->where('active', 1);

	}

	public function renderCategories() {
		$this->template->categories = $this->database->table('category')
			->select('id, name, short');
		$categories = $this->template->categories;
		
		$this->template->team = $this->database->table('team')
			->where('camp', CAMP)
			->where('active', 1);

		$this->template->children = array();

		foreach ($categories as $cat) {
			$category = $this->database->table('child')
				->select('*, :child_camp.team_id AS team_id, :child_camp.category_id AS category')
				->where('active', '1')
				->where(':child_camp.camp_id = ?', CAMP)
				->where(':child_camp.category_id', $cat->id);
				
			if ($category->count() > 0) {
				$this->template->children[] = $category;
			}
		}

	}

	public function renderTeams() {
		$this->template->teams = $this->database->table('team')
				->select('*')
				->where('camp', CAMP)
				->where('active', 1);
		$teams = $this->template->teams;

		$this->template->categories = $this->database->table('category');

		$this->template->children = array();

		foreach ($teams as $team) {
			$this->template->children[$team->id] = $this->database->table('child')
				->select('*, :child_camp.team_id AS team_id, :child_camp.category_id AS category')
				->where('active', '1')
				->where(':child_camp.camp_id = ?', CAMP)
				->where(':child_camp.team_id', $team->id);				
		}
	}

	

}