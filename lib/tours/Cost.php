<?php

namespace travelsoft\booking\tours;

use travelsoft\booking\abstraction\Cost as AbstractCost;
use travelsoft\booking\adapters\CurrencyConverter;

/**
 * Класс расчёта стоимости туруслуги
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Cost extends AbstractCost {

    /**
     * @var array
     */
    protected $_query;

    /**
     * @var \travelsoft\booking\adapters\CurrencyConverter
     */
    protected $_converter;
    
    /**
     * @param array $source
     */
    public function __construct(array $source = null) {
        
        if ($source) {
            
            $this->setSource($source);
        }
        
        $this->_converter = new CurrencyConverter();
        $this->_resetQuery();
    }
    
    /**
     * Устанавливает данные для рассчёта стоимости
     * @param array $source
     * @return \self
     */
    public function setSource(array $source) : self {
        
        $this->_source = $source;
        return $this;
    }

    /**
     * Для услуги с id
     * @param array $id
     * @return \self
     */
    public function forId(array $id): self {

        $id = array_filter($id, function ($id) {
            return $id > 0;
        });

        if (empty($id)) {

            throw new \Exception(get_called_class() . ': Enter correct id');
        }

        $this->_query['id'] = $id;

        return $this;
    }

    /**
     * Для количества взрослых
     * @param int $adults
     * @return \self
     */
    public function forAdults(int $adults): self {

        if ($adults < 0) {

            throw new \Exception(get_called_class() . ': Enter adults count >= 0 ');
        }

        $this->_query['adults'] = $adults;

        return $this;
    }

    /**
     * Для количества детей
     * @param int $children
     * @return \self
     */
    public function forChildren(int $children): self {

        if ($children < 0) {

            throw new \Exception(get_called_class() . ': Enter children count >= 0 ');
        }

        $this->_query['children'] = $children;

        return $this;
    }

    /**
     * Для дат
     * @param array $dates
     * @return \self
     */
    public function forDates(array $dates): self {

        $timestamps = array_map(
                function ($date) {
            return strtotime($date);
        }, array_filter($dates, function ($date) {
                    return strlen($date) > 0;
                })
        );

        if (empty($timestamps)) {

            throw new \Exception(get_called_class() . ': Enter correct dates');
        }

        $this->_query['unix_dates'] = $timestamps;

        return $this;
    }

    /**
     * Прибавлять к расчёту цену по туруслуге для детей
     * @param int $children
     * @return \self
     * @throws \Exception
     */
    public function forChildrenTourService(int $children) {

        if ($children < 0) {

            throw new \Exception(get_called_class() . ': For children tour service enter children count >= 0 ');
        }

        $this->_query['children_tour_service'] = $children;

        return $this;
    }

    /**
     * Прибавлять к расчёту цену по туруслуги для детей
     * @return array
     */
    public function forAdultTourService(int $adults): array {

        if ($adults < 0) {

            throw new \Exception(get_called_class() . ': For children tour service enter adults count >= 0 ');
        }

        $this->_query['adult_tour_service'] = $adults;

        return $this;
    }
    
    /**
     * Возвращает исходные данные для расчёта цен по турам
     * @return array
     */
    public function getSource () : array {
        
        return $this->_source;
    }
    
    /**
     * Возвращает результат расчёта цен по запросу
     * @return array
     */
    public function get(): array {

        $source = $this->_source;

        if ($this->_query['id']) {

            foreach ($this->_query['id'] as $id) {

                if (!isset($source[$id])) {

                    unset($source[$id]);
                }
            }
        }

        if ($this->_query['unix_dates']) {

            foreach ($this->_query['unix_dates'] as $timestamp) {

                foreach ($source as $id => $arr_sub) {

                    if (!isset($arr_sub[$timestamp])) {

                        unset($source[$id]);
                    }
                }
            }
        }

        $prices = array();

        foreach ($source as $id => $arr_sub) {
            
            $tmp_prices = null;
            foreach ($arr_sub as $timestamp => $arServiceData) {
                
                $price = 0;
                $date = date('d.m.Y', $timestamp);
                
                if ($this->_query['adults'] > 0 && $arServiceData['prices']['adult']['price'] > 0) {
                    $price = $this->_query['adults'] * $this->_converter->convert((float) $arServiceData['prices']['adult']['price'], (string) $arServiceData['prices']['adult']['currency']);
                }

                if ($this->_query['children'] > 0 && $arServiceData['prices']['children']['price'] > 0) {
                    $price += $this->_query['children'] * $this->_converter->convert((float) $arServiceData['prices']['children']['price'], (string) $arServiceData['prices']['children']['currency']);
                }

                if ($this->_query['adult_tour_service'] && $arServiceData['prices']['adult_tour_service']['price'] > 0) {
                    $price += $this->_converter->convert((float) $arServiceData['prices']['adult_tour_service']['price'], (string) $arServiceData['prices']['adult_tour_service']['currency']);
                }

                if ($this->_query['children_tour_service'] && $arServiceData['prices']['children_tour_service']['price'] > 0) {
                    $price += $this->_converter->convert((float) $arServiceData['prices']['children_tour_service']['price'], (string) $arServiceData['prices']['children_tour_service']['currency']);
                }
                
                $price = (float)$price;
                if ($price > 0) {
                                        
                    $tmp_prices[$date] = array(
                        'quota' => $arServiceData['quota'],
                        'duration' => $arServiceData['duration'],
                        'date_from' => $arServiceData['date_from'],
                        'date_to' => $arServiceData['date_to'],
                        'price_formatted' => $this->_converter->getFormatted($price, $this->_converter->getCurrentCurrencyIso()),
                        'price' => $price,
                        'currency' => $this->_converter->getCurrentCurrencyIso()
                    );
                }
            }
            
            if ($tmp_prices) {
                
                $prices[$id] = array(
                    
                    'id' => $id,
                    'dates' => $tmp_prices
                );
            }
        }
        
        $this->_resetQuery();
        
        return $prices;
    }

    /**
     * Возвращает минимальную цену для тура
     * @return array
     */
    public function getMinForTour(): array {

        $prices = array();

        foreach ($this->get() as $id => $arr_sub) {

            $arr_min = array('price' => exp(10));

            foreach ($arr_sub['dates'] as $arr_data) {

                if ($arr_min['price'] >= $arr_data['price']) {
                    
                    $arr_min = $arr_data;
                    $arr_min['id'] = $id;
                }
            }

            $prices[$id] = $arr_min;
        }

        return $prices;
    }
    
    /**
     * Возвращает минимальную цену по всем турам в поиске
     * @return array
     */
    public function getMinForTours(): array {

        $arr_min = array('price' => exp(10));

        foreach ($this->getMinForTour() as $arr_data) {

            if ($arr_data['price'] <= $arr_min['price']) {

                $arr_min = $arr_data;
            }
        }

        return $arr_min;
    }

    protected function _resetQuery() {

        $this->_query = array(
            'id' => null,
            'unix_dates' => null,
            'adults' => 0,
            'children' => 0,
            'adult_tour_service' => 0,
            'children_tour_service' => 0
        );
    }

}
