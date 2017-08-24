<?php

require_once 'header.php';

\Bitrix\Main\Loader::includeModule('iblock');

/**
 * @param type $property
 * @return string
 */
function getLinkElementName ($property) {
    
    if ($property['PROPERTY_TYPE'] == 'E') {
            
            if ($property['MULTIPLE'] == 'Y') {
                
                $arValues = array();
                foreach ($property['VALUE'] as $val) {
                    
                   $arElement = CIBlockElement::GetByID($val)->Fetch(); 
                   $arValues[] = $arElement['NAME'];
                }
                return implode(', ', $arValues);
                
            } else {
                
                $arElement = CIBlockElement::GetByID($property['VALUE'])->Fetch();
                return $arElement['NAME'];
            }
            
        } else {
            
            if ($property['MULTIPLE'] == 'Y') {
                
                return implode(', ', $property['VALUE']);
            } else {
                
                return $property['VALUE'];
            }
            
        }
    
}

if ($_REQUEST['q']) {

    $dbTours = \travelsoft\booking\stores\Tours::get(array('filter' => array('NAME' => '%' . $_REQUEST['q'] . '%')), false);
    
    $result = array();
    while ($dbTour = $dbTours->GetNextElement()) {
        
        $arFields = $dbTour->GetFields();
        $arProperties = $dbTour->GetProperties();
        
        $result = array('id' => $arFields['ID'], 'text' => $arFields['NAME']);
        
        if ($arProperties['HOTEL']['VALUE'] > 0) {
            
            $result['hotel'] = getLinkElementName($arProperties['HOTEL']);
        }
        
        if ($arProperties['FOOD']['VALUE']) {
            
            $result['food'] = getLinkElementName($arProperties['FOOD']);
        }
        
        if ($arProperties['COUNTRY']['VALUE']) {
            
            $result['country'] = getLinkElementName($arProperties['COUNTRY']);
        }
        
        if ($arProperties['POINT_DEPARTURE']['VALUE']) {
            
            $result['point_departure'] = $result['point_arrival'] = getLinkElementName($arProperties['POINT_DEPARTURE']);
        }
        
        if ($arProperties['TOURTYPE']['VALUE']) {
            
            $result['type'] = getLinkElementName($arProperties['TOURTYPE']);
        }
        
        $arResponse['items'][] = $result;
    }
}

echo \Bitrix\Main\Web\Json::encode($arResponse);

