<?php

namespace travelsoft\booking\adapters;

/**
 * Класс для работы с датами
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Date {
    
    /**
     * Возвращает объект даты
     * @param string $date
     * @return \Bitrix\Main\Type\DateTime
     */
    public static function create (string $date) {
        
        return new \Bitrix\Main\Type\DateTime((new \DateTime($date))->format('d.m.Y'));
    } 
}
