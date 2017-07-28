<?php

namespace travelsoft\booking\tours;

use travelsoft\booking\abstraction\Entity;
use travelsoft\booking\stores\Tours;

/**
 * Класс туристического предложения
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Offer extends Entity {
    
    /**
     * ID турпредложения
     * @var int
     */
    protected $_id = null;
    
    /**
     * Название турпредложения
     * @var string 
     */
    protected $_name = null;
    
    /**
     * Дата начала турпредложения
     * @var \Date
     */
    protected $_dateFrom = null;
    
    /**
     * Дата окончания турпредложения
     * @var \Date
     */
    protected $_dateTo = null;
    
    /**
     * Квота по турпредложению
     * @var int
     */
    protected $_quota = null;
    
    /**
     * Количество проданных мет по турпредложению
     * @var int
     */
    protected $_onSale = null;
    
    /**
     * Продолжительность
     * @var int
     */
    protected $_duration = null;
    
    /**
     * Количество взрослых
     * @var int
     */
    protected $_adults = null;
    
    /**
     * Количество детей
     * @var int
     */
    protected $_children = null;
    
    /**
     * Стоимость турпредложения
     * @var float
     */
    protected $_cost = null;
    
    /**
     * Валюта стоимости турпредложения
     * @var string
     */
    protected $_currency = null;
        
    /**
     * Город прибытия
     * @var string
     */
    protected $_arrivalCity = null;
    
    /**
     * Город прибытия
     * @var string
     */
    protected $_departureCity = null;
    
    /**
     * Страна проведения
     * @var string
     */
    protected $_country = null;
    
    /**
     * Тип турпредложения
     * @var string
     */
    protected $_type = null;
    
    /**
     * Номер по заявке
     * @var string
     */
    protected $_xNumber = null;
    
    /**
     * Размещение по турпредложению
     * @var string
     */
    protected $_placement = null;
    
    /**
     * Символьный код турпредложения
     * @var string
     */
    protected $_code = null;
    
    /**
     * Питание
     * @var string
     */
    protected $_food = null;
    
    /**
     * Услуги
     * @var string
     */
    protected $_services = null;
    
    /**
     * Устанавливает id турпредложения
     * @param int $id
     * @return $this
     */
    public function setId (int $id) {
        
        $this->_id = $id <= 0 ? null : $id;
        return $this;
    }
    
    /**
     * Возвращает ID предложения
     * @return string
     */
    public function getId () : int {
        
        return $this->_id;
    }
    
    /**
     * Устанавливает название турпредложения
     * @param string $name
     * @return $this
     */
    public function setName (string $name) {
        
        $this->_name = $name;
        $this->_code = translit($name);
        return $this;
    }
    
    /**
     * Возвращает имя предложения
     * @return string
     */
    public function getName () : string {
        
        return (string)$this->_name;
    }
    
    /**
     * Устанавливает дату начала 
     * @param \Date $date
     * @return $this
     */
    public function setDateFrom (\Date $date) {
        
        $this->_dateFrom = $date;
        return $this;
    }
    
    /**
     * Возвращает дату начала
     * @return \Date|null
     */
    public function getDateFrom () {
        
        return $this->_dateFrom;
    }
    
    /**
     * Устанавливает дату начала турпредложения
     * @param \Date $date
     * @return $this
     */
    public function setDateTo (\Date $date) {
        
        $this->_dateTo = $date;
        return $this;
    }
    
    /**
     * Возвращает дату окончания
     * @return \Date|null
     */
    public function getDateTo () {
        
        return $this->_dateTo;
    }
    
    /**
     * Устанавливает квоту турпредложения
     * @param int $quota
     * @return $this
     */
    public function setQuota (int $quota) {
        
        $this->_quota = $quota < 0 ? null : $quota;
        return $this;
    }
    
    /**
     * Возвращает квоту
     * @return int
     */
    public function getQuota () : int {
        
        return (int)$this->_quota;
    }
    
    /**
     * Устанавливает количество мест доступных для бронирования турпредложения
     * @param int $onSale
     * @return $this
     */
    public function setOnSale (int $onSale) {
        
        $this->_onSale = $onSale < 0 ? null : $onSale;
        return $this;
    }
    
    /**
     * Возвращает количество мест доступных для бронирования турпредложения
     * @return int
     */
    public function getOnSale () : int {
        
     return (int)$this->_onSale;
    }
    
    /**
     * Устанавливает стоимость турпредложения
     * @param float $cost
     * @return $this
     */
    public function setCost (float $cost) {
        
        $this->_cost = $cost < 0 ? null : $cost;
        return $this;
    }
    
    /**
     * Возвращает стоимость турпредложения
     * @return float
     */
    public function getCost () : float {
        
        return (float)$this->_cost;
    }
    
    /**
     * Устанавливает валюту турпредложения
     * @param string $currency
     * @return $this
     */
    public function setCurrency (string $currency) {
        
        $this->_currency = $currency;
        return $this;
    }
    
    /**
     * Возвращает валюту турпредложения
     * @return string
     */
    public function getCurrency () : string {
        
        return (string)$this->_currency;
    }
    
    /**
     * Устанавливает питание
     * @param string $food
     * @return $this
     */
    public function setFood (string $food) {
        
        $this->_food = $food;
        return $this;
    }
    
    /**
     * Возвращает питание
     * @return string
     */
    public function getFood () : string {
        
        return (string)$this->_food;
    }
    
    /**
     * Устанавливает город прибытия
     * @param string $arrivalCity
     * @return $this
     */
    public function setArrivalCity (string $arrivalCity ) {
        
        $this->_arrivalCity = $arrivalCity;
        return $this;
    }
    
    /**
     * Возвращает город прибытия
     * @return string
     */
    public function getArrivalCity () : string {
        
        return (string)$this->_arrivalCity;
    }
    
    /**
     * Устанавливает город отправления
     * @param string $departureCity
     * @return $this
     */
    public function setDepartureCity (string $departureCity) {
        
        $this->_departureCity = $departureCity;
        return $this;
    }
    
    /**
     * Возвращает город отправления
     * @return string
     */
    public function getDepartureCity () : string {
        
        return (string)$this->_departureCity;
    }
    
    /**
     * Устанавливает тип турпредложения
     * @param string $type
     * @return $this
     */
    public function setType (string $type) {
        
        $this->_type = $type;
        return $this;
    }
    
    /**
     * Возвращает тип турпредложения
     * @return string
     */
    public function getType () : string {
        
        return (string)$this->_type;
    }
    
    /**
     * Устанавливает услуги
     * @param string $services
     * @return $this
     */
    public function setServices (string $services) {
        
        $this->_services = $services;
        return $this;
    }
    
    /**
     * Возвращает услуги
     * @return string
     */
    public function getServices () : string {
        
        return (string)$this->_services;
    }
    
    /**
     * Устанавливает страну турпредложения
     * @param string $country
     * @return $this
     */
    public function setCountry (string $country) {
        
        $this->_country = $country;
        return $this;
    }
    
    /**
     * Возвращает страну турпредложения
     * @return string
     */
    public function getCountry () : string {
        
        return (string)$this->_country;
    }
    
    /**
     * Устанавливает количество взрослых
     * @param int $adults
     * @return $this
     * @throws \Exception
     */
    public function setAdults (int $adults) {
        
        if ($adults < 0) {
            
            throw new \Exception(get_called_class() . ": Adults value must be >= 0");
        }
        
        $this->_adults = $adults;
        return $this;
    }
    
    /**
     * Возвращает количество взрослых
     * @return int
     */
    public function getAdults () : int {
        
        return (int)$this->_adults;
    }
    
    /**
     * Устанавливает количество детей
     * @param int $children
     * @return $this
     * @throws \Exception
     */
    public function setChildren (int $children) {
        
        if ($children < 0) {
            
            throw new \Exception(get_called_class() . ": Children value must be >= 0");
        }
        
        $this->_children = $children;
        return $this;
    } 
    
    /**
     * Возвращает количество детей
     * @return int
     */
    public function getChildren () : int {
        
        return (int)$this->_children;
    }
    
    /**
     * Устанавливает номер по заявке
     * @param string $xNumber
     * @return $this
     */
    public function setExternalNumber (string $xNumber) {
        
        $this->_xNumber = $xNumber;
        return $this;
    }
    
    /**
     * Возвращает номер по заявке
     * @return string
     */
    public function getExternalNumber () : string {
        
        return (string)$this->_xNumber;
    }
    
    /**
     * Устанавливает размещение
     * @param string $placement
     * @return $this
     */
    public function setPlacement (string $placement) {
        
        $this->_placement = $placement;
        return $this;
    }
    
    /**
     * Возвращает размещение
     * @return string
     */
    public function getPlacement () : string {
        
        return (string)$this->_placement;
    }
    
    /**
     * Производит сохранение/обновление турпредложения
     * @return boolean
     */
    public function save () : bool {
        
        if ($this->_id > 0) {
            
            return boolval(Tours::update($this->_id, array('NAME' => $this->_name, 'CODE' => $this->_code)));
        } else {
            
            return boolval(Tours::add(array('NAME' => $this->_name, 'CODE' => $this->_code)));
        }
        
        return false;
    }
}
