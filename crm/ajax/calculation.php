<?php

require_once 'header.php';

$converter = new \travelsoft\booking\adapters\CurrencyConverter;

$quota = $_REQUEST['adults'] + $_REQUEST['children'];

if (
        $_REQUEST['id'] > 0 &&
        $_REQUEST['dateFrom'] != '' && 
        $quota > 0 &&
        in_array($_REQUEST['currency'], $converter->getListOfCurrency())
) {
    
    $ID = intVal($_REQUEST['id']);
    $children = intVal($_REQUEST['children']);
    $adults = intVal($_REQUEST['adults']);
    
    $searchEngine = new \travelsoft\booking\tours\SearchEngine;
    
    $searchEngine->setExtFilter(array(
        
        'ID' => $ID,
        'UF_DATE' => $_REQUEST['dateFrom']
    ));
    
    $arPrices = $searchEngine->search()
            ->filterByQuotas($quota)
            ->filterByStopSale()
            ->getCost()
            ->forId(array($ID))
            ->forAdults($adults)
            ->forChildren($children)
            ->forAdultTourService($adults)
            ->forChildrenTourService($children)
            ->getMinForTour();
    
    if (is_array($arPrices) && !empty($arPrices)) {
        
        $arResponse['result']['UF_DURATION'] = $arPrices[$ID]['duration'];
        
        try {
            
            $arResponse['result']['UF_COST'] = str_replace(' ', '', $converter->format($converter->convert($arPrices[$ID]['price'], $arPrices[$ID]['currency'], $_REQUEST['currency'])));
            $arResponse['result']['UF_DURATION'] = $arPrices[$ID]['duration'];
            $arResponse['result']['UF_DATE_TO'] = $arPrices[$ID]['date_to'];
            
        } catch (Exception $e) {
            
            $message = $e->getMessage();
            if ($message != '') {
                
                $arResponse['error'] = 'Произошла ошибка при попытке рассчитать стоимость. '
                        . 'Попробуйте изменить параметры для рассчета цен.';
            }
        }
    } else {
        
        $arResponse['error'] = 'Доступных цен не найдено. Попробуйте изменить параметры для рассчета цен.';
    }
}

echo \Bitrix\Main\Web\Json::encode($arResponse);