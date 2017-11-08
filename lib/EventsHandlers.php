<?php

namespace travelsoft\booking;

use travelsoft\booking\crm\Settings as CRMSettings;
use travelsoft\booking\Utils;

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
            
            $currentUserGroups = $USER->GetUserGroupArray();
            
            $arAllMenuItems = array(
                    CRMSettings::ADD_PRICES_URL => array(
                        "text" => "Цены и наличие мест",
                        "url" => CRMSettings::ADD_PRICES_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(),
                        "title" => "Цены и наличие мест",
                    ),
                    CRMSettings::ORDERS_LIST_URL => array(
                        "text" => "Список заказов",
                        "url" => CRMSettings::ORDERS_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(CRMSettings::ORDER_EDIT_URL),
                        "title" => "Список заказов",
                    ),
                    CRMSettings::CLIENTS_LIST_URL => array(
                        "text" => "Клиенты",
                        "url" => CRMSettings::CLIENTS_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(CRMSettings::CLIENT_EDIT_URL),
                        "title" => "Клиенты",
                    ),
                    CRMSettings::TOURISTS_LIST_URL => array(
                        "text" => "Туристы",
                        "url" => CRMSettings::TOURISTS_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(CRMSettings::TOURIST_EDIT_URL),
                        "title" => "Туристы",
                    ),
                    CRMSettings::PAYMENT_HISTORY_LIST_URL => array(
                        "text" => "История платежей",
                        "url" => CRMSettings::PAYMENT_HISTORY_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(CRMSettings::PAYMENT_HISTORY_EDIT_URL),
                        "title" => "История платежей",
                    ),
                    CRMSettings::CASH_DESKS_LIST_URL => array(
                        "text" => "Кассы",
                        "url" => CRMSettings::CASH_DESKS_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(CRMSettings::CASH_DESK_EDIT_URL),
                        "title" => "Кассы",
                    ),
                    CRMSettings::PAYMENTS_TYPES_LIST_URL => array(
                        "text" => "Типы оплаты",
                        "url" => CRMSettings::PAYMENTS_TYPES_LIST_URL . "?lang=" . LANGUAGE_ID,
                        "more_url" => array(CRMSettings::PAYMENT_TYPE_EDIT_URL),
                        "title" => "Типы оплаты",
                    )
                );
            
            $arMenuItems = array();
            
            if ($USER->IsAdmin()) {
                
                $arMenuItems = $arAllMenuItems;
                $arMenuItems[CRMSettings::DOCUMENTS_URL] = array(
                    "text" => "Документы",
                    "url" => CRMSettings::DOCUMENTS_URL . "?lang=" . LANGUAGE_ID,
                    "more_url" => array(CRMSettings::DOCUMENT_EDIT_URL),
                    "title" => "Документы",
                );
                
            } elseif (in_array(Settings::cashersUGroup(), $currentUserGroups)) {
                
                $arMenuItems = array(
                    CRMSettings::ORDERS_LIST_URL => $arAllMenuItems[CRMSettings::ORDERS_LIST_URL],
                    CRMSettings::DOCUMENTS_URL => $arAllMenuItems[CRMSettings::DOCUMENTS_URL],
                    CRMSettings::PAYMENT_HISTORY_LIST_URL => $arAllMenuItems[CRMSettings::PAYMENT_HISTORY_LIST_URL],
                    CRMSettings::CASH_DESKS_LIST_URL => $arAllMenuItems[CRMSettings::CASH_DESKS_LIST_URL],
                    CRMSettings::PAYMENTS_TYPES_LIST_URL => $arAllMenuItems[CRMSettings::PAYMENTS_TYPES_LIST_URL]
                );
            }
            
            if (in_array(Settings::managersUGroup(), $currentUserGroups)) {
                
                $arMenuItems = array(
                    CRMSettings::ADD_PRICES_URL => $arAllMenuItems[CRMSettings::ADD_PRICES_URL],
                    CRMSettings::ORDERS_LIST_URL => $arAllMenuItems[CRMSettings::ORDERS_LIST_URL],
                    CRMSettings::CLIENTS_LIST_URL => $arAllMenuItems[CRMSettings::CLIENTS_LIST_URL],
                    CRMSettings::TOURISTS_LIST_URL => $arAllMenuItems[CRMSettings::TOURISTS_LIST_URL],
                    CRMSettings::DOCUMENTS_URL => $arAllMenuItems[CRMSettings::DOCUMENTS_URL]
                );
            }
            
            $arGlobalMenu["global_menu_travelsoft_crm"] = array(
                "menu_id" => "travelsoft_booking_crm",
                "text" => "CRM",
                "title" => "CRM",
                "sort" => 500,
                "items_id" => "global_menu_travelsoft_booking_crm",
                "help_section" => "travelsoft_booking_crm",
                "items" => array_values($arMenuItems)
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
                    (int) $arOrder['UF_SERVICE_ID'], $dateFrom, (int) $arOrder['UF_ADULTS'], (int) $arOrder['UF_CHILDREN']
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
    
    /**
     * @param type $arFields
     */
    public static function onBeforeUserRegister(&$arFields) {

        $arFields['LOGIN'] = $arFields['EMAIL'];
    }

    /**
     * @param type $arFields
     */
    public static function onAfterUserRegister(&$arFields) {

        if ($arFields['USER_ID'] > 0) {

            if ($_POST['IS_AGENT'] == 'Y') {

                // ОТПРАВКА ПИСЬМА АДМИНУ САЙТА О РЕГИСТРАЦИИ НОВОГО АГЕНТА
                \Bitrix\Main\Mail\Event::send(array(
                    "EVENT_NAME" => "TRAVELSOFT_BOOKING",
                    "LID" => $arFields['LID'],
                    "C_FIELDS" => array(
                        "USER_ID" => $arFields['USER_ID']
                    ),
                    "DUPLICATE" => 'N',
                    "MESSAGE_ID" => \Bitrix\Main\Config\Option::get("travelsoft.booking", "MAIL_ID_FOR_ADMIN_NOTIFICATION")
                ));
            }
        }
    }

    /**
     * @param array $arFields
     */
    public static function onBeforeUserUpdate(&$arFields) {

        $arFields['LOGIN'] = $arFields['EMAIL'];
    }
}
