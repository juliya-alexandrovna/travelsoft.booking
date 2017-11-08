<?php

namespace travelsoft\booking\doccreators;

/**
 * Фабрика для договора по автобусному туру
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class BusContractFactory {

    /**
     * @param array $order
     * @param string $path
     * @param string $outputFormat
     */
    public function create(array $order, string $path, string $outputFormat = 'docx') {

        $template = \travelsoft\booking\doccreators\TemplateProccessorFactory::create($_SERVER['DOCUMENT_ROOT'] . $path);

        // Номер договора
        $template->setValue("NUMBER", $order['ID']);
        // Дата создания документа
        $template->setValue("DATECREATE", date('d.m.Y'));
        // Дата начала услуги
        $template->setValue("DATEFROM", $order['UF_DATE_FROM']->toString());
        // Дата окончания услуги
        $template->setValue("DATETO", $order['UF_DATE_TO']->toString());

        $client = current(\travelsoft\booking\stores\Users::get(array('filter' => array('ID' => $order['UF_USER_ID']),
                    'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'PERSONAL_PHONE', 'UF_PASS_NUMBER',
                        'UF_PASS_SERIES', 'PERSONAL_BIRTHDAY'))));

        if (strlen($client['FULL_NAME']) > 0) {
            // Полное имя клиента
            $template->setValue("CLIENTNAME", $client['FULL_NAME']);
        }
        if (strlen($client['PERSONAL_BIRTHDAY']) > 0) {
            // Дата рождения клиента
            $template->setValue("BIRTHDATE", $client['PERSONAL_BIRTHDAY']);
        }

        $client['UF_PASS_SERIES'] = trim($client['UF_PASS_SERIES']);
        $client['UF_PASS_NUMBER'] = trim($client['UF_PASS_NUMBER']);
        if (strlen($client['UF_PASS_SERIES']) > 0 && strlen($client['UF_PASS_NUMBER']) > 0) {
            // Серия и номер паспорта
            $template->setValue("PASSPORT", $client['UF_PASS_SERIES'] . $client['UF_PASS_NUMBER']);
        }

        if (strlen($client['PERSONAL_PHONE']) > 0) {
            // Номер телефона
            $template->setValue("PHONE", $client['PERSONAL_PHONE']);
        }

        if (strlen($client['UF_PASS_ADDRESS']) > 0) {
            // Адрес
            $template->setValue('ADDRESS', (string) $client['UF_PASS_ADDRESS']);
        }

        $lastPayment = \travelsoft\booking\stores\PaymentHistory::getLastPaymentByOrderId($order['ID']);
        if ($lastPayment['UF_DATE_CREATE']) {
            $payDate = date('d.m.Y', $lastPayment['UF_DATE_CREATE']->getTimestamp());
            $payDateTo = date('d.m.Y', $lastPayment['UF_DATE_CREATE']->getTimestamp() + (3 * 86400));
            // Дата последней оплаты
            $template->setValue("PAYDATE", $payDate);
            // Дата окончательной оплаты
            $template->setValue("PAYDATETO", $payDateTo);
        }

        //  Количество туристов
        $template->setValue("TOURISTSNUMBER", count($order['UF_TOURISTS_ID']));

        $converter = new \travelsoft\booking\adapters\CurrencyConverter();

        if ($order['CURRENT_TS_COST']) {
            $amount = $converter->convert($order['CURRENT_TS_COST'], $order['CURRENT_TS_COST_CURRENCY'], "BYN");
            $tcost = $converter->format($amount);
            $vat = $tcost = $converter->format($tcost * 0.2);
            // Общая стоимость туруслуги
            $template->setValue("TSCOST", $tcost);
            // НДС туруслуги
            $template->setValue("VAT", $vat);
        }

        if ($order['CURRENT_COST']) {
            $amount = $converter->convert($order['CURRENT_COST'], $order['CURRENT_COST_CURRENCY'], "BYN");
            $byMan = $amount / ($order['UF_ADULTS'] + $order['UF_CHILDREN']);
            $wholePart = intVal($byMan);
            $coins = intVal(($byMan - $wholePart) * 100);
            // Целая часть от стоимости на человека
            $template->setValue("WHOLEPART", $wholePart);
            // Количество копеек от стомости за человека
            $template->setValue("COINS", $coins);
        }

        if (!empty($order['UF_TOURISTS_ID'])) {
            $tourists = \travelsoft\booking\stores\Tourists::get(array('filter' => array('ID' => $order['UF_TOURISTS_ID'])));
            $touristsTxt = '';
            foreach ($tourists as $tourist) {
                $touristsTxt .= $tourist['FULL_NAME'] . "\r";
                $touristsTxt .= $tourist['FULL_NAME_LAT'] . "\r";
                $touristsTxt .= "Дата рождения: " . $tourist['UF_BIRTHDATE'] . "\r";
                $touristsTxt .= "Паспорт: " . $tourist['SN_PASSPORT'] . "\r";
                $touristsTxt .= "Адрес: " . $tourist['ADDRESS'] . "\r";
                $touristsTxt .= "Тел.: " . trim($tourist['UF_MOB_PHONE']) . "\r\r";
            }

            If (strlen($touristsTxt) > 0) {
                // Информация по туристам
                $template->setValue("TOURISTS", $touristsTxt);
            }
        }

        // Название тура
        $template->setValue("TOURNAME", str_replace("&", "", $order['UF_SERVICE_NAME']));
        if (strlen($order['UF_ROUTE']) > 0) {
            // Маршрут
            $template->setValue("ROUTE", $order['UF_ROUTE']);
        }
        if (strlen($order['UF_DEP_EXT_TXT'])) {
            // Время и место отправления
            $template->setValue("TIMEANDPLACE", $order['UF_DEP_EXT_TXT']);
        }
        if (strlen($order['UF_INCOST'])) {
            // В стоимость тура входит
            $template->setValue("INCOST", $order['UF_INCOST']);
        }
        if (strlen($order['UF_OUTCOST'])) {
            // В стоимость тура не входит
            $template->setValue("OUTCOST", $order['UF_OUTCOST']);
        }

        $template->stream('contract_' . $order['ID'] . '.' . $outputFormat);
    }

}
