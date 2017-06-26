<?php

use travelsoft\booking\stores\Tours;
use travelsoft\booking\tours\Cost;

/**
 * Класс оформления заказа тугуслуги (ajax)
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class TravelsoftAjaxMakeOrder extends CBitrixComponent {

    /**
     * @throws Exception
     */
    protected function _checkOffer() {

        $this->arParams['OFFER_ID'] = intVal($this->arParams['OFFER_ID']);

        $this->arResult['OFFER'] = Tours::getById($this->arParams['OFFER_ID']);

        if (empty($this->arResult['OFFER'])) {

            throw new Exception('Указан несуществующий ID туруслуги');
        }
    }

    /**
     * Обработка входных параметров компонента
     */
    public function prepareParameters() {
        
        if (!Bitrix\Main\Loader::includeModule('travelsoft.booking')) {
            
            throw new \Exception('Модуль travelsoft.booking не найден');
        }
        
        $this->_checkOffer();
    }

    public function checkUserRegistration() {
        
    }

    public function userRegistration() {
        
    }

    public function userAuthorization() {
        
    }

    /**
     * Устанавливает в $arResult описание стоимости предложения
     */
    public function setOfferCostDesc() {

    }

    public function processRequest() {

        if (
                $_SERVER['REQUEST_METHOD'] == 'POST' &&
                check_bitrix_sessid() &&
                strlen($_POST['BOOKING_NOW']) > 0 &&
                $this->arResult['USER']['IS_AUTHORIZED']
        ) {
            
            if (strlen($_POST['USER_LAST_NAME']) < 2) {
                
                $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_LAST_NAME';
            }
            
            if (strlen($_POST['USER_NAME']) < 2) {
                
                $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_NAME';
            }
            
            if ( !check_email($_POST['USER_EMAIL']) ) {
                
                $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_EMAIL';
            }
            
            $_POST['USER_PHONE'] = preg_replace('/\s/', '', $_POST['USER_PHONE']);
            if (preg_match('#^\+?[0-9]{0,}$#', $_POST['USER_PHONE']) !== 1) {
                
                $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_PHONE';
            }
            
            if ($this->arResult['QUOTAS'][$_POST['DATE']] > 0) {
                
                $this->arResult['CODE_ERRORS'][] = 'QUOTA_NOT_FOUND';
            }
            
            $_POST['ADULTS'] = intVal($_POST['ADULTS']);
            $_POST['CHILDREN'] = intVal($_POST['CHILDREN']);
            
            $PEOPLE = $_POST['ADULTS'] + $_POST['CHILDREN'];
            
            if ($PEOPLE <= 0) {
                
                $this->arResult['CODE_ERRORS'][] = 'WRONG_PEOPLE_COUNT';
            }
            
            if ($PEOPLE > $this->arResult['QUOTAS'][$_POST['DATE']]) {
                
                $this->arResult['CODE_ERRORS'][] = 'QUOTA_OVERLOAD';
            }
            
        }
    }

    /**
     * component body
     */
    public function executeComponent() {

        try {

            $this->prepareParameters();

            $this->processRequest();

            $this->IncludeComponentTemplate();
        } catch (\Exception $e) {

            ShowError($e->getMessage());
        }
    }

}
