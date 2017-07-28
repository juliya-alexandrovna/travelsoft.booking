<?php

namespace travelsoft\booking\stores;

use travelsoft\currency\interfaces\Store;
use Bitrix\Main\GroupTable;

/**
 * Класс для работы с таблицей групп пользователей
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class UserGroups extends Store{
    
    /**
     * Возвращает полученные данные из хранилища в виде массива
     * @param array $query
     * @param callable $callback
     * @return mixed
     */
    public static function get(array $query = array(), bool $likeArray = true, callable $callback = null) {

        
        $dbList = GroupTable::getList($query);
        
        if (!$likeArray) {
            
            return $dbList;
        }
        
        $result = array();
        if ($callback) {

            while ($arGroup = $dbList->fetch()) {

                $callback($arGroup);
                $result[$arGroup['ID']] = $arGroup;
            }
        } else {

            while ($arGroup = $dbList->fetch()) {

                $result[$arGroup['ID']] = $arGroup;
            }
        }
        
        return $result;
    }

    /**
     * Обновление записи по id
     * @param int $id
     * @param array $arUpdate
     * @return boolean
     */
    public static function update(int $id, array $arUpdate): bool {
        
        return boolval(GroupTable::update($id, $arUpdate));
    }

    /**
     * Добавляет запись в хранилище
     * @param array $arSave
     * @return int
     */
    public static function add(array $arSave): int {

        return (int) GroupTable::add($arSave);
    }

    /**
     * Удаление записи в базе
     * @param int $id
     */
    public static function delete(int $id): bool {
        return boolval(GroupTable::delete($id));
    }

    /**
     * Возвращает поля записи таблицы по id
     * @param int $id
     * @return array
     */
    public static function getById(int $id): array {

        $result = current(self::get(array("filter" => array("ID" => $id))));
        if (is_array($result) && !empty($result)) {

            return $result;
        } else {

            return array();
        }
    }
}
