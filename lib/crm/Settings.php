<?php

namespace travelsoft\booking\crm;

/**
 * Класс статических настроек CRM
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Settings {
        
    /**
     * ID таблицы касс
     */
    const CASH_DESKS_HTML_TABLE_ID = 'CASH_DESKS_TABLE';
    
    /**
     * ID таблицы заказов
     */
    const ORDERS_HTML_TABLE_ID = 'ORDERS_TABLE';
    
    /**
     * ID таблицы типов оплаты
     */
    const PAYMENTS_TYPES_HTML_TABLE_ID = 'PAYMENTS_TYPES_TABLE';
    
    /**
     * ID таблицы истории оплаты
     */
    const PAYMENT_HISTORY_HTML_TABLE_ID = 'PAYMENT_HISTORY_TABLE';
    
    /**
     * Url страницы добавления цен
     */
    const ADD_PRICES_URL = 'travelsoft_crm_booking_add_prices.php';
    
    /**
     * Url стрницы списка заказов
     */
    const ORDERS_LIST_URL = 'travelsoft_crm_booking_orders_list.php';
    
    /**
     * Url стрницы редактирования заказа
     */
    const ORDER_EDIT_URL = 'travelsoft_crm_booking_order_edit.php';
    
    /**
     * Url страницы списка клиентов
     */
    const CLIENTS_LIST_URL = 'travelsoft_crm_booking_clients_list.php';
    
    /**
     * Url страницы редактирования клиента
     */
    const CLIENT_EDIT_URL = 'travelsoft_crm_booking_client_edit.php';
    
    /**
     * Url страницы списка туристов
     */
    const TOURISTS_LIST_URL = 'travelsoft_crm_booking_tourists_list.php';
    
     /**
     * Url страницы редактирования туриста
     */
    const TOURIST_EDIT_URL = 'travelsoft_crm_booking_tourist_edit.php';
    
    /**
     * Url страницы документов
     */
    const DOCUMENTS_URL = 'travelsoft_crm_booking_documents.php';
    
    /**
     * Url страницы редактирования документа
     */
    const DOCUMENT_EDIT_URL = 'travelsoft_crm_booking_document_edit.php';
    
    /**
     * Url страницы списка касс
     */
    const CASH_DESKS_LIST_URL = 'travelsoft_crm_booking_cash_desks_list.php';
    
    /**
     * Url страницы добавления/редактирования касс
     */
    const CASH_DESK_EDIT_URL = 'travelsoft_crm_booking_cash_desk_edit.php';
    
    /**
     * Url страницы списка типов платежей
     */
    const PAYMENTS_TYPES_LIST_URL = 'travelsoft_crm_booking_payments_types_list.php';
    
    /**
     * Url страницы списка типов платежей
     */
    const PAYMENT_TYPE_EDIT_URL = 'travelsoft_crm_booking_payment_type_edit.php';
    
    /**
     * Url страницы списка истории платежей
     */
    const PAYMENT_HISTORY_LIST_URL = 'travelsoft_crm_booking_payment_history_list.php';
    
    /**
     * Url страницы добавления/редактирования истории платежей
     */
    const PAYMENT_HISTORY_EDIT_URL = 'travelsoft_crm_booking_payment_history_edit.php';
}
