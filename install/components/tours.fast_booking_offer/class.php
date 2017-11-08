<?php

use travelsoft\booking\stores\Tours;
use travelsoft\booking\tours\SearchEngine;
use travelsoft\booking\Settings;
use travelsoft\booking\stores\Orders;
use travelsoft\booking\adapters\Mail;
use travelsoft\booking\adapters\Date;
use travelsoft\booking\stores\Statuses;

/**
 * Класс оформления заказа тугуслуги
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class TravelsoftMakeOrder extends CBitrixComponent {

    /**
     * @var \travelsoft\booking\tours\Cost 
     */
    protected $_cost;

    /**
     * @return array
     */
    protected function _getExtFilter(): array {

        return array(
            'ID' => $this->arParams['OFFER_ID'],
            'UF_DATE' => $this->arParams['DATE']
        );
    }

    /**
     * Получение дополнительных полей по предложению
     * @param string $propertyCode
     * @return mixed
     */
    protected function _getOfferAdditionalField($propertyCode) {

        if ($this->arResult['OFFER']['PROPERTIES'][$propertyCode]['PROPERTY_TYPE'] == 'E') {

            if ($this->arResult['OFFER']['PROPERTIES'][$propertyCode]['MULTIPLE'] == 'Y') {

                $arValues = array();
                foreach ($this->arResult['OFFER']['PROPERTIES'][$propertyCode]['VALUE'] as $val) {

                    $arElement = CIBlockElement::GetByID($val)->Fetch();
                    $arValues[] = $arElement['NAME'];
                }
                return implode(', ', $arValues);
            } else {

                $arElement = CIBlockElement::GetByID($this->arResult['OFFER']['PROPERTIES'][$propertyCode]['VALUE'])->Fetch();
                return $arElement['NAME'];
            }
        } else {

            if ($this->arResult['OFFER']['PROPERTIES'][$propertyCode]['MULTIPLE'] == 'Y') {

                return implode(', ', $this->arResult['OFFER']['PROPERTIES'][$propertyCode]['VALUE']);
            } else {

                return $this->arResult['OFFER']['PROPERTIES'][$propertyCode]['VALUE'];
            }
        }
    }

    /**
     * проверка пароля в соответствии с политикой безопастности
     * @param string $password
     * @return bool
     */
    protected function _checkPasswordAgainstPolicy(string $password): bool {

        $defGroups = \Bitrix\Main\Config\Option::get('main', 'new_user_registration_def_group');

        $arDefGroups = array();

        if (strlen($defGroups) > 0) {

            $arDefGroups = explode(',', $defGroups);
        }

        $arPolicy = $GLOBALS['USER']->GetGroupPolicy($defGroups);

        $arPasswordErros = $GLOBALS['USER']->CheckPasswordAgainstPolicy($_POST['PASSWORD'], $arPolicy);

        if (!empty($arPasswordErros)) {

            $this->arResult['CODE_ERRORS'][] = 'WRONG_PASSWORD';

            $this->arResult['SYSTEM_ERRORS_MESSAGES']['WRONG_PASSWORD'] = implode('<br>', array_map(function ($error) {
                        return "(" . $error . ")";
                    }, $arPasswordErros));

            return false;
        }

        return true;
    }

    /**
     * Обработка входных параметров компонента
     */
    public function prepareParameters() {

        if (!Bitrix\Main\Loader::includeModule('travelsoft.booking')) {

            throw new \Exception('Модуль travelsoft.booking не найден');
        }

        $this->arParams['OFFER_ID'] = intVal($this->arParams['OFFER_ID']);

        # получаем информацию по предложению
        $this->arResult['OFFER'] = Tours::getById($this->arParams['OFFER_ID']);

        if (empty($this->arResult['OFFER'])) {

            throw new Exception('Указан несуществующий ID услуги');
        }

        if (!$this->arParams['DATE']) {

            throw new Exception('Укажите дату заезда');
        }

        if (!$this->arParams['CONVERT_IN_CURRENCY_ISO']) {

            throw new Exception('Выберите валюту для отображения цен');
        }

        # получаем объект по расчёту стоимости
        $this->arResult['COST'] = (new SearchEngine)->setExtFilter($this->_getExtFilter())
                        ->search()->filterByStopSale()->filterByQuotas(1)->getCost();

        $arCostSource = current(current($this->arResult['COST']->getSource()));

        if (empty($arCostSource)) {

            throw new Exception('По данному предложению цен не найдено');
        }

        $currencyConveter = new travelsoft\booking\adapters\CurrencyConverter;

        $this->arResult['DATE_FROM'] = $arCostSource['date_from'];

        $this->arResult['DATE_TO'] = $arCostSource['date_to'];

        $this->arResult['DURATION'] = $arCostSource['duration'];

        $this->arResult['QUOTA'] = $arCostSource['quota'];

        if ($arCostSource['prices']['adult']['price'] > 0) {

            $this->arResult['ADULT_PRICE'] = $currencyConveter->convert($arCostSource['prices']['adult']['price'], $arCostSource['prices']['adult']['currency'], $this->arParams['CONVERT_IN_CURRENCY_ISO']);

            $this->arResult['ADULT_PRICE_FORMATTED'] = $currencyConveter->getFormatted(
                    $this->arResult['ADULT_PRICE'], $this->arParams['CONVERT_IN_CURRENCY_ISO']
            );

            if ($arCostSource['prices']['adult_tour_service']['price'] > 0) {
                $this->arResult['ADULT_TOUR_SERVICE_PRICE'] = $currencyConveter->convert($arCostSource['prices']['adult_tour_service']['price'], (string) $arCostSource['prices']['adult_tour_service']['currency'], $this->arParams['CONVERT_IN_CURRENCY_ISO']);


                $this->arResult['ADULT_TOUR_SERVICE_PRICE_FORMATTED'] = $currencyConveter->getFormatted(
                        $this->arResult['ADULT_TOUR_SERVICE_PRICE'], $this->arParams['CONVERT_IN_CURRENCY_ISO']
                );
            }
        }

        if ($arCostSource['prices']['children']['price'] > 0) {

            $this->arResult['CHILDREN_PRICE'] = $currencyConveter->convert($arCostSource['prices']['children']['price'], $arCostSource['prices']['children']['currency'], $this->arParams['CONVERT_IN_CURRENCY_ISO']);

            $this->arResult['CHILDREN_PRICE_FORMATTED'] = $currencyConveter->getFormatted(
                    $this->arResult['CHILDREN_PRICE'], $this->arParams['CONVERT_IN_CURRENCY_ISO']
            );

            if ($arCostSource['prices']['children_tour_service']['price'] > 0) {
                $this->arResult['CHILDREN_TOUR_SERVICE_PRICE'] = $currencyConveter->convert($arCostSource['prices']['children_tour_service']['price'], $arCostSource['prices']['children_tour_service']['currency'], $this->arParams['CONVERT_IN_CURRENCY_ISO']);

                $this->arResult['CHILDREN_TOUR_SERVICE_PRICE_FORMATTED'] = $currencyConveter->getFormatted(
                        $this->arResult['CHILDREN_TOUR_SERVICE_PRICE'], $this->arParams['CONVERT_IN_CURRENCY_ISO']
                );
            }
        }

        $this->arResult['IS_AUTH_USER'] = $GLOBALS['USER']->IsAuthorized();

        if ($this->arResult['IS_AUTH_USER']) {
            $this->arResult['USER'] = $GLOBALS['USER']->GetList(
                            ($by = "ID"), ($order = "DESC"), array('ID' => $GLOBALS['USER']->GetID()), array('SELECT' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME', 'PERSONAL_PHONE'))
                    )->Fetch();
        }

        $this->arResult['HASH'] = md5(serialize($this->arParams) . $this->GetTemplateName());

        if ($this->arParams['USE_AJAX_MODE'] == 'Y') {
            CJSCore::Init(array('ajax'));
        }

        $this->arResult['USER_EMAIL'] = $_POST['USER_EMAIL'];
        $this->arResult['USER_NAME'] = $_POST['USER_NAME'];
        $this->arResult['USER_LAST_NAME'] = $_POST['USER_LAST_NAME'];
        $this->arResult['USER_PHONE'] = $_POST['USER_PHONE'] ? $_POST['USER_PHONE'] : $this->arResult['USER']['PERSONAL_PHONE'];
        $this->arResult['ADULTS'] = intVal($_POST['ADULTS']);
        $this->arResult['CHILDREN'] = intVal($_POST['CHILDREN']);
        $this->arResult['USER_COMMENT'] = $_POST['USER_COMMENT'];
    }

    /**
     * component body
     */
    public function executeComponent() {

        try {

            $this->prepareParameters();

            if (
                    $_SERVER['REQUEST_METHOD'] == 'POST' &&
                    check_bitrix_sessid() &&
                    strlen($_POST['BOOKING_NOW']) > 0 &&
                    $_POST['HASH'] === $this->arResult['HASH']
            ) {

                if (!$this->arResult['IS_AUTH_USER']) {

                    if ($_POST['FIRST_TIME'] == 'Y') {

                        # проверка email пользователя
                        if (!check_email($this->arResult['USER_EMAIL'])) {

                            $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_EMAIL';
                        }

                        # проверка фамилии пользователя
                        if (strlen($this->arResult['USER_LAST_NAME']) < 2) {

                            $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_LAST_NAME';
                        }

                        # проверка имени пользователя
                        if (strlen($this->arResult['USER_NAME']) < 2) {

                            $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_NAME';
                        }

                        if ($this->_checkPasswordAgainstPolicy($_POST['PASSWORD']) &&
                                strcmp($_POST['PASSWORD'], $_POST['CONFIRM_PASSWORD']) !== 0) {

                            $this->arResult['CODE_ERRORS'][] = 'WRONG_PASSWORD_CONFORMATION';
                        }

                        if (empty($this->arResult['CODE_ERRORS'])) {

                            if (\Bitrix\Main\Config\Option::get('main', 'captcha_registration') == 'Y') {
                                OptionTmpChanger::changeOption('main', 'captcha_registration', 'N');
                            }

                            $arResult = $GLOBALS['USER']->Register(
                                    $this->arResult['USER_EMAIL'], $this->arResult['USER_NAME'], $this->arResult['USER_LAST_NAME'], $_POST['PASSWORD'], $_POST['CONFIRM_PASSWORD'], $this->arResult['USER_EMAIL']
                            );

                            if ($arResult['TYPE'] != 'OK') {

                                $this->arResult['CODE_ERRORS'][] = 'REGISTER_FAIL';
                            } else {

                                $this->arResult['USER'] = $GLOBALS['USER']->GetList(
                                                ($by = "ID"), ($order = "DESC"), array('EMAIL' => $this->arResult['USER_EMAIL']), array('SELECT' => array('ID', 'EMAIL', 'NAME'))
                                        )->Fetch();

                                $GLOBALS['USER']->Update($this->arResult['USER']['ID'], array('PERSONAL_PHONE' => $this->arResult['USER_PHONE']));
                            }
                        }
                    } elseif ($_POST['FIRST_TIME'] == 'N') {

                        # проверка email пользователя
                        if (!check_email($this->arResult['USER_EMAIL'])) {

                            $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_EMAIL';
                        }

                        $this->arResult['USER'] = $GLOBALS['USER']->GetList(
                                        ($by = "ID"), ($order = "DESC"), array('EMAIL' => $this->arResult['USER_EMAIL']), array('SELECT' => array('ID', 'PASSWORD', 'ACTIVE', 'EMAIL', 'NAME', 'LAST_NAME'))
                                )->Fetch();

                        if ($this->arResult['USER']['ID'] > 0) {

                            # проверка пароля перед попыткой авторизации
                            if (strlen($this->arResult['USER']["PASSWORD"]) > 32) {

                                $salt = substr($this->arResult['USER']["PASSWORD"], 0, strlen($this->arResult['USER']["PASSWORD"]) - 32);
                                $db_password = substr($this->arResult['USER']["PASSWORD"], -32);
                            } else {

                                $salt = "";
                                $db_password = $this->arResult['USER']["PASSWORD"];
                            }

                            if (md5($salt . $_POST['PASSWORD']) == $db_password) {

                                if ($this->arResult['USER']['ACTIVE'] == 'Y') {

                                    $GLOBALS['USER']->Authorize($this->arResult['USER']["ID"]);
                                }
                            } else {

                                $this->arResult['CODE_ERRORS'][] = 'WRONG_ENTERED_USER_PASSWORD';
                            }
                        } else {

                            $this->arResult['CODE_ERRORS'][] = 'USER_NOT_FOUND';
                        }
                    }
                }

                # проверка телефона пользователя
                $this->arResult['USER_PHONE'] = preg_replace('/\s/', '', $this->arResult['USER_PHONE']);
                if (preg_match('#^\+?[0-9]{5,}$#', $this->arResult['USER_PHONE']) !== 1) {

                    $this->arResult['CODE_ERRORS'][] = 'WRONG_USER_PHONE';
                }

                # проверка квот
                $PEOPLE = $this->arResult['ADULTS'] + $this->arResult['CHILDREN'];

                if ($PEOPLE <= 0) {

                    $this->arResult['CODE_ERRORS'][] = 'WRONG_PEOPLE_COUNT';
                }

                $arSrc = current(current($this->arResult['COST']->getSource()));

                if ($PEOPLE > $arSrc['quota']) {

                    $this->arResult['CODE_ERRORS'][] = 'QUOTA_OVERLOAD';
                }

                if (empty($this->arResult['CODE_ERRORS'])) {

                    # подсчёт стоимости
                    $arCost = $this->arResult['COST']->forAdults($this->arResult['ADULTS'])->forChildren($this->arResult['CHILDREN'])->forAdultTourService($this->arResult['ADULTS'])->forChildrenTourService($this->arResult['CHILDREN'])->getMinForTours();

                    $STATUS_ID = Settings::defStatus();

                    $DEPPOINT = $this->_getOfferAdditionalField($this->arParams['PROPERTY_POINT_DEPARTURE_CODE']);
                    
                    $arCostSourceData = $this->arResult['COST']->getSource();
                    
                    $arCostInfo = $arCostSourceData[$this->arResult['OFFER']['ID']][MakeTimeStamp($arCost['date_from'])]['prices'];
                   
                    # создание заказа
                    $ORDER_ID = Orders::add(array(
                                'UF_DEP_CITY' => $DEPPOINT,
                                'UF_ARR_CITY' => $DEPPOINT,
                                'UF_HOTEL' => $this->_getOfferAdditionalField($this->arParams['PROPERTY_HOTEL_CODE']),
                                'UF_FOOD' => $this->_getOfferAdditionalField($this->arParams['PROPERTY_FOOD_CODE']),
                                'UF_COUNTRY' => $this->_getOfferAdditionalField($this->arParams['PROPERTY_COUNTRY_CODE']),
                                'UF_SERVICE_TYPE' => $this->_getOfferAdditionalField($this->arParams['PROPERTY_TYPE_CODE']),
                                'UF_SERVICE_ID' => $this->arResult['OFFER']['ID'],
                                'UF_USER_ID' => $this->arResult['USER']['ID'],
                                'UF_STATUS_ID' => $STATUS_ID,
                                'UF_DATE' => Date::createFromTimetamp(time()),
                                'UF_COST' => $arCost['price'],
                                'UF_CURRENCY' => $arCost['currency'],
                                'UF_DATE_FROM' => $this->arResult['DATE_FROM'],
                                'UF_DATE_TO' => $this->arResult['DATE_TO'],
                                'UF_SERVICE_NAME' => $this->arResult['OFFER']['NAME'],
                                'UF_DURATION' => $this->arResult['DURATION'],
                                'UF_ADULTS' => $this->arResult['ADULTS'],
                                'UF_CHILDREN' => $this->arResult['CHILDREN'],
                                'UF_ADULT_PRICE' => $arCostInfo['adult']['price'],
                                'UF_ADULT_PRICE_CRNC' => $arCostInfo['adult']['currency'],
                                'UF_CHILDREN_PRICE' => $arCostInfo['children']['price'],
                                'UF_CHILD_PRICE_CRNC' => $arCostInfo['children']['currency'],
                                'UF_ADULTTS_PRICE' => $arCostInfo['adult_tour_service']['price'],
                                'UF_ADTS_PRICE_CRNC' => $arCostInfo['adult_tour_service']['currency'],
                                'UF_CHILDTS_PRICE' => $arCostInfo['children_tour_service']['price'],
                                'UF_CHTS_PRICE_CRNC' => $arCostInfo['children_tour_service']['currency'],
                                'UF_COMMENT' => $this->arResult['USER_COMMENT']
                    ));

                    if ($ORDER_ID > 0) {

                        $inSale = \travelsoft\booking\Utils::increaseNumberOfSold(
                                $this->arResult['OFFER']['ID'], $arCost['date_from'], $PEOPLE);

                        $arStatus = Statuses::getById($STATUS_ID);

                        $arEmailFields = array(
                            "ORDER_ID" => $ORDER_ID,
                            "STATUS" => $arStatus['UF_NAME'],
                            "USER_EMAIL" => $this->arResult['USER_EMAIL'] ? $this->arResult['USER_EMAIL'] : $this->arResult['USER']['EMAIL'],
                            "USER_NAME" => $this->arResult['USER_NAME'] ? $this->arResult['USER_NAME'] : $this->arResult['USER']['NAME'],
                            "USER_LAST_NAME" => $this->arResult['USER_LAST_NAME'] ? $this->arResult['USER_LAST_NAME'] : $this->arResult['USER']['LAST_NAME'],
                            "USER_PHONE" => $this->arResult['USER_PHONE'] ? $this->arResult['USER_PHONE'] : $this->arResult['USER']['PERSONAL_PHONE'],
                            "EMAIL_TO" => $this->arResult['USER_EMAIL'] ? $this->arResult['USER_EMAIL'] : $this->arResult['USER']['EMAIL'],
                            "SERVICE_NAME" => $this->arResult['OFFER']['NAME'],
                            "DATE_FROM" => $this->arResult['DATE_FROM'],
                            "DATE_TO" => $this->arResult['DATE_TO'],
                            "DURATION" => $this->arResult['DURATION'],
                            "ADULTS" => $this->arResult['ADULTS'],
                            "CHILDREN" => $this->arResult['CHILDREN'],
                            "TOTAL_COST" => $arCost['price_formatted'],
                            "ORDER_LIST_PAGE" => $this->arParams['ORDER_LIST_PAGE'],
                            "ORDER_DETAIL_PAGE" => str_replace("#ORDER_ID#", $ORDER_ID, $this->arParams['ORDER_DETAIL_PAGE'])
                        );

                        # отправка уведомления о новом заказе
                        # менеджеру
                        Mail::sendNewOrderNotificationForManager($arEmailFields);

                        $arUserGroups = $GLOBALS['USER']->GetUserGroup($this->arResult['USER']['ID']);

                        if (in_array(Settings::agentsUGroup(), $arUserGroups)) {
                            # агенту
                            Mail::sendNewOrderNotificationForAgent($arEmailFields);
                        } else {
                            # клиенту
                            Mail::sendNewOrderNotificationForAgent($arEmailFields);
                        }
                    } else {

                        $this->arResult['CODE_ERRORS'][] = 'ORDER_CREATION_FAIL';
                    }
                }

                if ($this->arParams['USE_AJAX_MODE'] == 'Y') {

                    header('Content-type: application/json');

                    $GLOBALS['APPLICATION']->RestartBuffer();

                    $arResponse = array();

                    if (empty($this->arResult['CODE_ERRORS'])) {

                        $arResponse['success'] = true;
                        $arResponse['in_sale'] = $inSale;
                    } else {

                        $arResponse['errors'] = $this->arResult['CODE_ERRORS'];
                        $arResponse['system_errors_messages'] = $this->arResult['SYSTEM_ERRORS_MESSAGES'];
                    }

                    echo Bitrix\Main\Web\Json::encode($arResponse);

                    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
                    die();
                } else {

                    if (empty($this->arResult['CODE_ERRORS'])) {
                        LocalRedirect(str_replace("#ORDER_ID#", $ORDER_ID, $this->arParams['ORDER_DETAIL_PAGE']));
                    }
                }
            }

            $this->IncludeComponentTemplate();

            if ($this->arParams['INCLUDE_BOOTSTRAP'] == 'Y') {
                $GLOBALS['APPLICATION']->AddHeadString('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">');
            }
        } catch (\Exception $e) {

            ShowError($e->getMessage());
        }
    }

}

/** Класс для временной смены настроек модулей */
class OptionTmpChanger extends \Bitrix\Main\Config\Option {

    public static function changeOption(string $module, string $option, string $value) {

        parent::$options['-'][$module][$option] = $value;
    }

}
