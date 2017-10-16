<?php

namespace travelsoft\booking\documents;

/**
 * Репозиторий данных для формирования документа
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft 
 */
class Repository {
    
    /**
     * Переменные шаблона
     * @var array
     */
    protected $_labels = array();
    
    /**
     * Путь к файлу шаблона
     * @var string
     */
    public $path = '';
    
    /**
     * Установка метки шаблона
     * @param string $label
     * @param string $value
     */
    public function setLabel (string $label, string $value) {
        $this->_labels[$label] = $value;
    }
    
    /**
     * Получение меток шаблона
     * @return array
     */
    public function getLabels () : array {
        return (array)array_keys($this->_labels);
    }
    
    /**
     * Получение значений по меткам шаблона
     * @return array
     */
    public function getLabelsValues () : array{
        return (array)array_values($this->_labels);
    }
}
