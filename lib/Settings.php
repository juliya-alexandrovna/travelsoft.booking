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

    /**
     * Возвращает ID свойства "даты заездов" у тура
     */
    public static function tourDatePropertyId () : int {
       
        return 78;
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
