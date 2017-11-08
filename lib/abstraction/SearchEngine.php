<?php

namespace travelsoft\booking\abstraction;

use travelsoft\booking\tours\Cost;

/**
 * Абстрактный класс для поисковика цен
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
abstract class SearchEngine {
    
    /**
     * @var array
     */
    protected $_extFilter = array();
    
    /**
     * @var array
     */
    protected $_prices = array();

    /**
     * @var \travelsoft\booking\abstraction\Cost
     */
    protected $_cost;

    abstract public function search();
    
    /**
     * Устанавливает внешний фильтр для поиска услуг
     * @param array $extFilter
     * @return self
     */
    public function setExtFilter (array $extFilter) {
        
        $this->_extFilter = $extFilter;
        return $this;
    }
    
    /**
     * Возвращает объект стоимостей туров из поиска
     * @return \travelsoft\booking\tours\Cost
     */
    public function getCost() {

        if (!$this->_cost) {

            $this->_cost = new Cost($this->_prices);
        }

        return $this->_cost;
    }
}
