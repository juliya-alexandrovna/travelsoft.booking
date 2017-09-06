<?php

namespace travelsoft\booking\adapters;

/**
 * Класс адаптер для валюты
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class CurrencyConverter {

    /**
     * @var \travelsoft\currency\Converter
     */
    protected $_converter;

    public function __construct() {
        
        if (!\Bitrix\Main\Loader::includeModule('new.travelsoft.currency')) {
            
            throw new \Exception(get_called_class() . ': Module travelsoft.currency not found');
        }
        
        $this->_converter = \travelsoft\currency\Converter::getInstance();
        
        if (strlen($this->_converter->getDefaultCurrencyIso()) <= 0) {
            
            $this->_converter->initDefault();
        }
    }

    /**
     * Конвертирует цену в текущую валюту сайта
     * @param float $price
     * @param string $iso
     * @param string $isoOut
     * @return float
     */
    public function convert(float $price, string $iso, string $isoOut = null): float {
        
        if ($isoOut) {
            
            $result = $this->_converter->convert($price, $iso, $isoOut)->getResultLikeArray();
        } else {
            
            $result = $this->_converter->convert($price, $iso)->getResultLikeArray();
        }
        
        return $result['price'];
    }

    /**
     * Возвращает iso текущей валюты приложения
     * @return string
     */
    public function getCurrentCurrencyIso(): string {

        return $this->_converter->getDefaultCurrencyIso();
    }
    
    /**
     * Возвращает отформатированную цену
     * @param float $price
     * @param string $iso
     * @return string
     */
    public function getFormatted (float $price, string $iso) : string {
        
        return $this->_converter->format($price, $iso);
    }
    
    /**
     * Возвращает отформатированное число
     * @param float $price
     * @return string
     */
    public function format (float $price) : string {
        
        return $this->_converter->formatFloatValue($price);
    }
    
    /**
     * Возвращает список доступных валют
     * @return array
     */
    public function getListOfCurrency () : array {
        
        $arCurrencies = \travelsoft\currency\stores\Currencies::get();
        
        $arResult = array();
        foreach ($arCurrencies as $arCurrency) {
            
            $arResult[] = $arCurrency['UF_ISO'];
        }
        
        return $arResult;
    }
    
    /**
     * Возвращает текущий id курса в системе
     * @return int
     */
    public function getCurrentCourseId () : int {
        
        return \travelsoft\currency\Settings::currentCourseId();
    }
}
