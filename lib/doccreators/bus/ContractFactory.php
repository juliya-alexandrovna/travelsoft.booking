<?php

namespace travelsoft\booking\doccreators\bus;

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
    public function create(array $order, string $path) {

        $repository = new \travelsoft\booking\doccreators\Repository();

        $repository->path = $_SERVER['DOCUMENT_ROOT'] . $path;

        // Номер договора
        $repository->setLabel("NUMBER", $order['ID']);
        // Дата создания документа
        $repository->setLabel("DATECREATE", date('d.m.Y'));
        // Дата начала услуги
        $repository->setLabel("DATEFROM", $order['UF_DATE_FROM']->toString());
        // Дата окончания услуги
        $repository->setLabel("DATETO", $order['UF_DATE_TO']->toString());

        $client = current(\travelsoft\booking\stores\Users::get(array('filter' => array('ID' => $order['UF_USER_ID']),
                    'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'PERSONAL_PHONE', 'UF_PASS_NUMBER',
                        'UF_PASS_SERIES', 'PERSONAL_BIRTHDAY'))));

        if (strlen($client['FULL_NAME']) > 0) {
            // Полное имя клиента
            $repository->setLabel("CLIENTNAME", $client['FULL_NAME']);
        }
        if (strlen($client['PERSONAL_BIRTHDAY']) > 0) {
            // Дата рождения клиента
            $repository->setLabel("BIRTHDATE", $client['PERSONAL_BIRTHDAY']);
        }
        
        $client['UF_PASS_SERIES'] = trim($client['UF_PASS_SERIES']);
        $client['UF_PASS_NUMBER'] = trim($client['UF_PASS_NUMBER']);
        if (strlen($client['UF_PASS_SERIES']) > 0 && strlen($client['UF_PASS_NUMBER']) > 0) {
            // Серия и номер паспорта
            $repository->setLabel("PASSPORT", $client['UF_PASS_SERIES'] . $client['UF_PASS_NUMBER']);
        }

        if (strlen($client['PERSONAL_PHONE']) > 0) {
            // Номер телефона
            $repository->setLabel("PHONE", $client['PERSONAL_PHONE']);
        }

        if (strlen($client['UF_PASS_ADDRESS']) > 0) {
            // Адрес
            $repository->setLabel('ADDRESS', (string) $client['UF_PASS_ADDRESS']);
        }

        $lastPayment = \travelsoft\booking\stores\PaymentHistory::getLastPaymentByOrderId($order['ID']);
        if ($lastPayment['UF_DATE_CREATE']) {
            $payDate = date('d.m.Y', $lastPayment['UF_DATE_CREATE']->getTimestamp());
            $payDateTo = date('d.m.Y', $lastPayment['UF_DATE_CREATE']->getTimestamp() + (3 * 86400));
            // Дата последней оплаты
            $repository->setLabel("PAYDATE", $payDate);
            // Дата окончательной оплаты
            $repository->setLabel("PAYDATETO", $payDateTo);
        }

        //  Количество туристов
        $repository->setLabel("TOURISTSNUMBER", count($order['UF_TOURISTS_ID']));

        $converter = new \travelsoft\booking\adapters\CurrencyConverter();

        if ($order['CURRENT_TS_COST']) {
            $amount = $converter->convert($order['CURRENT_TS_COST'], $order['CURRENT_TS_COST_CURRENCY'], "BYN");
            $tcost = $converter->format($amount);
            $vat = $tcost = $converter->format($tcost * 0.2);
            // Общая стоимость туруслуги
            $repository->setLabel("TSCOST", $tcost);
            // НДС туруслуги
            $repository->setLabel("VAT", $vat);
        }

        if ($order['CURRENT_COST']) {
            $amount = $converter->convert($order['CURRENT_COST'], $order['CURRENT_COST_CURRENCY'], "BYN");
            $byMan = $amount / ($order['UF_ADULTS'] + $order['UF_CHILDREN']);
            $wholePart = intVal($byMan);
            $coins = intVal(($byMan - $wholePart) * 100);
            // Целая часть от стоимости на человека
            $repository->setLabel("WHOLEPART", $wholePart);
            // Количество копеек от стомости за человека
            $repository->setLabel("COINS", $coins);
        }

        // Информация по туристам
        $repository->setLabel("TOURISTS", "");
        // Название тура
        $repository->setLabel("TOURNAME", str_replace("&", "", $order['UF_SERVICE_NAME']));
        if (strlen($order['UF_ROUTE']) > 0) {
            // Маршрут
            $repository->setLabel("ROUTE", $order['UF_ROUTE']);
        }
        if (strlen($order['UF_DEP_EXT_TXT'])) {
            // Время и место отправления
            $repository->setLabel("TIMEANDPLACE", $order['UF_DEP_EXT_TXT']);
        }
        if (strlen($order['UF_INCOST'])) {
            // В стоимость тура входит
            $repository->setLabel("INCOST", $order['UF_INCOST']);
        }
        if (strlen($order['UF_OUTCOST'])) {
            // В стоимость тура не входит
            $repository->setLabel("OUTCOST", $order['UF_OUTCOST']);
        }
        
        (new \travelsoft\booking\doccreators\TemplateProcessor($repository))->saveAs($_SERVER['DOCUMENT_ROOT'] . '/upload/docs/contract_'.$order['ID'].'.docx');
    }

}
