<?php

namespace travelsoft\booking;

use travelsoft\booking\crm\Settings;

/**
 * Класс методов обработки событий
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class EventsHandlers {

    /**
     * Добавляет пункт меню CRM в global меню админ. части
     * @global type $USER
     * @param type $aGlobalMenu
     */
    public static function addGlobalAdminMenuItem(&$arGlobalMenu) {

        global $USER;

        if (crm\Utils::access()) {

            $arGlobalMenu["global_menu_travelsoft_crm"] = array(
                "menu_id" => "travelsoft_booking_crm",
                "text" => "CRM",
                "title" => "CRM",
                "sort" => 500,
                "items_id" => "global_menu_travelsoft_booking_crm",
                "help_section" => "travelsoft_booking_crm",
                "items" => array(
                    array(
                        "text" => "Цены и наличие мест",
                        "url" => Settings::ADD_PRICES_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(),
                        "title" => "Цены и наличие мест",
                    ),
                    array(
                        "text" => "Список заказов",
                        "url" => Settings::ORDERS_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(Settings::ORDER_EDIT_URL),
                        "title" => "Список заказов",
                    ),
                    array(
                        "text" => "Клиенты",
                        "url" => Settings::CLIENTS_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(Settings::CLIENT_EDIT_URL),
                        "title" => "Клиенты",
                    ),
                    array(
                        "text" => "Туристы",
                        "url" => Settings::TOURISTS_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(Settings::TOURIST_EDIT_URL),
                        "title" => "Туристы",
                    ),
                    array(
                        "text" => "Документы",
                        "url" => Settings::DOCUMENTS_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(),
                        "title" => "Документы",
                    ),
                    array(
                        "text" => "История платежей",
                        "url" => Settings::PAYMENT_HISTORY_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(Settings::PAYMENT_HISTORY_EDIT_URL),
                        "title" => "История платежей",
                    ),
                    array(
                        "text" => "Кассы",
                        "url" => Settings::CASH_DESKS_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(Settings::CASH_DESK_EDIT_URL),
                        "title" => "Кассы",
                    ),
                    array(
                        "text" => "Типы оплаты",
                        "url" => Settings::PAYMENTS_TYPES_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(Settings::PAYMENT_TYPE_EDIT_URL),
                        "title" => "Типы оплаты",
                    )
                )
            );
        }
    }

    /**
     * @param Bitrix\Main\Entity\Event $event
     */
    public static function onBeforeOrderUpdate($event) {

        $arParameters = $event->getParameters();

        $arOrder = \travelsoft\booking\stores\Orders::getById((int) $arParameters['id']['ID']);
        if (
                $arParameters['fields']['UF_STATUS_ID'] == \travelsoft\booking\Settings::cancellationStatus() &&
                $arOrder['UF_STATUS_ID'] != $arParameters['fields']['UF_STATUS_ID']
        ) {
            
            // ФЛАГ НА АННУЛЯЦИЮ
            $GLOBALS['__TRAVELSOFT']['NEED_CANCELLATION'] = true;
        }
    }

    /**
     * @param Bitrix\Main\Entity\Event $event
     */
    public static function onAfterOrderUpdate($event) {

        $arParameters = $event->getParameters();
        $arOrder = stores\Orders::getById($arParameters['id']['ID']);
        if ($GLOBALS['__TRAVELSOFT']['NEED_CANCELLATION'] && $arOrder['UF_SERVICE_ID'] > 0) {

            // АННУЛИРОВАНИЕ
            $dateFrom = $arOrder['UF_DATE_FROM'] ? $arOrder['UF_DATE_FROM']->toString() : '';
            
            Utils::bookingTourCancellation(
                    (int)$arOrder['UF_SERVICE_ID'], $dateFrom, (int) $arOrder['UF_ADULTS'], (int) $arOrder['UF_CHILDREN']
            );
        }
        unset($GLOBALS['__TRAVELSOFT']['NEED_CANCELLATION']);
    }

    /**
     * @param Bitrix\Main\Entity\Event $event
     */
    public static function onBeforeOrderDelete($event) {

        $arParameters = $event->getParameters();
        $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE'] = \travelsoft\booking\stores\Orders::getById((int) $arParameters['id']['ID']);
    }

    public static function onAfterOrderDelete() {

        if (
                $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']['ID'] > 0 &&
                $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']['UF_SERVICE_ID'] > 0 &&
                $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']['UF_STATUS_ID'] != \travelsoft\booking\Settings::cancellationStatus()
        ) {
            
            // АННУЛИРОВАНИЕ
            $dateFrom = $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']['UF_DATE_FROM'] ? $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']['UF_DATE_FROM']->toString() : '';
            Utils::bookingTourCancellation(
                    (int) $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']['UF_SERVICE_ID'], $dateFrom, (int) $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']['UF_ADULTS'], (int) $GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']['UF_CHILDREN']);

        }
        unset($GLOBALS['__TRAVELSOFT']['ORDERS_FIELDS_BEFORE_DELETE']);
    }

}
