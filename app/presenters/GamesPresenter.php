<?php

namespace App\Presenters;

use Nette,
	App\Model;


/**
 * Homepage presenter.
 */
class GamesPresenter extends BasePresenter
{

	public function __construct(Nette\Database\Context $database) {
		$this->database = $database;
	}

	public function renderDefault($order) {
		if (!isset($order)) $order = 'name';
		$this->template->games = $this->database->table('game')
			->where('active', '1')
			->order($order);
		$this->template->gameType = $this->database->table('game_type');
	}

}