<?php

namespace travelsoft\booking\adapters;

use travelsoft\booking\abstraction\Store;
use travelsoft\booking\Settings;
use Bitrix\Highloadblock\HighloadBlockTable as HL;

\Bitrix\Main\Loader::includeModule("highloadblock");

/**
 * Класс адаптер для bitrix highloadblock
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
abstract class Highloadblock extends Store {

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
    public static function get(array $query = array(), callable $callback = null): array {

        $table = self::getTable();
        $dbList = $table::getList((array) $query);
        $result = array();
        if ($callback) {
            while ($res = $dbList->fetch()) {
                $callback($res);
                $result[$res["ID"]] = $res;
            }
        } else {
            while ($res = $dbList->fetch()) {
                $result[$res["ID"]] = $res;
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

        $table = self::getTable();
        return boolval($table::update($id, $arUpdate));
    }

    /**
     * Добавляет запись в хранилище
     * @param array $arSave
     * @return int
     */
    public static function add(array $arSave): int {

        $table = self::getTable();
        return (int) $table::add($arSave)->getId();
    }

    /**
     * 
     * @param int $id
     */
    public static function delete(int $id): bool {

        $table = self::getTable();
        return boolval($table::delete($id));
    }

    /**
     * Возвращает поля записи таблицы по id
     * @param int $id
     * @return array
     */
    public static function getById(int $id): array {

        $class = get_called_class();
        return (array) current($class::get(array("filter" => array("ID" => $id))));
    }

    /**
     * @return string
     */
    protected static function getTable(): string {

        $class = get_called_class();
        $tableId = $class::$storeName . "StoreId";
        return HL::compileEntity(HL::getById(Settings::$tableId())->fetch())->getDataClass();
    }

}
