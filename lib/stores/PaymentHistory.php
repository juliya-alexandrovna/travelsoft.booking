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

}
