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

                    self::preparedOrderFieldsForView($arOrder);
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

        self::preparedOrderFieldsForView($arOrder);

        return $arOrder;
    }

    /**
     * Возвращает максимальный id списка заказов
     * @return int
     */
    public static function getLastId() {
        $result = parent::get(array('select' => array(new \Bitrix\Main\Entity\ExpressionField('MAX_ID', 'max(ID)'))), false)->fetch();
        return intVal($result['MAX_ID']);
    }

    /**
     * Подготовка полей заказа для отображения
     * @param type $arOrder
     */
    public static function preparedOrderFieldsForView(&$arOrder) {

        $converter = new CurrencyConverter;

        if ($arOrder['UF_SERVICE_ID'] > 0) {

            $cost = new \travelsoft\booking\tours\Cost(array(
                $arOrder['UF_SERVICE_ID'] => array(
                    strtotime($arOrder['UF_DATE_FROM']) => array(
                        'prices' => array(
                            'adult' => array(
                                'price' => $arOrder['UF_ADULT_PRICE'],
                                'currency' => $arOrder['UF_ADULT_PRICE_CRNC']
                            ),
                            'children' => array(
                                'price' => $arOrder['UF_CHILDREN_PRICE'],
                                'currency' => $arOrder['UF_CHILD_PRICE_CRNC'],
                            ),
                            'adult_tour_service' => array(
                                'price' => $arOrder['UF_ADULTTS_PRICE'],
                                'currency' => $arOrder['UF_ADTS_PRICE_CRNC']
                            ),
                            'children_tour_service' => array(
                                'price' => $arOrder['UF_CHILDTS_PRICE'],
                                'currency' => $arOrder['UF_CHTS_PRICE_CRNC']
                            )
                        )
                    )
                )
            ));

            $arTotalCost = current($cost->forAdultTourService((int) $arOrder['UF_ADULTS'])
                            ->forAdults((int) $arOrder['UF_ADULTS'])
                            ->forChildren((int) $arOrder['UF_CHILDREN'])
                            ->forChildrenTourService((int) $arOrder['UF_CHILDREN'])
                            ->getMinForTour());

            if (strlen($arOrder['UF_ADULT_PRICE_CRNC']) > 0) {
                $arOrder['CURRENT_TOTAL_COST_CURRENCY'] = $arOrder['UF_ADULT_PRICE_CRNC'];
            } elseif (strlen($arOrder['UF_CHILD_PRICE_CRNC']) > 0) {
                $arOrder['CURRENT_TOTAL_COST_CURRENCY'] = $arOrder['UF_CHILD_PRICE_CRNC'];
            } else {
                $arOrder['CURRENT_TOTAL_COST_CURRENCY'] = $arOrder['UF_CURRENCY'];
            }

            if ($arTotalCost['price'] > 0.01) {

                $arOrder['CURRENT_TOTAL_COST'] = $converter->convert(
                        $arTotalCost['price'], $arTotalCost['currency'], $arOrder['CURRENT_TOTAL_COST_CURRENCY']);

                $arCurrentCost = current($cost->forAdults((int) $arOrder['UF_ADULTS'])->forChildren((int) $arOrder['UF_CHILDREN'])->getMinForTour());

                $arOrder['CURRENT_COST'] = $converter->convert(
                        $arCurrentCost['price'], $arCurrentCost['currency'], $arOrder['CURRENT_TOTAL_COST_CURRENCY']);

                $arOrder['CURRENT_COST_CURRENCY'] = $arOrder['CURRENT_TOTAL_COST_CURRENCY'];

                $arCurrentTsCost = current($cost->forAdultTourService((int) $arOrder['UF_ADULTS'])->forChildrenTourService((int) $arOrder['UF_CHILDREN'])->getMinForTour());

                $arOrder['CURRENT_TS_COST'] = $arCurrentTsCost['price'];
                $arOrder['CURRENT_TS_COST_CURRENCY'] = $arCurrentTsCost['currency'];
            } else {

                $arOrder['CURRENT_TOTAL_COST'] = $arOrder['CURRENT_COST'] = (float) $arOrder['UF_COST'];
                $arOrder['CURRENT_COST_CURRENCY'] = $arOrder['CURRENT_TOTAL_COST_CURRENCY'];
            }
        } else {

            $arOrder['CURRENT_TOTAL_COST'] = $arOrder['CURRENT_COST'] = (float) $arOrder['UF_COST'];

            $arOrder['CURRENT_TOTAL_COST_CURRENCY'] = $arOrder['CURRENT_COST_CURRENCY'] = $arOrder['UF_CURRENCY'];

            if ($arOrder['UF_TS_COST'] > 0) {

                $arOrder['CURRENT_TS_COST'] = (float) $arOrder['UF_TS_COST'];
                $arOrder['CURRENT_TS_COST_CURRENCY'] = $arOrder['UF_TS_CURRENCY'];

                $arOrder['CURRENT_TOTAL_COST'] += $converter->convert(
                        (float) $arOrder['UF_TS_COST'], $arOrder['UF_TS_CURRENCY'], $arOrder['CURRENT_TOTAL_COST_CURRENCY']);
            }
        }

        $arOrder['PAID'] = self::_calculateTheAmountPaid(array(
                    'order_id' => $arOrder['ID'],
                    'currency' => $arOrder['CURRENT_TOTAL_COST_CURRENCY']
        ));

        $arOrder['FORMATTED_TS_PAID'] = 0.00;
        $arOrder['FORMATTED_TS_TO_PAY'] = 0.00;
        $arOrder['FORMATTED_COST_PAID'] = 0.00;
        $arOrder['FORMATTED_COST_TO_PAY'] = 0.00;

        if ($arOrder['PAID'] > 0) {
            $convetedPaid = $converter->convert($arOrder['PAID'], $arOrder['CURRENT_TOTAL_COST_CURRENCY'], $arOrder['CURRENT_TS_COST_CURRENCY']);
            $delta = $arOrder['CURRENT_TS_COST'] - $convetedPaid;

            if ($delta >= 0.01) {

                $arOrder['FORMATTED_TS_PAID'] = $converter->getFormatted($convetedPaid, $arOrder['CURRENT_TS_COST_CURRENCY']);
                $arOrder['FORMATTED_TS_TO_PAY'] = $converter->getFormatted($delta, $arOrder['CURRENT_TS_COST_CURRENCY']);
                $arOrder['FORMATTED_COST_PAID'] = $converter->getFormatted(0.00, $arOrder['CURRENT_COST_CURRENCY']);
                $arOrder['FORMATTED_COST_TO_PAY'] = $converter->getFormatted($arOrder['CURRENT_TS_COST'], $arOrder['CURRENT_COST_CURRENCY']);
            } else {

                $arOrder['FORMATTED_TS_PAID'] = $converter->getFormatted($arOrder['CURRENT_TS_COST'], $arOrder['CURRENT_TS_COST_CURRENCY']);
                $arOrder['FORMATTED_TS_TO_PAY'] = $converter->getFormatted(0.00, $arOrder['CURRENT_TS_COST_CURRENCY']);

                $convertedDelta = $converter->convert(abs($delta), $arOrder['CURRENT_TS_COST_CURRENCY'], $arOrder['CURRENT_COST_CURRENCY']);

                $arOrder['FORMATTED_COST_PAID'] = $converter->getFormatted($convertedDelta, $arOrder['CURRENT_COST_CURRENCY']);
                $arOrder['FORMATTED_COST_TO_PAY'] = $converter->getFormatted($arOrder['CURRENT_COST'] - $convertedDelta, $arOrder['CURRENT_COST_CURRENCY']);
            }

            $arOrder['FORMATTED_CURRENT_COST'] = $converter->getFormatted($arOrder['CURRENT_COST'], $arOrder['CURRENT_COST_CURRENCY']);
            $arOrder['FORMATTED_CURRENT_TS_COST'] = $converter->getFormatted($arOrder['CURRENT_TS_COST'], $arOrder['CURRENT_TS_COST_CURRENCY']);
        }

        $arOrder['TO_PAY'] = $arOrder['CURRENT_TOTAL_COST'] - $arOrder['PAID'];
        if ($arOrder['TO_PAY'] <= 0.01) {
            $arOrder['TO_PAY'] = 0.00;
        }

        $arOrder['FORMATTED_CURRENT_TOTAL_COST'] = $converter->format($arOrder['CURRENT_TOTAL_COST']);
        $arOrder['FORMATTED_TO_PAY'] = $converter->format($arOrder['TO_PAY']);
        $arOrder['FORMATTED_PAID'] = $converter->format($arOrder['PAID']);
    }

    /**
     * Производит расчет оплаченной суммы в валюте заказа
     * @param array $parameters
     * @return float
     */
    protected static function _calculateTheAmountPaid(array $parameters): float {

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

        return (float) $price;
    }

}
