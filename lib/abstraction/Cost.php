<?php

namespace travelsoft\booking\abstraction;

/**
 * Description of Cost
 *
 * @author dimabresky
 */
abstract class Cost {

    /**
     * @var array
     */
    protected $_source = array();

    abstract public function setSource(array $source);
    
    abstract public function get(): array;
    
    abstract public function getSource(): array;

    abstract public function getMinForTour(): array;

    abstract public function getMinForTours(): array;
}
