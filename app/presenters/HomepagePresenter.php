<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderDefault()
	{
		//$this->template->camp = $this->db->query('SELECT * FROM camp WHERE active="1"');
		$this->template->camp = $this->database;
		$dataY = $this->database->table('child')
			->select('id, COUNT(id) AS pocet')
			->where(':child_camp.camp_id', CAMP)
			->group(':child_camp.category_id')
			->order(':child_camp.category_id')
			->fetchPairs('id', 'pocet');

		$dataX = $this->database->table('category')
			->order('id')
			->fetchPairs('id', 'short');

		$iteratorY = new \ArrayIterator($dataY);
		$iteratorX = new \ArrayIterator($dataX);

		$this->template->dataX = iterator_to_array($iteratorX, false);
		$this->template->dataY = iterator_to_array($iteratorY, false);

		$child = $this->database->table('child')
			->where(':child_camp.camp_id', CAMP)
			->where('active', 1)
			->select('id, rc')
			->fetchPairs("id", "rc");

		$womanCount = 0;
		foreach ($child as $i => $ch) {
			if ($this->isGirl($ch)) {
				unset($child[$i]);
				$womanCount++;
			}
		}
		$this->template->manCount = count($child);
		$this->template->womanCount = $womanCount;

	}

}