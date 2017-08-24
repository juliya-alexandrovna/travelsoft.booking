<?php

namespace travelsoft\booking;

/**
 * Класс утилит
 * 
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Utils {

    /**
     * Возвращает результат конвертации массива в строку
     * @param array $arFields
     * @return string
     */
    public static function ats(array $arFields): string {
        return base64_encode(gzcompress(serialize($arFields), 9));
    }

    /**
     * Возвращает результат конвертации строки в массив
     * @param string $str
     * @return array
     */
    public static function sta(string $str): array {
        return (array) unserialize(gzuncompress(base64_decode($str)));
    }

    /**
     * Возвращает массив всех указанных дней недели в указанном формате из указанного периода
     * @param int $udf дата начала периода в unix формате
     * @param int $udt дата окончания периода в unix формате
     * @param int $day номер дня недели (1-7)
     * @param string $format формат возвращаемой даты
     * @return array
     */
    public static function getDaysArrayFromPeriod(int $udf, int $udt, int $day, string $format = 'd.m.Y'): array {

        $result = array();
        $ud = $udf;

        while ($ud <= $udt) {

            if ((int) date("N", $ud) === $day) {

                $result[$ud] = date($format, $ud);
            }

            $ud += 86400;
        }

        return $result;
    }

    /**
     * Возвращает массив дат из указанного периода в указанном формате
     * @param int $udf
     * @param int $udt
     * @param string $format
     * @return array
     */
    public static function getDaysArrayByPeriod(int $udf, int $udt, string $format = 'd.m.Y'): array {

        $result = array();
        $ud = $udf;

        while ($ud <= $udt) {

            $result[$ud] = date($format, $ud);

            $ud += 86400;
        }

        return $result;
    }

    /**
     * Возвращает список рассчитанных цен для страницы списка туров по фильтру
     * Производит замену параметров (ID и $dateProperty) фильтре поиска для списка туров 
     * @param array $extFilter
     * @param string $dateProperty ключ, который содержит даты для поиска
     * @return array
     */
    public static function getToursMinPricesForList(& $extFilter, string $dateProperty): array {

        $searchEngine = new \travelsoft\booking\tours\SearchEngine;
        if (is_array($extFilter) && !empty($extFilter)) {

            # формирование массива дат для поиска цен
            if ($extFilter[$dateProperty]) {

                $extFilter['><UF_DATE'] = $extFilter[$dateProperty];
                unset($extFilter[$dateProperty]);
            }

            # фильтр для поиска цен
            $cost = $searchEngine->setExtFilter($extFilter);
        }

        $cost = $searchEngine->search()->filterByStopSale()->getCost();

        # цена за взрослого
        $arSearchAdultsPrices = $cost->forAdults(1)->getMinForTour();

        # цена за ребенка
        $arSearchChildrenPrices = $cost->forChildren(1)->getMinForTour();

        # дополняем цены ценой за ребенка, если не установлена цена за взрослого
        foreach ($arSearchChildrenPrices as $id => $arr_values) {

            if (!isset($arSearchAdultsPrices[$id])) {

                $arSearchAdultsPrices[$id] = $arr_values;
            }
        }

        $extFilter['ID'] = !empty($arSearchAdultsPrices) ? array_keys($arSearchAdultsPrices) : array(-1);

        if ($extFilter['><UF_DATE']) {
            unset($extFilter['><UF_DATE']);
        }

        return $arSearchAdultsPrices;
    }

    /**
     * Проверка наличия квот для бронирования
     * @param int $offerId
     * @param string $dateFrom
     * @param int $quota
     * @return bool
     */
    public static function checkQuota(int $offerId, string $dateFrom, int $quota): bool {

        $extFilter = array(
            'ID' => $offerId,
            'UF_DATE' => $dateFrom
        );

        $arSourceData = (new \travelsoft\booking\tours\SearchEngine)
                ->setExtFilter($extFilter)
                ->filterByQuotas($quota)
                ->filterByStopSale()
                ->search()
                ->getCost()
                ->getSource();

        if (empty($arSourceData)) {

            return false;
        }

        return true;
    }

    /**
     * Увеличевает количество проданных
     * @param int $offerId
     * @param string $dateFrom
     * @param int $count
     */
    public static function increaseNumberOfSold(int $offerId, string $dateFrom, int $count) {

        # увеличиваем количество проданных
        $arQuota = current(stores\Quotas::get(array(
                    'filter' => array(
                        'UF_SERVICE_ID' => $offerId,
                        'UF_DATE' => adapters\Date::create($dateFrom)
                    ),
                    'select' => array(
                        'ID', 'UF_SOLD_NUMBER', 'UF_QUOTA'
                    )
        )));

        $value = $arQuota['UF_SOLD_NUMBER'] + $count;

        stores\Quotas::update($arQuota['ID'], array(
            'UF_SOLD_NUMBER' => $value
        ));
    }

    /**
     * @global CMain $APPLICATION
     * @global CUser $USER
     * @var array $arParams
     * @var array $arResult
     * @var CatalogSectionComponent $component
     * @var CBitrixComponentTemplate $this
     * @var string $templateName
     * @var string $componentPath
     */
    public static function _er(string $code, bool $div = false, string $message = '') {

        $text = strlen($message) > 0 ? $message : GetMessage($code);

        if ($div) {
            echo '<div class="error">' . $text . '</div>';
        } else {
            echo '<span class="error">' . $text . '</span>';
        }
    }

    /**
     * Обертка метода self::_er()
     * @param array $arErrors
     * @param string $code
     * @param bool $div
     * @param string $message
     */
    public static function showError(array $arErrors = array(), string $code, bool $div = false, string $message = '') {

        if (in_array($code, $arErrors)) {
            Utils::_er($code, $div);
        }
    }

    /**
     * Склеивает элементы массива в строку
     * @param array $array
     * @return string
     */
    public static function gluingAnArray(array $array, string $delemiter = ' '): string {

        return implode($delemiter, array_filter($array, function ($item) {
                    return strlen($item) > 0;
                }));
    }

    /**
     * Аннулирование путевки
     * @param int $serviceId
     * @param string $dateFrom
     * @param int $adults
     * @param int $children
     */
    public static function bookingTourCancellation(int $serviceId, string $dateFrom, int $adults, int $children) {

        if (strtotime($dateFrom) > time()) {
            
            $arQuota = current(stores\Quotas::get(array(
                        'filter' => array(
                            'UF_SERVICE_ID' => $serviceId,
                            'UF_DATE' => adapters\Date::create($dateFrom)
                        ),
                        'select' => array(
                            'ID', 'UF_SOLD_NUMBER'
                        ))
            ));

            if ($arQuota['ID'] > 0) {

                $soldNumber = intVal($arQuota['UF_SOLD_NUMBER']) - $adults - $children;

                stores\Quotas::update($arQuota['ID'], array('UF_SOLD_NUMBER' => $soldNumber < 0 ? 0 : $soldNumber));
            }
        }
    }

}
