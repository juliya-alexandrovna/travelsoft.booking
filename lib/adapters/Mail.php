<?php

namespace travelsoft\booking\adapters;

use travelsoft\booking\Settings;

/**
 *  Класс адаптер отправки почтовых уведомлений
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Mail {

    protected static $_commonMailParameters = array(
        "EVENT_NAME" => "TRAVELSOFT_BOOKING",
        "LID" => SITE_ID,
        "DUPLICATE" => "N"
    );

    /**
     * Отправка почтового уведомления клиенту при создании заказа
     * @param array $fields
     */
    public static function sendNewOrderNotificationForClient(array $fields) {

        \Bitrix\Main\Mail\Event::send(array_merge(array(
            "C_FIELDS" => $fields,
            "MESSAGE_ID" => Settings::mailIdForClientNotification()
                        ), self::$_commonMailParameters));
    }

    /**
     * Отправка почтового уведомления агенту при создании заказа
     * @param array $fields
     */
    public static function sendNewOrderNotificationForAgent(array $fields) {

        \Bitrix\Main\Mail\Event::send(array_merge(array(
            "C_FIELDS" => $fields,
            "MESSAGE_ID" => Settings::mailIdForAgentNotification()
                        ), self::$_commonMailParameters));
    }

    /**
     * Отправка почтового уведомления менеджеру при создании заказа
     * @param array $fields
     */
    public static function sendNewOrderNotificationForManager(array $fields) {
        
        $fields["EMAIL_TO"] = Settings::managerEmailForNotification();
        $fields["LANG"] = LANGUAGE_ID;
        
        \Bitrix\Main\Mail\Event::send(array_merge(array(
            "C_FIELDS" => $fields,
            "MESSAGE_ID" => Settings::mailIdForManagerNotification()
                        ), self::$_commonMailParameters));
    }

}
