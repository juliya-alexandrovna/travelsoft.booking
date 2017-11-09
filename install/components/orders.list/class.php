<?php

use travelsoft\booking\stores\Orders;
use travelsoft\booking\stores\Statuses;
use travelsoft\booking\stores\Users;

/**
 * Класс списка заказов
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class TravelsoftOrdersList extends CBitrixComponent {

    /**
     * Обработка входных параметров компонента
     */
    public function prepareParameters() {

        if (!Bitrix\Main\Loader::includeModule('travelsoft.booking')) {

            throw new Exception('Модуль travelsoft.booking не найден');
        }

        if (!$GLOBALS['USER']->IsAuthorized()) {

            throw new Exception('Список заказов доступен только для зарегистрированных пользователей');
        }

        if (!$this->arParams['DETAIL_PAGE']) {

            throw new Exception('Укажите шаблон детальной страницы заказа');
        }

        $this->arParams["PAGE_ORDERS_COUNT"] = $this->arParams["PAGE_ORDERS_COUNT"] > 0 ? $this->arParams["PAGE_ORDERS_COUNT"] : 10;
    }
    
    /**
     * @return array
     */
    public function getFilterByUser () {
        
        $filter = array();
        if (!$GLOBALS['USER']->IsAdmin()) {

            $filter['UF_USER_ID'] = $GLOBALS['USER']->GetID();
        }
        
        return $filter;
    }
    
    /**
     * @return array
     */
    public function getFilter () {
        
        $filter = array();
        
        if (strlen($_REQUEST['SHOW_FROM_FILTER']) > 0 && check_bitrix_sessid()) {

            if ($_REQUEST['UF_DATE_FROM']) {
                $filter['UF_DATE_FROM'] = $_REQUEST['UF_DATE_FROM'];
            }

            if ($_REQUEST['UF_SERVICE_NAME']) {
                $filter['UF_SERVICE_NAME'] = $_REQUEST['UF_SERVICE_NAME'];
            }

            if ($_REQUEST['UF_DATE']) {
                $filter['><UF_DATE'] = array(date('d.m.Y H:i:s', MakeTimeStamp($_REQUEST['UF_DATE'])), date('d.m.Y H:i:s', MakeTimeStamp($_REQUEST['UF_DATE']) + 86399));
            }

            if ($_REQUEST['UF_STATUS_ID']) {
                $filter['UF_STATUS_ID'] = $_REQUEST['UF_STATUS_ID'];
            }
        }
        
        return array_merge($filter, $this->getFilterByUser());
    }
    
    public function createVariables () {
        
        # orders
        $dbOrders = Orders::get(array("filter" => $this->getFilterByUser()), false);
        
        $trash = array();
        while ($order = $dbOrders->Fetch()) {
            $this->arResult['VARS']['ORDERS'][] = $order['ID'];
            if (!in_array($order['UF_SERVICE_NAME'], $trash)) {
                $this->arResult['VARS']['SERVICES_NAMES'][] = array('UF_SERVICE_NAME' => $order['UF_SERVICE_NAME']);
                $trash[] = $order['UF_SERVICE_NAME'];
            }
        }
        
        # statuses
        $dbStatuses = Statuses::get(array("order" => array('ID' => 'ASC')), false);
        
        while ($status = $dbStatuses->Fetch()) {
            $this->arResult['VARS']['STATUSES'][$status['ID']] = array("ID" => $status['ID'], 'UF_NAME' => $status['UF_NAME']);
        }
    }
    
    /**
     * component body
     */
    public function executeComponent() {

        global $APPLICATION;

        try {
            
            if (strlen($_REQUEST['CANCEL']) > 0) {
                LocalRedirect($APPLICATION->GetCurPageParam("", array(
                    "sessid",
                    "UF_SERVICE_NAME",
                    "UF_STATUS_ID",
                    "UF_DATE",
                    "UF_DATE_FROM",
                    "CANCEL",
                    "SHOW_FROM_FILTER"
                ), false));
            }
            
            $this->prepareParameters();

            $arUsers = array();

            $this->arResult["NAV"] = new \Bitrix\Main\UI\PageNavigation("nav-orders-list");
            $this->arResult["NAV"]->allowAllRecords(true)->setPageSize($this->arParams["PAGE_ORDERS_COUNT"])->initFromUri();

            $dbOrders = Orders::get(array(
                        "filter" => $this->getFilter(),
                        "count_total" => true,
                        "order" => array('ID' => 'DESC'),
                        "offset" => $this->arResult["NAV"]->getOffset(),
                        "limit" => $this->arResult["NAV"]->getLimit()
                            ), false);

            $converter = new travelsoft\booking\adapters\CurrencyConverter;
            
            $this->createVariables();
            
            while ($arOrder = $dbOrders->fetch()) {

                if ($arOrder['UF_STATUS_ID']) {

                    $arOrder['STATUS_NAME'] = $this->arResult['VARS']['STATUSES'][$arOrder['UF_STATUS_ID']]['UF_NAME'];
                }

                if ($arOrder['UF_USER_ID'] > 0) {

                    if (!$arUsers[$arOrder['UF_USER_ID']]) {

                        $arUser = current(Users::get(array('filter' => array('ID' => $arOrder['UF_USER_ID']), 'select' => array('ID', 'NAME', 'SECOND_NAME', 'LAST_NAME', 'EMAIL', 'UF_LEGAL_NAME'))));
                        if ($arUser['ID'] > 0) {

                            $arUsers[$arUser['ID']] = $arUser;
                        }
                    }

                    $arOrder['USER_NAME'] = strlen($arUsers[$arUser['ID']]['UF_LEGAL_NAME']) > 0 ? $arUsers[$arUser['ID']]['UF_LEGAL_NAME'] : $arUsers[$arUser['ID']]['NAME'] . ' ' . $arUsers[$arUser['ID']]['LAST_NAME'];
                }
                
                Orders::preparedOrderFieldsForView($arOrder);
                
                $this->arResult['ORDERS_LIST'][$arOrder['ID']] = $arOrder;

            }

            $this->arResult["NAV"]->setRecordCount($dbOrders->getCount());

            $this->IncludeComponentTemplate();
        } catch (\Exception $e) {

            ShowError($e->getMessage());
        }
    }

}
