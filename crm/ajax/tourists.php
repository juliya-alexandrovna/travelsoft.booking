<?php

require_once 'header.php';

if ($_REQUEST['q']) {

    $dbTourists = \travelsoft\booking\stores\Tourists::get(array(
        'filter' => array(
            'LOGIC' => 'OR',
            array('UF_NAME' => "%".$_REQUEST['q']."%"),
            array('UF_SECOND_NAME' => "%".$_REQUEST['q']."%"),
            array('UF_LAST_NAME' => "%".$_REQUEST['q']."%"),
            array('UF_EMAIL' => "%".$_REQUEST['q']."%"),
            )
    ), false);
    
    while ($arTourist = $dbTourists->fetch()) {
        
        $name = '';
        if ($arTourist['UF_NAME']) {
            
            $name .= $arTourist['UF_NAME'];
        }
        
        if ($arTourist['UF_SECOND_NAME']) {
            
            $name .= ' '.$arTourist['UF_SECOND_NAME'];
        }
        
        If ($arTourist['UF_LAST_NAME']) {
            
            $name .= ' '.$arTourist['UF_LAST_NAME'];
        }
        
        if ($arTourist['UF_EMAIL']) {
            
            $name .= '['.$arTourist['UF_EMAIL'].']';
        }
        
        $arResponse['items'][] = array('id' => $arTourist['ID'], 'text' => $name);
    }
}

echo \Bitrix\Main\Web\Json::encode($arResponse);
