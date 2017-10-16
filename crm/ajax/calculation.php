<?php

require_once 'header.php';

$converter = new \travelsoft\booking\adapters\CurrencyConverter;

//$quota = $_REQUEST['adults'] + $_REQUEST['children'];

if (
        $_REQUEST['id'] > 0 &&
        $_REQUEST['dateFrom'] != ''
//        $_REQUEST['dateFrom'] != '' && 
//        $quota > 0 &&
//        in_array($_REQUEST['currency'], $converter->getListOfCurrency())
) {
    
    $ID = intVal($_REQUEST['id']);
//    $children = intVal($_REQUEST['children']);
//    $adults = intVal($_REQUEST['adults']);
    
    $searchEngine = new \travelsoft\booking\tours\SearchEngine;
    
    $searchEngine->setExtFilter(array(
        
        'ID' => $ID,
        'UF_DATE' => $_REQUEST['dateFrom']
    ));
    
    $arSourceSearchEngine = $searchEngine->search()
//    $arPrices = $searchEngine->search()
//            ->filterByQuotas($quota)
//            ->filterByStopSale()
//            ->getCost()
            ->getCost()->getSource();
//            ->forId(array($ID))
//            ->forAdults($adults)
//            ->forChildren($children)
//            ->forAdultTourService($adults)
//            ->forChildrenTourService($children)
//            ->getMinForTour();
    
//    if (is_array($arPrices) && !empty($arPrices)) {
    $arSourceOfferPriceData = current($arSourceSearchEngine[$ID]);
    if (is_array($arSourceOfferPriceData['prices']) && !empty($arSourceOfferPriceData['prices'])) {
        
//        $arResponse['result']['UF_DURATION'] = $arSourceOfferPriceData['duration'];
        
        try {
            
//            $arResponse['result']['UF_COST'] = str_replace(' ', '', $converter->format($converter->convert($arPrices[$ID]['price'], $arPrices[$ID]['currency'], $_REQUEST['currency'])));
//            $arResponse['result']['UF_DURATION'] = $arPrices[$ID]['duration'];
//            $arResponse['result']['UF_DATE_TO'] = $arPrices[$ID]['date_to'];
            
            $arResponse['result']['UF_ADULT_PRICE'] = $arSourceOfferPriceData['prices']['adult']['price'];
            $arResponse['result']['UF_ADULT_PRICE_CRNC'] = $arSourceOfferPriceData['prices']['adult']['currency'];
            $arResponse['result']['UF_CHILDREN_PRICE'] = $arSourceOfferPriceData['prices']['children']['price'];
            $arResponse['result']['UF_CHILD_PRICE_CRNC'] = $arSourceOfferPriceData['prices']['children']['currency'];
            $arResponse['result']['UF_ADULTTS_PRICE'] = $arSourceOfferPriceData['prices']['adult_tour_service']['price'];
            $arResponse['result']['UF_ADTS_PRICE_CRNC'] = $arSourceOfferPriceData['prices']['adult_tour_service']['currency'];
            $arResponse['result']['UF_CHILDTS_PRICE'] = $arSourceOfferPriceData['prices']['children_tour_service']['price'];
            $arResponse['result']['UF_CHTS_PRICE_CRNC'] = $arSourceOfferPriceData['prices']['children_tour_service']['currency'];
            $arResponse['result']['UF_DURATION'] = $arSourceOfferPriceData['duration'];
            $arResponse['result']['UF_DATE_TO'] = $arSourceOfferPriceData['date_to'];
            
        } catch (Exception $e) {
            
            $message = $e->getMessage();
            if ($message != '') {
                
                $arResponse['error'] = 'Произошла ошибка при попытке рассчитать стоимость. '
                        . 'Попробуйте изменить параметры для рассчета цен.';
            }
        }
    } else {
        
        $arResponse['error'] = 'Доступных цен не найдено или квота на тур истекла. Попробуйте изменить параметры для рассчета цен.';
    }
}

echo \Bitrix\Main\Web\Json::encode($arResponse);