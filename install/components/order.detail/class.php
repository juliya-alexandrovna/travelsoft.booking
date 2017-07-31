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
class TravelsoftOrderDetail extends CBitrixComponent {

    /**
     * Обработка входных параметров компонента
     */
    public function prepareParameters() {

        if (!Bitrix\Main\Loader::includeModule('travelsoft.booking')) {

            throw new Exception('Модуль travelsoft.booking не найден');
        }

        if (!$GLOBALS['USER']->IsAuthorized()) {

            throw new Exception('Информация по заказу доступна только для зарегистрированного пользователя');
        }
        
        $this->arParams['ORDER_ID'] = intVal($this->arParams['ORDER_ID']);
        if ($this->arParams['ORDER_ID'] <= 0) {
            
            throw new Exception('Не указан номер заказа');
        }

    }

    /**
     * component body
     */
    public function executeComponent() {

        try {

            $this->prepareParameters();
            
            $arFilter = array('ID' => $this->arParams['ORDER_ID']);
            if (!$GLOBALS['USER']->IsAdmin()) {

                $arFilter['UF_USER_ID'] = $GLOBALS['USER']->GetID();
            }
            
            $this->arResult['ORDER'] = current( Orders::get(array(
                'filter' => $arFilter
            )) );
            
            if ($this->arResult['ORDER']['UF_STATUS_ID']) {
                
                $arStatus = Statuses::getById($this->arResult['ORDER']['UF_STATUS_ID']);
                $this->arResult['ORDER']['STATUS'] = $arStatus['UF_NAME'];
            }
            
            if ($this->arResult['ORDER']['UF_USER_ID'] > 0) {

                    if (!$arUsers[$this->arResult['ORDER']['UF_USER_ID']]) {

                        $arUser = Users::getById($this->arResult['ORDER']['UF_USER_ID']);
                        if ($arUser['ID'] > 0) {

                            $arUsers[$arUser['ID']] = $arUser;
                        }
                    }

                    $this->arResult['ORDER']['USER_NAME'] = $arUsers[$arUser['ID']]['NAME'];
                    $this->arResult['ORDER']['USER_LAST_NAME'] = $arUsers[$arUser['ID']]['LAST_NAME'];
                    $this->arResult['ORDER']['USER_PHONE'] = $arUsers[$arUser['ID']]['PERSONAL_PHONE'];
                }
            
            $this->arResult['ORDER']['COST_FORMATTED'] = (new travelsoft\booking\adapters\CurrencyConverter)->format($this->arResult['ORDER']['UF_COST']);

            $this->IncludeComponentTemplate();
        } catch (\Exception $e) {

            ShowError($e->getMessage());
        }
    }

}
