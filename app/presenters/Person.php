<?php

namespace App\Presenters;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Třída obsahující data k jedné konkrétní osobě
 *
 * @author jirigalis
 */
class Person {
        private $name, $id, $start, $cil, $vybehl, $trmin, $stopcas, $vysledny;
        
        /**
         * Konstruktor třídy vytvoří nový objekt typu Person a přiřadí k němu všechny časy
         * @param type $name
         * @param type $id
         * @param type $start
         * @param type $cil
         * @param type $vybehl
         * @param type $trmin
         * @param type $stopcas
         * @param type $vysledny
         */        
        public function __construct($name, $id, $start, $cil, $vybehl, $trmin, $stopcas, $vysledny) {
                $this->name=$name;
                $this->id=$id;
                $this->start=$start;
                $this->cil=$cil;
                $this->stopcas=$stopcas;
                $this->trmin=$trmin;
                $this->vybehl=$vybehl;
                $this->vysledny=$vysledny;
        }
        
        /**
         * Vrátí jméno osoby
         * @return type string Jméno osoby
         */
        public function getName() {
                return $this->name;
        }

        public function setStart($start) {
                $this->start = $start;
        }

        public function setCil($cil) {
                $this->cil = $cil;
        }

        public function setStopcas($stopcas) {
                $this->stopcas = $stopcas;
        }

        public function setTrmin($trmin) {
                $this->trmin = $trmin;
        }

        public function setVybehl($vybehl) {
                $this->vybehl = $vybehl;
        }

        public function setVysledny($vysledny) {
                $this->vysledny = $vysledny;
        }

        public function setName($name) {
                $this->name = $name;
        }
        
        /**
         * Vrátí ID osoby
         * @return type int id osoby
         */
        public function getId() {
                return $this->id;
        }
        
        /**
         * Vrátí výsledný čas ve formátu HH:MM:SS.MS
         * @return type string výsledný čas
         */
        public function getVysledny() {
                if ($this->vybehl==0) return ("99:99:99");
                return $this->vysledny;
        } 
        
        /**
         * Funkce zjistí, jestli závodník vyběhl.
         * @return type boolean vybehl
         */
        public function getVybehl() {
                return $this->vybehl;
        }

        /**
         * Vrátí jméno osoby a id v závorce
         * @return type string jméno (id)+
         */
        public function __toString() {
                return $this->name . " (" . $this->id . ")";
        }        
}

?>
