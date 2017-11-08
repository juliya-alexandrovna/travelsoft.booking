<?php

namespace travelsoft\booking\stores;

use travelsoft\booking\adapters\Highloadblock;

/**
 * Класс для работы с таблицей статусов заказа
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Tourists extends Highloadblock {

    protected static $storeName = 'tourists';
    
    /**
     * @param array $query
     * @param boolean $likeArray
     * @param \travelsoft\booking\stores\callable $callback
     * @return array
     */
    public static function get(array $query = array(), bool $likeArray = true, callable $callback = null) {
        return parent::get($query, $likeArray, function (&$item) use ($callback) {
            
                    if ($callback) {
                        $callback($item);
                    }
                    
                    $item['FULL_NAME'] = "";
                    $item['FULL_LAT_NAME'] = "";
                    $item['SN_PASSPORT'] = "";
                    $item['ADDRESS'] = "";
                    
                    if (strlen($item['UF_NAME']) > 0) {
                        $item['FULL_NAME'] = $item['UF_NAME'];
                    }

                    if (strlen($item['FULL_NAME']) > 0) {

                        if (strlen($item['UF_SECOND_NAME']) > 0) {
                            $item['FULL_NAME'] .= ' ' . $item['UF_SECOND_NAME'];
                        }

                        if (strlen($item['UF_LAST_NAME']) > 0) {
                            $item['FULL_NAME'] .= ' ' . $item['UF_LAST_NAME'];
                        }
                    }

                    if (strlen($item['UF_NAME_LAT'])) {
                        $item['FULL_NAME_LAT'] .= $item['UF_NAME_LAT'];
                    }

                    if (strlen($item['FULL_NAME_LAT']) > 0 && strlen($item['UF_LAST_NAME_LAT'])) {
                        $item['FULL_NAME_LAT'] .= ' ' . $item['UF_LAST_NAME_LAT'];
                    }
                    
                    $item['UF_PASS_SERIES'] = trim($item['UF_PASS_SERIES']);
                    $item['UF_PASS_NUMBER'] = trim($item['UF_PASS_NUMBER']);
                    if (strlen($item['UF_PASS_SERIES']) > 0) {
                        
                        $item['SN_PASSPORT'] .= $item['UF_PASS_SERIES'];
                        if (strlen($item['SN_PASSPORT']) > 0) {
                            $item['SN_PASSPORT'] .= $item['UF_PASS_NUMBER'];
                        }
                        
                    }
                    
                    $addressPull = array();
                    
                    $item['UF_COUNTRY'] = trim($item['UF_COUNTRY']);
                    if (strlen($item['UF_COUNTRY']) > 0) {
                        $addressPull[] = $item['UF_COUNTRY'];
                    }
                    
                    $item['UF_CITY'] = trim($item['UF_CITY']);
                    if (strlen($item['UF_CITY']) > 0) {
                        $addressPull[] = $item['UF_CITY'];
                    }
                    
                    $item['UF_REGION'] = trim($item['UF_REGION']);
                    if (strlen($item['UF_REGION']) > 0) {
                        $addressPull[] = $item['UF_REGION'];
                    }
                    
                    $item['UF_DISTRICT'] = trim($item['UF_DISTRICT']);
                    if (strlen($item['UF_DISTRICT']) > 0) {
                        $addressPull[] = $item['UF_DISTRICT'];
                    }
                    
                    $item['UF_STREET'] = trim($item['UF_STREET']);
                    if (strlen($item['UF_STREET']) > 0) {
                        $addressPull[] = $item['UF_STREET'];
                    }
                    
                    $item['UF_HOME'] = trim($item['UF_HOME']);
                    if (strlen($item['UF_HOME']) > 0) {
                        $addressPull[] = $item['UF_HOME'];
                    }
                    
                    $item['UF_FLAT'] = trim($item['UF_FLAT']);
                    if (strlen($item['UF_FLAT']) > 0) {
                        $addressPull[] = $item['UF_FLAT'];
                    }
                    
                    $item['ADDRESS'] = implode(" ", $addressPull);
                });
    }

}
