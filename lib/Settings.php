<?php

namespace travelsoft\booking;

use Bitrix\Main\Config\Option;

/**
 * Класс настроек модуля бронирования
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Settings {
    
    public static function managerEmailForNotification () : string {
        
        $email = (string) self::get('MANAGER_EMAIL_FOR_NOTIFICATION');
        
        $arPartsEmail = explode('@', $email);
        
        if (!strlen($email) || !strlen($arPartsEmail[1])) {
            
            $email = (string) Option::get('main', 'email_from');
        }
        
        return $email;
    }
    
    public static function mailIdForClientNotification() : int {
        
        return (int)self::get('MAIL_ID_FOR_CLIENT_NOTIFICATION');
    }
    
    public static function mailIdForAdminNotification() : int {
        
        return (int)self::get('MAIL_ID_FOR_ADMIN_NOTIFICATION');
    }
    
    public static function mailIdForAgentNotification() : int {
        
        return (int)self::get('MAIL_ID_FOR_AGENT_NOTIFICATION');
    }
    
    public static function mailIdForManagerNotification() : int {
        
        return (int)self::get('MAIL_ID_FOR_MANAGER_NOTIFICATION');
    }
    
    /**
     * Возвращает id статуса заказа при его создании
     * @return int
     */
    public static function defStatus(): int {

        return (int) self::get("STATUS_ID_FOR_ORDER_CREATION");
    }
    
    /**
     * Возвращает id таблицы туров
     * @return int
     */
    public static function toursStoreId(): int {

        return (int) self::get("TOURS_IB");
    }

    /**
     * Возвращает id таблицы типов питания
     * @return int
     */
    public static function foodStoreId(): int {

        return (int) self::get("FOOD_IB");
    }
    
    /**
     * Возвращает id таблицы продолжительности услуги
     * @return int
     */
    public static function durationStoreId(): int {

        return (int) self::get("DURATION_HL");
    }
    
    /**
     * Возвращает id таблицы питания
     * @return int
     */
    public static function citizenshipStoreId(): int {

        return (int) self::get("CITIZENSHIP_HL");
    }

    /**
     * Возвращает id таблицы заказов
     * @return int
     */
    public static function ordersStoreId(): int {

        return (int) self::get("ORDERS_HL");
    }

    /**
     * Возвращает id таблицы типов цен
     * @return int
     */
    public static function priceTypesStoreId(): int {

        return (int) self::get("PRICE_TYPES_HL");
    }

    /**
     * Возвращает id таблицы цен
     * @return int
     */
    public static function pricesStoreId(): int {

        return (int) self::get("PRICES_HL");
    }

    /**
     * Возвращает id таблицы квот
     * @return int
     */
    public static function quotasStoreId(): int {

        return (int) self::get("QUOTAS_HL");
    }

    /**
     * Возвращает id таблицы статусов
     * @return int
     */
    public static function statusesStoreId(): int {

        return (int) self::get("STATUSES_HL");
    }

    /**
     * Возвращает id таблицы настроек CRM
     * @return int
     */
    public static function crmsettingsStoreId(): int {

        return (int) self::get("CRMSETTINGS_HL");
    }

    /**
     * Возвращает id таблицы туристов
     * @return int
     */
    public static function touristsStoreId(): int {

        return (int) self::get("TOURISTS_HL");
    }

    /**
     * Возвращает id группы пользователей для агентов
     * @return int
     */
    public static function agentsUGroup(): int {

        return (int) self::get("AGENTS_USER_GROUPS");
    }

    /**
     * Возвращает id группы пользователей для менеджеров
     * @return int
     */
    public static function managersUGroup(): int {

        return (int) self::get("MANAGERS_USER_GROUPS");
    }

    /**
     * @param string $name
     * @return string
     */
    protected static function get(string $name): string {

        return (string) Option::get("travelsoft.booking", $name);
    }

}
