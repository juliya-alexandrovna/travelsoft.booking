<?php
namespace travelsoft\booking\stores;

use travelsoft\booking\abstraction\Store;

/**
 * Класс для работы с таблицей пользователей
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Users extends Store {
    
    /**
     * Возвращает полученные данные из хранилища в виде массива
     * @param array $query
     * @param callable $callback
     * @return mixed
     */
    public static function get(array $query = array(), bool $likeArray = true, callable $callback = null) {

        $arFilter = array();
        if ($query['filter']) {
            
            $arFilter = $query['filter'];
        }
        
        $by = "ID";
        $order = "ASC";
        if ($query['order']) {
            
            $by = key($query['order']);
            $order = current($query['order']);
        }
        
        $arParameters = array();
        if ($query['select']) {
            
            $arParameters['SELECT'] = array();
            $arParameters['FIELDS'] = array();
            
            foreach ($query['select'] as $field) {
                
                if (strpos($field, "UF_") === 0) {
                    
                    $arParameters['SELECT'][] = $field;
                } else {
                    
                    $arParameters['FIELDS'][] = $field;
                }
            }
            
            if (!in_array('ID', $arParameters['FIELDS'])) {
                $arParameters['FIELDS'][] = 'ID';
            }
        }
        
        if ($query['limit']) {
            
            $arParameters['NAV_PARAMS']['nPageSize'] = $query['limit'];
        }
        
        $dbList = $GLOBALS['USER']->GetList($by, $order, $arFilter, $arParameters);
        
        if (!$likeArray) {
            
            return $dbList;
        }
        
        $result = array();
        if ($callback) {

            while ($arUser = $dbList->GetNext()) {

                $callback($arUser);
                $result[$arUser['ID']] = $arUser;
                $result[$arUser['ID']]['FULL_NAME'] = self::getFullUserNameByFields($arUser);
                $result[$arUser['ID']]['FULL_NAME_WITH_EMAIL'] = self::getFullUserNameWithEmailByFields($arUser);
            }
        } else {

            while ($arUser = $dbList->GetNext()) {

                $result[$arUser['ID']] = $arUser;
                $result[$arUser['ID']]['FULL_NAME'] = self::getFullUserNameByFields($arUser);
                $result[$arUser['ID']]['FULL_NAME_WITH_EMAIL'] = self::getFullUserNameWithEmailByFields($arUser);
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
        
        return boolval($GLOBALS['USER']->Update($id, $arUpdate));
    }

    /**
     * Добавляет запись в хранилище
     * @param array $arSave
     * @return int
     */
    public static function add(array $arSave): int {

        return (int) $GLOBALS['USER']->Add($arSave);
    }

    /**
     * Удаление записи в базе
     * @param int $id
     */
    public static function delete(int $id): bool {
        return boolval($GLOBALS['USER']->Delete($id));
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
    
    /**
     * Возвращает полное имя пользователя по входным полям
     * @param array $fields
     * @return string
     */
    public static function getFullUserNameByFields (array $fields): string {
        
        $userName = "";
        if (strlen($fields['NAME']) > 0) {
            $userName = $fields['NAME'];
        }

        if (strlen($userName) > 0) {

            if (strlen($fields['SECOND_NAME']) > 0) {
                $userName .= ' ' . $fields['SECOND_NAME'];
            }

            if (strlen($fields['LAST_NAME']) > 0) {
                $userName .= ' ' . $fields['LAST_NAME'];
            }
        }
        
        return $userName;
    }
    
    /**
     * Возвращает полное имя пользователя с email по входным полям
     * @param array $fields
     * @return string
     */
    public static function getFullUserNameWithEmailByFields (array $fields): string {
        
        $userName = self::getFullUserNameByFields($fields);
        if (strlen($userName) > 0 && strlen($fields['EMAIL']) > 0) {
            $userName .= '[' . $fields['EMAIL'] . ']';
        }
        
        return $userName;
    }
}
