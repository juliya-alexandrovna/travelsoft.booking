<?php

use travelsoft\booking\stores\Orders;
use travelsoft\booking\stores\Statuses;

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
     * component body
     */
    public function executeComponent() {

        try {

            $this->prepareParameters();

            $arFilter = array();
            if (!$GLOBALS['USER']->IsAdmin()) {

                $arFilter['UF_USER_ID'] = $GLOBALS['USER']->GetID();
            }

            $arOrdersListStatuses = array();

            $this->arResult["NAV"] = new \Bitrix\Main\UI\PageNavigation("nav-orders-list");
            $this->arResult["NAV"]->allowAllRecords(true)->setPageSize($this->arParams["PAGE_ORDERS_COUNT"])->initFromUri();

            $dbOrders = Orders::get(array(
                        'filter' => $arFilter,
                        "count_total" => true,
                        "order" => array('ID' => 'DESC'),
                        "offset" => $this->arResult["NAV"]->getOffset(),
                        "limit" => $this->arResult["NAV"]->getLimit()
                            ), false);

            $converter = new travelsoft\booking\adapters\CurrencyConverter;

            while ($arOrder = $dbOrders->fetch()) {

                if ($arOrder['UF_STATUS_ID']) {

                    if (!isset($arOrdersListStatuses[$arOrder['UF_STATUS_ID']])) {

                        $arStatus = Statuses::getById($arOrder['UF_STATUS_ID']);
                        $arOrdersListStatuses[$arStatus['ID']] = $arStatus['UF_NAME'];
                    }

                    $arOrder['STATUS_NAME'] = $arOrdersListStatuses[$arOrder['UF_STATUS_ID']];
                }

                $this->arResult['ORDERS_LIST'][$arOrder['ID']] = $arOrder;
                $this->arResult['ORDERS_LIST'][$arOrder['ID']]['COST_FORMATTED'] = $converter->format($arOrder['UF_COST']);
            }

            $this->arResult["NAV"]->setRecordCount($dbOrders->getCount());

            $this->IncludeComponentTemplate();
        } catch (\Exception $e) {

            ShowError($e->getMessage());
        }
    }

}
