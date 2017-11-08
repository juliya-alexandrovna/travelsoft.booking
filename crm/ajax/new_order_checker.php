<?php

require_once 'header.php';

$arResponse['result']['content'] = '';

if ($_REQUEST['last_id'] > 0 && bitrix_sessid()) {

    $LAST_ID = intVal($_REQUEST['last_id']);

    $arOrders = travelsoft\booking\stores\Orders::get(array(
                'filter' => array('>ID' => $LAST_ID),
                'order' => array('ID' => 'DESC')
    ));

    if (!empty($arOrders)) {

        $arHeaders = \travelsoft\booking\crm\Utils::getOrdersTableHeaders();

        $list = new CAdminList(\travelsoft\booking\crm\Settings::ORDERS_HTML_TABLE_ID);
        
        $list->AddHeaders($arHeaders);

        $list->AddGroupActionTable(Array(
            "delete" => "Удалить"
        ));
        
        foreach ($arOrders as $ID => $arOrder) {
        
            $row = new CAdminListRow($arHeaders, \travelsoft\booking\crm\Settings::ORDERS_HTML_TABLE_ID);

            $row->id = $arOrder['ID'];
            $row->arRes = $arOrder;
            $row->pList = &$list;
            $row->pList->bShowActions = true;

            $arStatus = \travelsoft\booking\stores\Statuses::getById($arOrder['UF_STATUS_ID']);

            \travelsoft\booking\crm\Utils::prepareRowForOrdersTable($row, array(
                'ORDER' => $arOrder,
                'STATUSES' => array($arStatus['ID'] => $arStatus)
            ));
            
            ob_start();
            $row->Display();
            $arResponse['result']['content'] .= ob_get_clean();
            
        }
            
        $arResponse['result']['last_id'] = travelsoft\booking\stores\Orders::getLastId();
    }
}

echo \Bitrix\Main\Web\Json::encode($arResponse);
