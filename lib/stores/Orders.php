<?php

namespace travelsoft\booking\stores;

use travelsoft\booking\adapters\Highloadblock;
use travelsoft\booking\adapters\CurrencyConverter;

/**
 * Класс для работы с таблицей заказов
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class Orders extends Highloadblock {

    protected static $storeName = 'orders';

    /**
     * Возвращает полученные данные из таблицы заказов
     * и производит расчет оплаченной суммы для каждого заказа,
     * если параметр $likeArray равен true
     * 
     * @param array $query
     * @param callable $callback
     * @return mixed
     */
    public static function get(array $query = array(), bool $likeArray = true, callable $callback = null) {
        return parent::get($query, $likeArray, function (&$arOrder) use ($callback) {

                    if ($callback) {
                        $callback($arOrder);
                    }

                    $arOrder['PAID'] = self::calculateTheAmountPaid(array(
                                'order_id' => $arOrder['ID'],
                                'currency' => $arOrder['UF_CURRENCY']
                    ));


                    $arOrder['TO_PAY'] = (float) $arOrder['UF_COST'] - (float) $arOrder['PAID'];
                    if ($arOrder['TO_PAY'] <= 0.01) {
                        $arOrder['TO_PAY'] = 0.00;
                    }
                });
    }

    /**
     * Возвращает полученные данные из таблицы заказов по id
     * и производит расчет оплаченной суммы
     * 
     * @param int $id
     * @return array
     */
    public static function getById(int $id): array {

        $arOrder = parent::getById($id);

        $arOrder['PAID'] = self::calculateTheAmountPaid(array(
                    'order_id' => $arOrder['ID'],
                    'currency' => $arOrder['UF_CURRENCY']
        ));


        $arOrder['TO_PAY'] = (float) $arOrder['UF_COST'] - (float) $arOrder['PAID'];
        if ($arOrder['TO_PAY'] <= 0.01) {
            $arOrder['TO_PAY'] = 0.00;
        }

        return $arOrder;
    }

    /**
     * Возвращает максимальный id списка заказов
     * @return int
     */
    public static function getOrderLastId() {
        $result = parent::get(array('select' => array(new \Bitrix\Main\Entity\ExpressionField('MAX_ID', 'max(ID)'))), false)->fetch();
        return intVal($result['MAX_ID']);
    }
    
    /**
     * Производит расчет оплаченной суммы в валюте заказа
     * @param array $parameters
     * @return string
     */
    protected static function calculateTheAmountPaid(array $parameters): string {

        if (strlen($parameters['currency']) <= 0) {
            return "0";
        }

        $arPaymentHistories = PaymentHistory::get(array(
                    'filter' => array('UF_ORDER_ID' => $parameters['order_id'])
        ));

        foreach ($arPaymentHistories as $arPaymentHistory) {

            $arCourseInfo = \travelsoft\booking\Utils::sta($arPaymentHistory['UF_COURSE_INFO']);

            if ($arCourseInfo['COURSE_ID'] > 0) {

                $converter = new CurrencyConverter($arCourseInfo['COURSE_ID'], $arCourseInfo['COMMISSIONS']);

                $price += $converter->convert((float) $arPaymentHistory['UF_PRICE'], (string) $arPaymentHistory['UF_CURRENCY'], (string) $parameters['currency']);
            }
        }

        return (new CurrencyConverter)->format((float) $price);
    }

}
