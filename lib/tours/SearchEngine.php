<?php

namespace travelsoft\booking\tours;

use travelsoft\booking\abstraction\SearchEngine as AbstractSearchEngine;
use travelsoft\booking\stores\Tours;
use travelsoft\booking\stores\Quotas;
use travelsoft\booking\stores\Prices;
use travelsoft\booking\stores\PriceTypes;
use travelsoft\booking\adapters\Date;
use travelsoft\booking\Settings;

/**
 * Класс для поиска туров и расчёта цен
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class SearchEngine extends AbstractSearchEngine {
    
    /**
     * Производит поиск цен
     * @return \self
     */
    public function search() {

        $toursId = Tours::get(array(
                    'filter' => $this->_extFilter,
                    'select' => array('ID')
        ));
        
        if ($toursId) {
            
            $this->_setPreparedPricesData(
                    Prices::get(array('filter' => $this->_getPricesFilter(array_keys($toursId))))
            );
        }
        
        return $this;
    }

    /**
     * Фильтрует цены по stop sale
     * @param array $prices
     * @return \self
     */
    public function filterByStopSale(): self {

        $servicesId = array_keys($this->_prices);

        if ($servicesId) {

            $quotasFilter = $this->_getDatesFilter();
            $quotasFilter['UF_SERVICE_ID'] = $servicesId;
            $quotasFilter['UF_STOP'] = 1;

            $quotas = Quotas::get(array('filter' => $quotasFilter, 'select' => array('UF_UNIX_DATE', 'UF_SERVICE_ID')));

            foreach ($quotas as $arr_quota) {

                if (isset($this->_prices[$arr_quota['UF_SERVICE_ID']][$arr_quota['UF_UNIX_DATE']])) {

                    unset($this->_prices[$arr_quota['UF_SERVICE_ID']][$arr_quota['UF_UNIX_DATE']]);
                }
            }
        }

        return $this;
    }

    /**
     * Фильтрация результата по квоте
     * @param int $quota
     * @return \self
     */
    public function filterByQuotas(int $quota): self {

        $servicesId = array_keys($this->_prices);

        if ($servicesId) {

            $quotasFilter = $this->_getDatesFilter();
            $quotasFilter['UF_SERVICE_ID'] = $servicesId;

            $quotas = Quotas::get(array('filter' => $quotasFilter, 'select' => array('UF_QUOTA', 'UF_UNIX_DATE', 'UF_SERVICE_ID')));

            foreach ($quotas as $arr_quota) {

                if (isset($this->_prices[$arr_quota['UF_SERVICE_ID']][$arr_quota['UF_UNIX_DATE']]) && $arr_quota['UF_QUOTA'] < $quota) {

                    unset($this->_prices[$arr_quota['UF_SERVICE_ID']][$arr_quota['UF_UNIX_DATE']]);
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function _getDatesFilter(): array {

        if ($this->_extFilter['><PROPERTY_' . Settings::tourDatePropertyId()]) {
            
            return array('><UF_DATE' => array_map(function ($date) { return Date::create($date); }, $this->_extFilter['><PROPERTY_' . Settings::tourDatePropertyId()]));
        }

        if ($this->_extFilter['>PROPERTY_' . Settings::tourDatePropertyId()]) {
            
            return array('>UF_DATE' => Date::create($this->_extFilter['>PROPERTY_' . Settings::tourDatePropertyId()]));
        }

        if ($this->_extFilter['<PROPERTY_' . Settings::tourDatePropertyId()]) {

            return array('<UF_DATE' => Date::create($this->_extFilter['><PROPERTY_' . Settings::tourDatePropertyId()]));
        }
        
        if ($this->_extFilter['>=PROPERTY_' . Settings::tourDatePropertyId()]) {

            return array('>=UF_DATE' => Date::create($this->_extFilter['>=PROPERTY_' . Settings::tourDatePropertyId()]));
        }

        if ($this->_extFilter['<=PROPERTY_' . Settings::tourDatePropertyId()]) {

            return array('<=UF_DATE' => Date::create($this->_extFilter['<=PROPERTY_' . Settings::tourDatePropertyId()]));
        }
        
        if ($this->_extFilter['PROPERTY_' . Settings::tourDatePropertyId()]) {
        
            return array('<=UF_DATE' => Date::create($this->_extFilter['<=PROPERTY_' . Settings::tourDatePropertyId()]));
        }

        return array();
    }

    /**
     * Возвращает фильтр в виде массива для поиска цен
     * @param array $toursId
     * @return array
     */
    protected function _getPricesFilter(array $toursId): array {
        
        $pricesFilter = $this->_getDatesFilter();
        $pricesFilter['UF_SERVICE_ID'] = $toursId;

        return $pricesFilter;
    }

    /**
     * Обрабатывает найденные цены
     * @param array $prices
     */
    protected function _setPreparedPricesData(array $prices) {
        
        $pts = array();

        foreach ($prices as $price) {

            if (!isset($pts[$price['UF_PRICE_TYPE_ID']])) {

                $pts[$price['UF_PRICE_TYPE_ID']] = current(PriceTypes::get(array(
                            'filter' => array('ID' => $price['UF_PRICE_TYPE_ID']),
                )));
            }

            $this->_prices[$price['UF_SERVICE_ID']][$price['UF_UNIX_DATE']][$pts[$price['UF_PRICE_TYPE_ID']]['UF_CODE']] = array(
                'price' => $price['UF_GROSS'],
                'currency' => $pts[$price['UF_PRICE_TYPE_ID']]['UF_CURRENCY_ISO']
            );  
        }
    }
}
