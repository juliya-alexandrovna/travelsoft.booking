<?php

namespace travelsoft\booking\stores;

use travelsoft\booking\adapters\Highloadblock;

/**
 * Класс для работы с таблицей истории платежей
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class PaymentHistory extends Highloadblock {

    protected static $storeName = 'paymentHistory';
    
    /**
     * Возвращает информацию по последнему платежу по заказу
     * @param int $orderId
     * @return array
     */
    public static function getLastPaymentByOrderId (int $orderId) : array {
        
        return (array)current(self::get(array('order' => array('ID' => 'DESC'), 'filter' => array('UF_ORDER_ID' => $orderId),
            'limit' => 1)));
    }
    
}
