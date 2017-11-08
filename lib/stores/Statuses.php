<?php

namespace travelsoft\booking\stores;

use travelsoft\booking\adapters\Highloadblock;

/**
 * Класс для работы с таблицей статусов заказа
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Statuses extends Highloadblock {

    protected static $storeName = 'statuses';

}
