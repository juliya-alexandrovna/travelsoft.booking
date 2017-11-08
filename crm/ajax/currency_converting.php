<?php

require_once 'header.php';

$converter = new \travelsoft\booking\adapters\CurrencyConverter;

if (
        $_REQUEST['price'] > 0 &&
        in_array($_REQUEST['currency_in'], $converter->getListOfCurrency()) &&
        in_array($_REQUEST['currency_out'], $converter->getListOfCurrency())
) {

    try {

        $arResponse['result'] = str_replace(' ', '', $converter->format($converter->convert($_REQUEST['price'], $_REQUEST['currency_in'], $_REQUEST['currency_out'])));
    } catch (Exception $e) {

        $message = $e->getMessage();
        if ($message != '') {

            $arResponse['error'] = 'Произошла ошибка при попытке рассчитать стоимость. '
                    . 'Попробуйте изменить параметры для рассчета цен.';
        }
    }
}

echo \Bitrix\Main\Web\Json::encode($arResponse);
