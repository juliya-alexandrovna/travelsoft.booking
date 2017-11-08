<?php

require_once 'header.php';

if ($_REQUEST['q']) {

    $GLOBALS["FILTER_logic"] = "or";
    $dbUsers = travelsoft\booking\stores\Users::get(array('filter' => array(
                    'NAME' => '%' . $_REQUEST['q'] . '%',
                    'LAST_NAME' => '%' . $_REQUEST['q'] . '%',
                    'EMAIL' => '%' . $_REQUEST['q'] . '%'
                ),
                'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL', 'UF_PASS_ISSUED_BY',
                    'UF_PASS_DATE_ISSUE', 'UF_PASS_ACTEND', 'UF_PASS_PERNUM', 'UF_PASS_NUMBER', 'UF_PASS_SERIES')
                    ), false);

    while ($arUser = $dbUsers->Fetch()) {

        $name = '';
        if ($arUser['NAME']) {

            $name .= $arUser['NAME'];
        }

        if ($arUser['SECOND_NAME']) {

            $name .= ' ' . $arUser['SECOND_NAME'];
        }

        If ($arUser['LAST_NAME']) {

            $name .= ' ' . $arUser['LAST_NAME'];
        }

        if ($arUser['EMAIL']) {

            $name .= '[' . $arUser['EMAIL'] . ']';
        }

        $arResponse['items'][] = array(
            
            'id' => $arUser['ID'],
            'text' => $name,
            'name' => $arUser['NAME'],
            'last_name' => $arUser['LAST_NAME'],
            'second_name' => $arUser['SECOND_NAME'],
            'issued_by' => $arUser['UF_PASS_ISSUED_BY'],
            'date_issue' => $arUser['UF_PASS_DATE_ISSUE'],
            'actend' => $arUser['UF_PASS_ACTEND'],
            'pernum' => $arUser['UF_PASS_PERNUM'],
            'number' => $arUser['UF_PASS_NUMBER'],
            'series' => $arUser['UF_PASS_SERIES']
        );
    }
}

echo \Bitrix\Main\Web\Json::encode($arResponse);
