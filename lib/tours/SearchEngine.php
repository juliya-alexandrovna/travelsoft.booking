<?php

namespace travelsoft\booking\tours;

use travelsoft\booking\abstraction\SearchEngine as AbstractSearchEngine;
use travelsoft\booking\stores\Tours;
use travelsoft\booking\stores\Quotas;
use travelsoft\booking\stores\Prices;
use travelsoft\booking\stores\PriceTypes;
use travelsoft\booking\adapters\Date;
use \travelsoft\booking\stores\Duration;

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

        if ($this->_prices) {

            foreach ($this->_prices as $serviceId => $arServiceData) {

                foreach ($arServiceData as $timestamp => $arData) {

                    if ($arData['stop_sale']) {

                        unset($this->_prices[$serviceId][$timestamp]);
                    }
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

        if ($this->_prices) {

            foreach ($this->_prices as $serviceId => $arServiceData) {

                foreach ($arServiceData as $timestamp => $arData) {

                    if ($arData['quota'] < $quota) {

                        unset($this->_prices[$serviceId][$timestamp]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Возвращает фильтр в виде массива для поиска цен
     * @param array $toursId
     * @return array
     */
    protected function _getPricesFilter(array $toursId): array {

        if (!empty($this->_extFilter['><UF_DATE']) && is_array($this->_extFilter['><UF_DATE'])) {

            $pricesFilter = array('><UF_DATE' => array_map(function ($date) {
                            return Date::create($date);
                        }, $this->_extFilter['><UF_DATE']));
        } elseif ($this->_extFilter['>UF_DATE']) {

            $pricesFilter = array('>UF_DATE' => Date::create($this->_extFilter['>UF_DATE']));
        } elseif ($this->_extFilter['<UF_DATE']) {

            $pricesFilter = array('<UF_DATE' => Date::create($this->_extFilter['<UF_DATE']));
        } elseif ($this->_extFilter['>=UF_DATE']) {

            $pricesFilter = array('>=UF_DATE' => Date::create($this->_extFilter['><UF_DATE']));
        } elseif ($this->_extFilter['<=UF_DATE']) {

            $pricesFilter = array('<=UF_DATE' => Date::create($this->_extFilter['<=UF_DATE']));
        } else {

            $pricesFilter = array('>UF_DATE' => Date::create(date('d.m.Y', time())));
        }

        $pricesFilter['UF_SERVICE_ID'] = $toursId;

        return $pricesFilter;
    }

    /**
     * Обрабатывает найденные цены
     * @param array $prices
     */
    protected function _setPreparedPricesData(array $prices) {

        if (!empty($prices)) {

            $arDurations = $arQuotas = $arPriceTypes = array();

            $servicesId = array_map(function ($arPrice) {
                return $arPrice['UF_SERVICE_ID'];
            }, $prices);

            if (!empty($servicesId)) {

                $priceTypesId = array_map(function ($arPrice) {
                    return $arPrice['UF_PRICE_TYPE_ID'];
                }, $prices);

                if (!empty($priceTypesId)) {

                    $dates = array_map(function ($arPrice) {
                        return $arPrice['UF_DATE'];
                    }, $prices);

                    $dbDurations = Duration::get(array(
                                'filter' => array('UF_DATE' => $dates, 'UF_SERVICE_ID' => $servicesId),
                                'select' => array('ID', 'UF_UNIX_DATE', 'UF_DURATION', 'UF_SERVICE_ID')
                    ));

                    foreach ($dbDurations as $arDuration) {

                        $arDurations[$arDuration['UF_SERVICE_ID']][$arDuration['UF_UNIX_DATE']] = $arDuration['UF_DURATION'];
                    }

                    $dbQuotas = Quotas::get(array(
                                'filter' => array('UF_DATE' => $dates, 'UF_SERVICE_ID' => $servicesId),
                                'select' => array('ID', 'UF_UNIX_DATE', 'UF_QUOTA', 'UF_SERVICE_ID', 'UF_STOP')
                    ));

                    foreach ($dbQuotas as $arQuota) {

                        $arQuotas[$arQuota['UF_SERVICE_ID']][$arQuota['UF_UNIX_DATE']] = array(
                            'stop_sale' => $arQuota['UF_STOP'],
                            'quota' => $arQuota['UF_QUOTA']
                        );
                    }

                    $arPriceTypes = PriceTypes::get(
                                    array(
                                        'filter' => array('ID' => $priceTypesId)
                                    )
                    );

                    foreach ($prices as $price) {

                        if (!$this->_prices[$price['UF_SERVICE_ID']][$price['UF_UNIX_DATE']]) {

                            $this->_prices[$price['UF_SERVICE_ID']][$price['UF_UNIX_DATE']] = array(
                                'duration' => $arDuration[$price['UF_SERVICE_ID']][$price['UF_UNIX_DATE']] >= 1 ? $arDuration[$price['UF_SERVICE_ID']][$price['UF_UNIX_DATE']] : 1,
                                'quota' => $arQuotas[$price['UF_SERVICE_ID']][$price['UF_UNIX_DATE']]['quota'],
                                'stop_sale' => $arQuotas[$price['UF_SERVICE_ID']][$price['UF_UNIX_DATE']]['stop_sale'],
                                'prices' => array()
                            );
                        }

                        $this->_prices[$price['UF_SERVICE_ID']][$price['UF_UNIX_DATE']]['prices'][$arPriceTypes[$price['UF_PRICE_TYPE_ID']]['UF_CODE']] = array(
                            'price' => $price['UF_GROSS'],
                            'currency' => $arPriceTypes[$price['UF_PRICE_TYPE_ID']]['UF_CURRENCY_ISO']
                        );
                    }
                }
            }
        }
    }

}
