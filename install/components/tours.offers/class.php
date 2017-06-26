<?php

use travelsoft\booking\tours\SearchEngine;
use travelsoft\booking\Settings;
use travelsoft\booking\stores\Quotas;
use travelsoft\booking\adapters\Date;

/**
 * Класс туристических предложений
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class TravelsoftToursOffers extends CBitrixComponent {

    /**
     * @var \travelsoft\booking\adapters\CurrencyConverter
     */
    protected $_converter;

    /**
     * @return array
     */
    protected function _getExtFilter(): array {

        $extFilter = array("ACTIVE" => "Y");

        if (!empty($this->arParams['ID'])) {

            $extFilter['ID'] = $this->arParams['ID'];
        }

        if (is_array($this->arParams['DATES']) && !empty($this->arParams['DATES'])) {

            $extFilter['=PROPERTY_' . Settings::tourDatePropertyId()] = $this->arParams['DATES'];
        }

        if (!empty($GLOBALS[$this->arParams['GLOBAL_FILTER_NAME']])) {

            $extFilter = array_merge($extFilter, $GLOBALS[$this->arParams['GLOBAL_FILTER_NAME']]);
        }

        return $extFilter;
    }

    /**
     * @return array
     */
    protected function _getQuotasFilter(): array {

        $quotasFilter = array('UF_SERVICE_ID' => array_keys($this->arResult['COST_PREPARED']));

        if (is_array($this->arParams['DATES']) && !empty($this->arParams['DATES'])) {

            $quotasFilter['=UF_DATE'] = array_map(function ($date) {

                return Date::create($date);
            }, $this->arParams['DATES']);
        }

        return $quotasFilter;
    }

    /**
     * @param float $price
     * @param string $inIso
     * @return array
     */
    protected function _convert(float $price, string $inIso): array {

        $result = array();

        foreach ($this->arParams['SHOW_CURRENCY_ISO'] as $iso) {
            $result[] = array(
                'price' => $this->_converter->format($this->_converter->convert($price, $inIso, $iso)),
                'currency' => $iso
            );
        }

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function _converting(array $data): array {

        $result = array();

        if ($data['price'] > 0) {

            $result = $this->_convert($data['price'], $data['currency']);
        }

        return $result;
    }

    /**
     * Обработка входных параметров
     * @throws \Exception
     */
    public function prepareParameters() {

        if (!\Bitrix\Main\Loader::includeModule('travelsoft.booking')) {

            throw new \Exception('Модуль travelsoft.booking не найден');
        }

        if (!\Bitrix\Main\Loader::includeModule('new.travelsoft.currency')) {

            throw new \Exception('Модуль travelsoft.currency не найден');
        }

        $this->arParams['ID'] = array_filter($this->arParams['ID'], function ($id) {
            return $id > 0;
        });

        if (is_array($this->arParams['DATES']) && !empty($this->arParams['DATES'])) {

            $this->arParams['DATES'] = array_map(function ($date) {
                return ConvertDateTime($date, "YYYY-MM-DD HH:MI:SS", "ru");
            }, $this->arParams['DATES']);
        }

        if (empty($this->arParams['SHOW_CURRENCY_ISO'])) {

            throw new \Exception('Укажите в какой валюте необходимо выводит цены');
        }
    }

    /**
     * Устанавливает arResult
     */
    public function setData() {

        $this->arResult = array();

        $se = new SearchEngine;

        if (($extFilter = $this->_getExtFilter())) {

            $se->setExtFilter($extFilter);
        }

        $this->arResult['COST'] = $se->search()->filterByStopSale()->filterByQuotas(1)->getCost()->getSource();

        $this->arResult['COST_PREPARED'] = array();

        $this->_converter = new travelsoft\booking\adapters\CurrencyConverter;

        foreach ($this->arResult['COST'] as $id => $arr_sub) {

            $this->arResult['COST_PREPARED'][$id] = array(
                'id' => $id,
                'dates' => array()
            );

            foreach ($arr_sub as $timestamp => $arr_prices) {

                $arPrices = array();

                foreach ($arr_prices as $type => $arr_price) {

                    $arPrices[$type] = $this->_converting($arr_price);
                }

                $date = date('d.m.Y', $timestamp);
                $this->arResult['COST_PREPARED'][$id]['dates'][$date] = array(
                    'date' => $date,
                    'prices' => $arPrices
                );

                usort($this->arResult['COST_PREPARED'][$id]['dates'], function ($d1, $d2) {
                    return new DateTime($d1["date"]) > new DateTime($d2["date"]);
                });
            }
        }

        if (!empty($this->arResult['COST_PREPARED'])) {
            $this->arResult['QUOTAS'] = array();
            $query = array(
                'filter' => $this->_getQuotasFilter(),
                'select' => array('ID', 'UF_QUOTA', 'UF_DATE', 'UF_SERVICE_ID')
            );

            foreach (Quotas::get($query) as $arQuota) {

                $this->arResult['QUOTAS'][$arQuota['UF_SERVICE_ID']][$arQuota['UF_DATE']->format('d.m.Y')] = $arQuota['UF_QUOTA'];
            }
        }
    }

    /**
     * component body
     */
    public function executeComponent() {

        try {

            $this->prepareParameters();

            $this->setData();

            $this->IncludeComponentTemplate();
        } catch (\Exception $e) {

            ShowError($e->getMessage());
        }
    }

}
