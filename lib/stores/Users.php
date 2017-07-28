<?php
namespace travelsoft\booking\stores;

use \travelsoft\currency\interfaces\Store;

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
        }
        
        if ($query['limit']) {
            
            $arParameters['NAV_PARAMS']['nPageSize'] = $query['limit'];
        }
        
        $dbList = CUser::GetList($by, $order, $arFilter, $arParameters);
        
        if (!$likeArray) {
            
            return $dbList;
        }
        
        $result = array();
        if ($callback) {

            while ($arUser = $dbList->GetNext()) {

                $callback($arUser);
                $result[$arUser['ID']] = $arUser;
            }
        } else {

            while ($arUser = $dbList->GetNext()) {

                $result[$arUser['ID']] = $arUser;
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
}
