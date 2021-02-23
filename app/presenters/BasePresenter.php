<?php

namespace App\Presenters;

use Nette,
	App\Model,
    Nette\Database\Connection;

require_once("const.php");
/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	protected $database;

	public function beforeRender() {
		$this->template->selectedCamp = $this->database->table('camp')->get(CAMP)->name;
		$this->template->selectedCampYear = $this->database->table('camp')->get(CAMP)->year;
	}
	
	public function isGirl($rc) {
		$cislo = substr($rc, 2,2);
		return ($cislo>'50');
	}

	public function getBirth($rc) {
		$year = substr($rc, 0,2);
		$month = substr($rc, 2,2);
		$day = substr($rc, 4,2);

		if ($month >= 50) {
			$month -= 50;
			if ($month < 10)
				$month = "0" . $month;
		}

		if ($year > 54){
			$year = "19" . $year;
		}
		else {
			$year = "20" . $year;
		}

		return $day . ". " . $month . ". " . $year;
	}

	/**
	 * Vrátí věk s přesností na den na základě rodného čísla v rámci daného tábora.
	 */
	public function getAge($rc) {
		$year = substr($rc, 0,2);
		$month = substr($rc, 2,2);

		//přidání předšílí roku
		if ($year > 54) {
			$rc = "19" . $rc;
		}
		else {
			$rc = "20" . $rc;			
		}

		//úprava RČ jestli se jedná o dívku
		if ($month > 50) {
			$rc -= 50000000;
		}

		return floor((($this->database->table('camp')->get(CAMP)->year . date("md")) - substr($rc, 0, 8)) / 10000);
	}

	public function getCategory($rc) {
		$category = NULL;
		$age = $this->getAge($rc);


		if ($age <= 9) {
			if ($this->isGirl($rc))
				$category = 1;
			else 
				$category = 2;
		}
		else if ($age >= 10 && $age <= 12) {			
			if ($this->isGirl($rc))
				$category = 3;
			else 
				$category = 4;
		}
		else {
			if ($this->isGirl($rc))
				$category = 5;
			else 
				$category = 6;			
		}

		return $category;
	}

	/**
	 * Funkce převede čas, který je na vstupu, na sekundy.
	 **/
	public function timeToSec($str) {
		$pole = explode(":", $str);
		$vysl = $pole[2]*1 + 60*$pole[1] + 3600*$pole[0];
		return $vysl;
	}

	/**
	 * Funkce převede sekundy, které jsou na vstupu na čas. Čas je ve formátu string.
	 **/
	public function secToTime($s) {
		//výpočty
		$sec = $s%60;
		$sec = round($sec, 3);
		$s = round($s, 3);
		$s -= $sec;
		$s /= 60;
		$s = round($s, 3);
		$min = $s%60;
		$s -= $min;
		$s /= 60;

	  	$s = round($s, 3);
		$hour =$s;

		//přidání nul před jednociferná čísla
		if ($sec < 10)
			$sec = "0" . $sec;
		if ($min < 10)
			$min = "0" . $min;
		if ($hour < 10)
			$hour = "0" . $hour;

		//spojení jendotlivých části do jednoho stringu
		$vysl = $hour . ":" . $min . ":" . $sec;

		return $vysl;
	}

}
