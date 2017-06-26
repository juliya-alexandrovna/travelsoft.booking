<?php

namespace travelsoft {

    if (!function_exists("\\travelsoft\\ats")) {

        /**
         * Возвращает результат конвертации массива в строку
         * @param array $arFields
         * @return string
         */
        function ats(array $arFields): string {
            return base64_encode(gzcompress(serialize($arFields), 9));
        }

    }

    if (!function_exists("\\travelsoft\\sta")) {

        /**
         * Возвращает результат конвертации строки в массив
         * @param string $str
         * @return array
         */
        function sta(string $str): array {
            return (array) unserialize(gzuncompress(base64_decode($str)));
        }

    }
}

namespace travelsoft\booking {

    if (!function_exists("\\travelsoft\\booking\\crmAccess")) {

        /**
         * Определяет права доступа к CRM
         * @global type $USER
         * @return bool
         */
        function crmAccess(): bool {

            global $USER;

            $access = false;
            if ($USER->IsAdmin()) {

                $access = true;
            } else {

                $allowGroups = array(
                    Settings::managersUGroup()
                );
                $arUserGroups = $USER->GetUserGroupArray();

                foreach ($arUserGroups as $groupId) {

                    if (in_array($groupId, $allowGroups)) {
                        $access = true;
                        break;
                    }
                }
            }

            return $access;
        }

    }

    if (!function_exists("\\travelsoft\\booking\\getDaysArrayFromPeriod")) {

        /**
         * Возвращает массив всех указанных дней недели в указанном формате из указанного периода
         * @param int $udf дата начала периода в unix формате
         * @param int $udt дата окончания периода в unix формате
         * @param int $day номер дня недели (1-7)
         * @param string $format формат возвращаемой даты
         * @return array
         */
        function getDaysArrayFromPeriod(int $udf, int $udt, int $day, string $format = 'd.m.Y'): array {

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

    }

    if (!function_exists("\\travelsoft\\booking\\getDaysArrayByPeriod")) {

        /**
         * Возвращает массив дат из указанного периода в указанном формате
         * @param int $udf
         * @param int $udt
         * @param string $format
         * @return array
         */
        function getDaysArrayByPeriod(int $udf, int $udt, string $format = 'd.m.Y'): array {

            $result = array();
            $ud = $udf;

            while ($ud <= $udt) {

                $result[$ud] = date($format, $ud);

                $ud += 86400;
            }

            return $result;
        }

    }

    if (!function_exists("\\travelsoft\\booking\\getToursMinPricesForList")) {

        function getToursMinPricesForList(array $extFilter = array()): array {
            
            $searchEngine = new \travelsoft\booking\tours\SearchEngine;
            if (!empty($extFilter)) {

                # производим поиск цен по фильтру
                $cost = $searchEngine->setExtFilter($extFilter)->search()->filterByStopSale()->getCost();
            } else {

                # производим поиск цен с сегоднешнего дня
                $cost = $searchEngine->setExtFilter(array('>PROPERTY_78' => date('d.m.Y')))->search()->getCost();
            }
            
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
            
            return $arSearchAdultsPrices;
        }
    }
}