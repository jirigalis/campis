<?php

namespace App\Presenters;

use Nette,
        App\Model;
/**
 * Description of CategoryClass
 * Třída reprezentující kategorie závodníků. Obsahuje data a informace, které se týkají
 * lidí, co patří do dané kategorie
 * @author jirigalis
 */
class Category {
        private $id;
        private $short;
        private $name;
        private $persons = Array();
        private $database;


        /**
         * Konstruktor objektu kategorie. 
         * @param type $id id dané kategorie
         * @param type string název kategorie
         * @param type string zkratka dané kategorie
         */
        public function __construct(\Nette\Database\Context $database, $id, $name, $short) {
                $this->database = $database;

                $this->id = $id;
                $this->name=$name;
                $this->short = $short;
        }
        
        /**
         * Metoda přidá novou osobu do kategorie
         * @param type $name
         * @param type $id
         * @param type $start
         * @param type $cil
         * @param type $vybehl
         * @param type $trmin
         * @param type $stopcas
         * @param type $vysledny
         */
        public function addPerson($name, $id, $start, $cil, $vybehl, $trmin, $stopcas, $vysledny) {
                $this->persons[] = new Person($name, $id, $start, $cil, $vybehl, $trmin, $stopcas, $vysledny);
        }
        
        /**
         * Metoda vypíše seznam věech osob v kategorii
         */
        public function printPersons($short) {
                $table =  "<table class=\"dataTable\">
                        <tr>
                                <th>Místo</th>
                                <th>Jméno</th>
                                <th>Čas</th>
                                <th>Body</th>
                        </tr>";
                $i=1;
                foreach($this->persons as $item) {
                        
                        $vysledny = $item->getVysledny();
                        if ($vysledny == "99:99:99") $vysledny=" - ";
                        $table .= "<tr>";
                        $table .= "<td>" . $i . ".</td><td>" . $item->getName() . "</td><td>" . $vysledny . "</td><td>";
                        
                        if ($i <= RANKED) {
                                $table .= constant("POINTS_".$i);
                        }
                        
                        $table .= "</td></tr>";
                        $i++;

                        if($short && $i==RANKED+1)
                                break;
                                                        
                }
                $table .= "</table>";
                return $table;
        }        
        
        /**
         * Vrátí osobu se zadaným id.
         * @param type $id id hledané osoby
         * @return type Osoba když existuje, jinak FALSE
         */
        public function getPerson($id) {
                foreach ($this->persons as $item) {
                        if ($item->getId() == $id)
                                return $item;
                }
                return false;
        }

        /**
         * Seřadí pole osob v rámci kategorie za použití komparátoru {@link comparePerson}
         */
        public function sort() {
                usort($this->persons, array($this, 'comparePerson'));
        }
                
        /**
         * Metoda porovná časy dvou osob v parametrech
         * Časy osob si metoda sama převádí na sekundy voláním funkce TimeToSec ze
         * souboru template.php 
         * @return int  1    když je první osoba pomalejší než druhá
         *              -1   když je první osoba rychlejší než druhá
         *              0    když mají stejný čas
         * @return int
         */
        public function comparePerson(Person $o1, Person $o2) {                 
                if ($this->timeToSec($o1->getVysledny()) > $this->timeToSec($o2->getVysledny())) return 1;
                if ($this->timeToSec($o1->getVysledny()) < $this->timeToSec($o2->getVysledny())) return -1;
                return 0;
        }
        
        /**
         * Funkce převede čas, který je na vstupu, na sekundy.
         **/
        public function timeToSec($str) {
                $pole = explode(":", $str);
                $vysl = $pole[2]*1 + 60*$pole[1] + 3600*$pole[0];
                return $vysl;
        }
        
        public function getId(){
                return $this->id;
        }
        
        public function getName() {
                return $this->name;
        }
        
        public function getShort() {
                return $this->short;
        }
        
        /**
         * Počítá prvky v poli - v tomto případě počet osob v kategorii
         * @return type int počet osob v kategorii
         */
        public function getCatLength() {
                return count($this->persons);
        }
        
        /**
         * Funkce vrátí osobu na daném místě v poli osob. Aby to odpovídalo pořadí, 
         * pole již musí být seřazené!
         * @param type $place číslo pozice osoby
         * @return boolean false když tolikáté místo v poli osob není (kategorie nemá
         * tolik závodníků, type Person, když se dané místo nalezne.
         */
        public function getPlace($place) {
                if (count($this->persons)>=$place)
                        return $this->persons[$place-1];
                return FALSE;
        }

        /**
         * Vytiskne řádek o kategorii ve formátu Název (zkratka)/n
         */
        public function __toString() {
                echo $this->name . " (" . $this->short . ")<br />";
        }


        /**
         * Načte do dané kategorie všechny požadované členy a to bez údajů ze závodu
         */
        public function fillCategory() {
                
                $persons = $this->database->table('child')
                        ->where('active', 1)
                        ->where(':child_camp.camp_id = ?', CAMP)
                        ->where(':child_camp.category_id', $this->getId())
                        ->order(':child_camp.category_id, surname')
                        ;

                foreach ($persons as $p) {
                        $this->addPerson($p->name, $p->id, 
                                '00:00:00', '00:00:00', '0', '00:00:00', '00:00:00', '00:00:00');
                }
        }
}


?>
