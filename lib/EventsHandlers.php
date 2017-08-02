<?php

namespace travelsoft\booking;

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
                        "url" => "travelsoft_crm_booking_add_prices.php?lang=" . LANGUAGE_ID,
                        "more_url" => array(),
                        "title" => "Цены и наличие мест",
                    ),
                    array(
                        "text" => "Список заказов",
                        "url" => "travelsoft_crm_booking_orders_list.php?lang=" . LANGUAGE_ID,
                        "more_url" => array(),
                        "title" => "Список заказов",
                    ),
                    array(
                        "text" => "Клиенты",
                        "url" => "travelsoft_crm_booking_clients.php?lang=" . LANGUAGE_ID,
                        "more_url" => array(),
                        "title" => "Клиенты",
                    ),
                    array(
                        "text" => "Агенты",
                        "url" => "travelsoft_crm_booking_agents.php?lang=" . LANGUAGE_ID,
                        "more_url" => array(),
                        "title" => "Агенты",
                    ),
                    array(
                        "text" => "Документы",
                        "url" => "travelsoft_crm_booking_documents.php?lang=" . LANGUAGE_ID,
                        "more_url" => array(),
                        "title" => "Документы",
                    )
                )
            );
        }
    }

}
