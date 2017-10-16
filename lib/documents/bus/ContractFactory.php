<?php

namespace travelsoft\booking\documents\bus;

/**
 * Фабрика для договора по автобусному туру
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class ContractFactory {
    
    /**
     * @param array $order
     * @param string $path
     */
    public function create (array $order, string $path) : \travelsoft\booking\docs\Repository {
        
        $repository = new \travelsoft\booking\docs\Repository();
        $repository->path = $path;
        
        // Номер договора
        $repository->setLabel("#NUMBER#", $order['ID']);
        // Дата создания документа
        $repository->setLabel("#DATE_CREATE#", date('d.m.Y'));
        // Дата начала услуги
        $repository->setLabel("#DATE_FROM#", $order['DATE_FROM']->toString());
        // Дата окончания услуги
        $repository->setLabel("#DATE_TO#", $order['DATE_TO']->toString());
        
        $client = current(\travelsoft\booking\stores\Users::get(array('filter' => array('ID' => $order['UF_USER_ID']), 
            'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'PERSONAL_PHONE', 'UF_PASS_NUMBER',
                'UF_PASS_SERIES', 'PERSONAL_BIRTHDAY'))));
        
        // Полное имя клиента
        $repository->setLabel("#CLIENT_NAME#", $client['FULL_NAME']);
        // Дата рождения клиента
        $repository->setLabel("#BIRTHDATE#", $client['PERSONAL_BIRTHDAY']);
        
        $passport = '';
        $client['UF_PASS_SERIES'] = trim($client['UF_PASS_SERIES']);
        $client['UF_PASS_NUMBER'] = trim($client['UF_PASS_NUMBER']);
        if (strlen($client['UF_PASS_SERIES']) > 0 && strlen($client['UF_PASS_NUMBER']) > 0) {
            $passport = $client['UF_PASS_SERIES'] . $client['UF_PASS_NUMBER'];
        }
        // Серия и номер паспорта
        $repository->setLabel("#PASSPORT#", $passport);
        
        // Номер телефона
        $repository->setLabel("#PHONE#", $client['PERSONAL_PHONE']);
        // Адрес
        $repository->setLabel('#ADDRESS#', $client['UF_PASS_ADDRESS']);
        
        $lastPayment = \travelsoft\booking\stores\PaymentHistory::getLastPaymentByOrderId($order['ID']);
        $payDate = '';
        if ($lastPayment['UF_DATE_CREATE']) {
            $payDate = $lastPayment['UF_DATE_CREATE']->toString();
        }
        // Дата последней оплаты
        $repository->setLabel("#PAY_DATE#", $payDate);
        //  Количество туристов
        $repository->setLabel("#TOURISTS_NUMBER#", count($order['UF_TOURISTS_ID']));
        
        $converter = new \travelsoft\booking\adapters\CurrencyConverter();
        
        $tcost = '';
        $vat = '';
        if ($order['CURRENT_TS_COST']) {
            $arCost = $converter->convert($order['CURRENT_TS_COST'], $order['CURRENT_TS_COST_CURRENCY'], "BYN");
            $tcost = $converter->format($arCost['price']);
            $vat = $tcost = $converter->format($tcost*0.2);
        }
        // Общая стоимость туруслуги
        $repository->setLabel("#TS_COST#", $tcost);
        // НДС туруслуги
        $repository->setLabel("#VAT#", $vat);
        
        $wholePart = '';
        $coins = '';
        if ($order['CURRENCT_COST']) {
            $arCost = $converter->convert($order['CURRENT_COST'], $order['CURRENT_COST_CURRENCY'], "BYN");
            $byMan = $arCurrent("");
        }
    }
}
