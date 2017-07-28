<?php

namespace travelsoft\booking\abstraction;

/**
 * Абстрактный класс "сущность"
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
abstract class Entity {
    
    abstract public function save () : bool;
}
