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
     * @param int $order_id
     * @param string $email
     * @param string $status
     */
    public static function sendNewOrderNotificationForClient(int $order_id, string $email, string $status) {

        \Bitrix\Main\Mail\Event::send(array_merge(array(
            "C_FIELDS" => array(
                "EMAIL_TO" => $email,
                "STATUS" => $status,
                "ORDER_ID" => $order_id,
                "LANG" => LANGUAGE_ID
            ),
            "MESSAGE_ID" => Settings::mailIdForClientNotification()
                        ), self::$_commonMailParameters));
    }

    /**
     * Отправка почтового уведомления агенту при создании заказа
     * @param int $order_id
     * @param string $email
     * @param string $status
     */
    public static function sendNewOrderNotificationForAgent(int $order_id, string $email, string $status) {

        \Bitrix\Main\Mail\Event::send(array_merge(array(
            "C_FIELDS" => array(
                "EMAIL_TO" => $email,
                "STATUS" => $status,
                "ORDER_ID" => $order_id,
                "LANG" => LANGUAGE_ID
            ),
            "MESSAGE_ID" => Settings::mailIdForAgentNotification()
                        ), self::$_commonMailParameters));
    }

    /**
     * Отправка почтового уведомления менеджеру при создании заказа
     * @param int $order_id
     */
    public static function sendNewOrderNotificationForManager(int $order_id) {

        \Bitrix\Main\Mail\Event::send(array_merge(array(
            "C_FIELDS" => array(
                "EMAIL_TO" => Settings::managerEmailForNotification(),
                "ORDER_ID" => $order_id,
                "LANG" => LANGUAGE_ID
            ),
            "MESSAGE_ID" => Settings::mailIdForManagerNotification()
                        ), self::$_commonMailParameters));
    }

}
