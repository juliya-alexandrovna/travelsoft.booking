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
         * Возвращает список рассчитанных цен для страницы списка туров
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
}