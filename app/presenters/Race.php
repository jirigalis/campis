<?php

namespace App\Presenters;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Race class
 *
 * @author jirigalis
 */
class Race {
        //put your code here
        private $id_zavodu;
        private $categories;
        private $raceFile;
        private $database;
        
        /**
         * Vytvoří nový závod, zatím přiřadí jen id.
         * @param type $id_zavodu
         */
        public function __construct(\Nette\Database\Context $database, $id_zavodu, $raceData) {
                $this->id_zavodu=$id_zavodu;
                $this->raceData = $raceData;
                $this->database = $database;
                $this->categories = array();
        }
        
        /**
         * Vrátí ID závodu.
         * @return type int id závodu
         */
        public function getIdZavodu() {
                return $this->id_zavodu;
        }

        /**
         * Vytiskne seznam kategorií
         */
        public function printCategories() {
                foreach ($this->categories as $category) {
                        echo $category->getName() . "<br>";
                }
        }
        
        /**
         * Vrátí celé pole kategorií
         * @return type array pole kategorií
         */
        public function getCategories() {
                return $this->categories;
        }


        /**
         * Vrátí kategorii se zadaným id
         * @param type $id
         * @return boolean false když kategorie nenalezena, objekt category, když nalezena
         */
        public function getCategory($id) {
                foreach ($this->categories as $item) {
                        if ($item->getId() == $id)
                                return $item;
                }              
                return false;
        }


        /**
         * Přidá novou kategorii do pole kategorií
         */
        public function addCategory($id, $name, $short) {
                array_push($this->categories, new Category($this->database, $id, $name, $short));
        }

        /**
         * Načte všechny kategorie pro daný závod
         */
        public function loadCategories() {
                $categories = $this->database->table('category');
                foreach ($categories as $c) {
                        $this->addCategory($c->id, $c->name, $c->short);
                }
        }

        /**
         * Naplní všechny kategorie dětmi z databáze
         **/
        public function fillCategories() {
                if (!$this->categories)
                        return false;
                foreach ($this->categories as $c) {
                        $c->fillCategory();
                }
        }

        /**
         * Na základě XML souboru provede vyhodnocení a vypsání výsledků závodu.
         * @param $insert indikátor zápisu dat do DB. Jestliže je nastaven na TRUE (1), data sebudou zapisovat.
         */
        public function evalRace($insert) {
                $results = array();

                $this->loadCategories();
                //$this->categories->fillCategories();

                //otevření XML spojení
                $xmlData = simplexml_load_file($this->raceData);

                //naplnění kategorií osobami včetně údajů ze závodu
                foreach ($xmlData->OSOBA as $child) {
                        $this->getCategory($child->KATEGORIE)
                                ->addPerson($child->JMENO, $child['id'], 
                                        $child->CASY->START, $child->CASY->CIL, $child->VYBEHL, $child->CASY->TRMIN, $child->CASY->STOPCAS, $child->CASY->VYSLEDNY);
                }                

                foreach ($this->getCategories() as $category) {
                        if ($category->getCatLength() > 0) {
                                $category->sort();
                                array_push($results, $category->printPersons($category->getId()));
                        }
                }
                
                return $this;
        }
        
}

?>
