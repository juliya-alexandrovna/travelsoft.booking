<?php

use travelsoft\booking\stores\Orders;
use travelsoft\booking\stores\Tourists;
use travelsoft\booking\Settings;

/**
 * Класс списка заказов
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
class TravelsoftTouristsForm extends CBitrixComponent {

    /**
     * @var array
     */
    public $fields = array(
        'ID' => array(),
        'UF_NAME' => array('required' => true),
        'UF_NAME_LAT' => array('required' => true),
        'UF_LAST_NAME' => array('required' => true),
        'UF_LAST_NAME_LAT' => array('required' => true),
        'UF_REG_ADDRESS' => array('required' => false),
        'UF_SECOND_NAME' => array(),
        'UF_PASS_SERIES' => array('required' => true),
        'UF_PASS_PERNUM' => array('required' => false),
        'UF_PASS_NUMBER' => array('required' => true),
        'UF_PASS_ISSUED_BY' => array('required' => true),
        'UF_PASS_DATE_ISSUE' => array('required' => true),
		//        'UF_PASS_ACTEND' => array('required' => true),
		//        'UF_CITIZENSHIP' => array('required' => true),
        'UF_BIRTHDATE' => array('required' => true),
		//        'UF_BIRTHCOUNTRY' => array('required' => true),
		//        'UF_BIRTHCITY' => array('required' => true),
            'UF_NEED_VISA' => array(),
            'UF_NEED_INSUR' => array(),
//            'UF_HAVE_VISA' => array(), 
//            'UF_VISA_DATE_FROM' => array(), 
//            'UF_VISA_DATE_TO' => array(), 
        'UF_MALE' => array('required' => true),
        'UF_FILE' => array('required' => false)
    );

    /**
     * Обработка входных параметров компонента
     */
    public function prepareParameters() {

        if (!Bitrix\Main\Loader::includeModule('travelsoft.booking')) {

            throw new Exception('Модуль travelsoft.booking не найден');
        }

        if (!$GLOBALS['USER']->IsAuthorized()) {

            throw new Exception('Добавление/редактирование туристов доступно только для зарегистрированного пользователя');
        }

        $this->arParams['ORDER_ID'] = intVal($this->arParams['ORDER_ID']);
        if ($this->arParams['ORDER_ID'] <= 0) {

            throw new Exception('Не указан номер путевки');
        }

        $arFilter = array('ID' => $this->arParams['ORDER_ID']);

        if (!$GLOBALS['USER']->IsAdmin()) {

            $arFilter['UF_USER_ID'] = $GLOBALS['USER']->GetID();
        }

        if ($this->arParams['ADDITING_ALLOWED_DAYS'] <= 0) {
            $this->arParams['ADDITING_ALLOWED_DAYS'] = 14;
        }

        $this->arResult['ORDER'] = current(Orders::get(array('filter' => $arFilter,
                    'select' => array('ID', 'UF_ADULTS', 'UF_CHILDREN', 'UF_TOURISTS_ID', 'UF_DATE_FROM'))));

        if ($this->arResult['ORDER']['ID'] <= 0) {

            throw new Exception('Путевка с ID="' . $this->arParams['ORDER_ID'] . '" не найдена');
        }

        $this->arResult['PEOPLE_COUNT'] = $this->arResult['ORDER']['UF_ADULTS'] + $this->arResult['ORDER']['UF_CHILDREN'];
    }

    /**
     * Проверка полей туриста
     * @param array $arTourist
     * @return array
     */
    public function checkingTourist(array $arTourist): array {

        $arErrors = array();

        foreach ($this->fields as $fieldName => $arField) {

            if ($arField['required']) {

                if (
                        ($fieldName == 'UF_PASS_DATE_ISSUE' ||
                        $fieldName == "UF_PASS_ACTEND" ||
                        $fieldName == 'UF_BIRTHDATE') &&
                        preg_match("#^\d{2}\.\d{2}\.\d{4}$#", $arTourist[$fieldName]) === 1
                ) {

                    continue;
                } elseif (strlen($arTourist[$fieldName]) > 0) {

                    continue;
                }

                $arErrors[] = 'WRONG_' . $fieldName;
            }
        }

        return $arErrors;
    }

    /**
     * Обработка формы добавления/редактирования туристов
     * @global type $USER_FIELD_MANAGER
     */
    public function processingForm() {

        global $USER_FIELD_MANAGER;

        $this->arResult['ERRORS'] = array();

        if (
                strlen($_POST['SAVE']) > 0 &&
                bitrix_sessid_post() &&
                $_SERVER['REQUEST_METHOD'] === 'POST' &&
                is_array($_POST['TOURISTS']) &&
                !empty($_POST['TOURISTS']) &&
                count($_POST['TOURISTS']) <= $this->arResult['PEOPLE_COUNT']
        ) {

            $this->arResult['FORM_REQUEST'] = true;

            $arSavedTourits = array();
            foreach ($_POST['TOURISTS'] as $key => $arTourist) {

                $arLocalErrors = $this->checkingTourist($arTourist);

                $result = false;

                if (empty($arLocalErrors)) {

                    $arSave = array();

                    $USER_FIELD_MANAGER->EditFormAddFields('HLBLOCK_' . Settings::touristsStoreId(), $arSave, array('FORM' => $arTourist, 'FILES' => $this->getFile(intVal($key))));

                    if (!$GLOBALS['USER']->IsAdmin()) {

                        $arSave['UF_USER_ID'] = $GLOBALS['USER']->GetID();
                    }

                    if ($arTourist['ID'] > 0) {

                        $arTourist['ID'] = intVal($arTourist['ID']);

                        $result = Tourists::update($arTourist['ID'], $arSave);

                        if ($result) {

                            $arSavedTourits[$key] = $arTourist['ID'];
                        }
                    } else {

                        $result = Tourists::add($arSave);

                        if ($result) {

                            $arSavedTourits[$key] = $result;
                        }
                    }
                } else {

                    $this->arResult['ERRORS'][$key] = $arLocalErrors;

                    if (in_array($arTourist['ID'], $this->arResult['ORDER']['UF_TOURISTS_ID'])) {

                        $arSavedTourits[$key] = $arTourist['ID'];
                    }
                }

                if (!$result) {

                    $this->arResult['ERRORS'][$key][] = 'TOURIST_SAVE_ERROR';
                }
            }

            Orders::update($this->arResult['ORDER']['ID'], array('UF_TOURISTS_ID' => $arSavedTourits));

            $this->arResult['ORDER']['UF_TOURISTS_ID'] = $arSavedTourits;

            if (empty($this->arResult['ERRORS'])) {

                $_SESSION['__TRAVELSOFT']['SUCCESS_TOURISTS_EDIT_FORM'] = true;

                LocalRedirect($GLOBALS['APPLICATION']->GetCurPageParam());
            }
        }
    }

    /**
     * Данные по прикрепленному файлу по туристу
     * @param int $index
     * @return array
     */
    public function getFile(int $index): array {

        if ('' != $_FILES['TOURISTS']['name'][$index]['UF_FILE']) {
            return array(
                'UF_FILE' => array(
                    'name' => $_FILES['TOURISTS']['name'][$index]['UF_FILE'],
                    'type' => $_FILES['TOURISTS']['type'][$index]['UF_FILE'],
                    'tmp_name' => $_FILES['TOURISTS']['tmp_name'][$index]['UF_FILE'],
                    'error' => $_FILES['TOURISTS']['error'][$index]['UF_FILE'],
                    'size' => $_FILES['TOURISTS']['size'][$index]['UF_FILE'],
                )
            );
        }

        return array();
    }

    /**
     * Подготовка данных формы
     */
    public function prepareFormData() {

        global $USER_FIELD_MANAGER;

        $arTourists = array();

        $arSelect = $this->fields;

        if (!empty($this->arResult['ORDER']['UF_TOURISTS_ID'])) {

            $arTourists = $this->getTouristsByFilter(array('ID' => $this->arResult['ORDER']['UF_TOURISTS_ID']));
        }

        for ($i = 0; $i < $this->arResult['PEOPLE_COUNT']; $i++) {

            $arReadyData = $arTourists[$this->arResult['ORDER']['UF_TOURISTS_ID'][$i]];

            if ($_POST['TOURISTS'][$i]) {

                $arReadyData = $_POST['TOURISTS'][$i];
            }

            $this->arResult['FORM_DATA'][$i] = array_filter(
                    $USER_FIELD_MANAGER->getUserFieldsWithReadyData('HLBLOCK_' . Settings::touristsStoreId(), $arReadyData, LANGUAGE_ID), function ($arData) use ($arSelect) {
                return isset($arSelect[$arData['FIELD_NAME']]);
            });
            
            array_walk($this->arResult['FORM_DATA'][$i], function (&$arItem) use ($arSelect, $arReadyData) {

                if ($arSelect[$arItem['FIELD_NAME']]['required']) {
                    $arItem['required'] = true;
                }

                if ($arItem['FIELD_NAME'] === 'UF_FILE' && $arItem['VALUE'] > 0) {
                    $arItem['DETAILS'] = $arReadyData['DETAILS_BY_FILE'];
                }
            });

            $this->arResult['FORM_DATA'][$i] = array_merge(array('ID' => array('VALUE' => $arTourists[$this->arResult['ORDER']['UF_TOURISTS_ID'][$i]]['ID'], 'EDIT_FORM_LABEL' => 'Выбрать из списка')), $this->arResult['FORM_DATA'][$i]);
        }

        $arFilter = array();

        if (!$GLOBALS['USER']->IsAdmin()) {

            $arFilter = array('UF_USER_ID' => $GLOBALS['USER']->GetID());
        }

        $this->arResult['TOURISTS'] = $this->getTouristsByFilter($arFilter);
    }

    /**
     * Проверка на возможность внесение информации по туристам
     * @return bool
     */
    public function additingAllowed(): bool {
        
        return ((MakeTimeStamp($this->arResult['ORDER']['UF_DATE_FROM']) - time())/86400) > $this->arParams['ADDITING_ALLOWED_DAYS'];
    }

    /**
     * Получение массива туристов по фильтру
     * @param array $arFilter
     * @return array
     */
    public function getTouristsByFilter(array $arFilter = array()): array {

        return Tourists::get(array('filter' => $arFilter, 'select' => array_keys($this->fields)), true, function (&$arItem) {

                    $arDateFields = array(
                        "UF_PASS_DATE_ISSUE", "UF_PASS_ACTEND", "UF_BIRTHDATE"
                    );

                    foreach ($arDateFields as $field) {

                        if ($arItem[$field]) {

                            $arItem[$field] = $arItem[$field]->toString();
                        }
                    }

                    if ($arItem['UF_FILE'] > 0) {
                        $arItem['DETAILS_BY_FILE'] = CFile::GetFileArray($arItem['UF_FILE']);
                    }
                });
    }

    /**
     * component body
     */
    public function executeComponent() {

        try {
            
            $this->prepareParameters();
            
            $this->arResult['ADDITING_ALLOWED'] = $this->additingAllowed();
            
            if ($this->arResult['ADDITING_ALLOWED']) {

                $this->processingForm();
                
            }
            
            $this->prepareFormData();

            $this->IncludeComponentTemplate();
        } catch (\Exception $e) {

            ShowError($e->getMessage());
        }
    }

}
