<?php

namespace travelsoft\booking\adapters;

use travelsoft\booking\abstraction\Store;
use travelsoft\booking\Settings;

\Bitrix\Main\Loader::includeModule("iblock");

/**
 * Класс адаптер для bitrix iblock
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
abstract class Iblock extends Store {

    /**
     * @var string
     */
    protected static $storeName = null;

    /**
     * Возвращает полученные данные из хранилища в виде массива
     * @param array $query
     * @param callable $callback
     * @return array
     */
    public static function get(array $query = array(), bool $likeArray = true,callable $callback = null) {

        if ($query['filter']) {

            $arFilter = $query["filter"];
        }

        $arFilter["IBLOCK_ID"] = self::getStoreId();

        if ($query['order']) {

            $arOrder = $query['order'];
        }

        if ($query['select']) {

            $arSelect = $query['select'];
        }

        if ($query['nav']) {

            $arNav = $query['nav'];
        }

        $dbList = \CIBlockElement::GetList($arOrder, $arFilter, null, $arNav, $arSelect);
        
        if (!$likeArray) {
            
            return $dbList;
        }
        
        $result = array();
        if ($callback) {

            while ($dbElement = $dbList->GetNextElement()) {

                $arFields = $dbElement->GetFields();
                if ($arFields["ID"] > 0) {

                    $arProperties = $dbElement->GetProperties();
                    $callback($arFields, $arProperties);
                    $result[$arFields["ID"]] = $arFields;
                    $result[$arFields["ID"]]["PROPERTIES"] = $arProperties;
                }
            }
        } else {

            while ($dbElement = $dbList->GetNextElement()) {

                $arFields = $dbElement->GetFields();
                if ($arFields["ID"] > 0) {

                    $arProperties = $dbElement->GetProperties();
                    $result[$arFields["ID"]] = $arFields;
                    $result[$arFields["ID"]]["PROPERTIES"] = $arProperties;
                }
            }
        }

        return (array) $result;
    }

    /**
     * Обновление записи по id
     * @param int $id
     * @param array $arUpdate
     * @return boolean
     */
    public static function update(int $id, array $arUpdate): bool {

        $ob = new \CIBlockElement;
        return boolval($ob->Update($id, $arUpdate));
    }

    /**
     * Добавляет запись в хранилище
     * @param array $arSave
     * @return int
     */
    public static function add(array $arSave): int {

        $ob = new \CIBlockElement;
        $arSave['IBLOCK_ID'] = self::getStoreId();
        return (int) $ob->Add($arSave);
    }

    /**
     * Удаляет запись из хранилища
     * @param int $id
     */
    public static function delete(int $id): bool {

        $ob = new \CIBlockElement;
        return boolval($ob->Delete($id));
    }

    /**
     * Возвращает поля записи таблицы по id
     * @param int $id
     * @return array
     */
    public static function getById(int $id): array {

        $class = get_called_class();
        
        $result = current($class::get(array("filter" => array("ID" => $id))));
        if (is_array($result) && !empty($result)) {
            
            return $result;
        } else {
            
            return array();
        }
    }

    /**
     * @return int
     */
    protected static function getStoreId(): int {

        $class = get_called_class();
        $tableId = $class::$storeName . "StoreId";
        return (int) Settings::$tableId();
    }

}
