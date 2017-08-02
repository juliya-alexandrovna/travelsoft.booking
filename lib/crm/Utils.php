<?php

namespace travelsoft\booking\crm;

use travelsoft\booking\stores\Statuses;
use travelsoft\booking\adapters\CurrencyConverter;
use travelsoft\booking\stores\Orders;
use travelsoft\booking\stores\Users;
use travelsoft\booking\Settings;
use \travelsoft\booking\stores\UserGroups;

/*
 * Функционал для страниц crm
 */

class Utils {

    /**
     * Определяет права доступа к CRM
     * @global type $USER
     * @return bool
     */
    public static function access(): bool {

        global $USER;

        $access = false;
        if ($USER->IsAdmin()) {

            $access = true;
        } else {

            $allowGroups = array(
                Settings::managersUGroup()
            );
            $arUserGroups = $USER->GetUserGroupArray();

            foreach ($arUserGroups as $groupId) {

                if (in_array($groupId, $allowGroups)) {
                    $access = true;
                    break;
                }
            }
        }

        return $access;
    }

    /**
     * Отправка json-строки
     * @global \CMain $APPLICATION
     * @param string $body
     */
    public static function sendJsonResponse(string $body) {

        global $APPLICATION;

        header('Content-Type: application/json');

        $APPLICATION->RestartBuffer();

        ob_start();

        echo $body;

        die();
    }

    /**
     * Возвращает пользовательские настройки дял отображения
     * @param array $userSettings
     * @return array
     */
    public static function getPreparedUserSettings(array $userSettings): array {

        $result = array();

        foreach ($userSettings as $us) {

            $result[$us['UF_ELEMENT_ID']] = \travelsoft\booking\Utils::sta($us['UF_SFIELDS']);
        }

        return $result;
    }

    /**
     * Обрабатывает запрос от формы изменения цен и квот и возвращает результат
     * @param array $request
     * @return array
     */
    public static function processPriceAndQuotasFormRequest(array $request): array {

        $req = $request;

        $response = array();

        $dbTour = current(\travelsoft\booking\stores\Tours::get(array(
                    'filter' => array('ID' => $req['tourid']),
                    'select' => array('ID')
        )));

        $dbTour['ID'] = intVal($dbTour['ID']);

        if ($dbTour['ID'] > 0) {

            # обработка квот
            if ($req['quotas']) {

                foreach ($req['quotas'] as $timestamp => $value) {

                    $response['quotas'][$timestamp] = self::_processQuotas($timestamp, intVal($value), $dbTour['ID']);
                }
            }

            if ($req['duration']) {

                foreach ($req['duration'] as $timestamp => $value) {

                    $response['duration'][$timestamp] = self::_processDuration($timestamp, intVal($value), $dbTour['ID']);
                }
            }

            # обработка stop sale
            if ($req['stop_sale']) {

                foreach ($req['stop_sale'] as $timestamp => $value) {

                    $response['stop_sale'][$timestamp] = self::_processStopSale($timestamp, boolval($value), $dbTour['ID']);
                }
            }

            # обработка цен
            if ($req['prices']) {

                foreach ($req['prices'] as $ptid => $value) {

                    foreach ($value as $timestamp => $vvalue) {

                        $response['prices'][$ptid][$timestamp] = self::_processPrices($timestamp, $ptid, (float) $vvalue, $dbTour['ID']);
                    }
                }
            }

            # обработка запроса на массовое редактирование
            if ($req['mass_edit']) {

                $me = $req['mass_edit'];

                if (!empty($me['unix_dates'])) {

                    if ($me['quotas']) {

                        foreach ($me['unix_dates'] as $timestamp) {

                            $response['quotas'][$timestamp] = self::_processQuotas($timestamp, intVal($me['quotas']['value']), $dbTour['ID']);
                        }
                    }

                    if ($me['duration']) {

                        foreach ($me['unix_dates'] as $timestamp) {

                            $response['duration'][$timestamp] = self::_processDuration($timestamp, intVal($me['duration']['value']), $dbTour['ID']);
                        }
                    }

                    if ($me['stop_sale']) {

                        foreach ($me['unix_dates'] as $timestamp) {

                            $response['stop_sale'][$timestamp] = self::_processStopSale($timestamp, boolval($me['stop_sale']['value']), $dbTour['ID']);
                        }
                    }

                    if ($me['prices']) {

                        foreach ($me['unix_dates'] as $timestamp) {

                            foreach ($me['prices'] as $ptid => $value) {

                                $response['prices'][$ptid][$timestamp] = self::_processPrices($timestamp, intVal($ptid), (float) $value, $dbTour['ID']);
                            }
                        }
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Обрабатывает запрос на изменение квот и возвращает результат
     * @param string $timestamp
     * @param int $value
     * @param int $tourid
     * @return array
     */
    public static function _processQuotas(string $timestamp, int $value, int $tourid): array {

        $result = $dbQuota = array();

        $dbQuota = current(\travelsoft\booking\stores\Quotas::get(array(
                    'filter' => array(
                        'UF_UNIX_DATE' => $timestamp,
                        'UF_SERVICE_ID' => $tourid
                    ),
                    'select' => array('ID', 'UF_SOLD_NUMBER')
        )));

        if ($dbQuota['ID'] > 0) {

            if ($value <= 0) {

                \travelsoft\booking\stores\Quotas::delete($dbQuota['ID']);
                $result = array(
                    'quota_value' => null,
                    'sold_value' => 0,
                    'onsale_value' => 0
                );
            } else {

                \travelsoft\booking\stores\Quotas::update($dbQuota['ID'], array('UF_QUOTA' => $value));
                $onsale = $value - $dbQuota['UF_SOLD_NUMBER'];
                $result = array(
                    'quota_value' => $value,
                    'sold_value' => $dbQuota['UF_SOLD_NUMBER'],
                    'onsale_value' => $onsale >= 0 ? $onsale : 0
                );
            }
        } else {

            if ($value > 0) {

                $id = \travelsoft\booking\stores\Quotas::add(array(
                            'UF_QUOTA' => $value,
                            'UF_SERVICE_ID' => $tourid,
                            'UF_SOLD_NUMBER' => 0,
                            'UF_UNIX_DATE' => $timestamp,
                            'UF_DATE' => date('d.m.Y', $timestamp)
                ));

                $result = array(
                    'quota_value' => $value,
                    'sold_value' => 0,
                    'onsale_value' => 0
                );
            }
        }

        return $result;
    }

    /**
     * Обрабатывает запрос на изменение квот и возвращает результат
     * @param string $timestamp
     * @param int $value
     * @param int $tourid
     * @return int
     */
    public static function _processDuration(string $timestamp, int $value, int $tourid): int {

        $result = 0;

        $dbDuration = current(\travelsoft\booking\stores\Duration::get(array(
                    'filter' => array(
                        'UF_UNIX_DATE' => $timestamp,
                        'UF_SERVICE_ID' => $tourid
                    ),
                    'select' => array('ID', 'UF_DURATION')
        )));

        if ($dbDuration['ID'] > 0) {

            if ($value <= 0) {

                \travelsoft\booking\stores\Duration::delete($dbDuration['ID']);
                $result = 0;
            } else {

                \travelsoft\booking\stores\Duration::update($dbDuration['ID'], array('UF_DURATION' => $value));
                $result = $value;
            }
        } else {

            if ($value > 0) {

                $id = \travelsoft\booking\stores\Duration::add(array(
                            'UF_DURATION' => $value,
                            'UF_SERVICE_ID' => $tourid,
                            'UF_UNIX_DATE' => $timestamp,
                            'UF_DATE' => date('d.m.Y', $timestamp)
                ));

                $result = $value;
            }
        }

        return $result;
    }

    /**
     * Обрабатывает запрос на изменение stop sale и возвращает результат
     * @param string $timestamp
     * @param bool $value
     * @param int $tourid
     * @return int
     */
    public static function _processStopSale(string $timestamp, bool $value, int $tourid): int {

        $result = 0;

        $dbQuota = current(\travelsoft\booking\stores\Quotas::get(array(
                    'filter' => array(
                        'UF_UNIX_DATE' => $timestamp,
                        'UF_SERVICE_ID' => $tourid
                    ),
                    'select' => array('ID')
        )));

        if ($dbQuota['ID'] > 0) {

            if ($value) {

                \travelsoft\booking\stores\Quotas::update($dbQuota['ID'], array('UF_STOP' => 1));
                $result = 1;
            } else {

                \travelsoft\booking\stores\Quotas::update($dbQuota['ID'], array('UF_STOP' => 0));
                $result = 0;
            }
        }

        return $result;
    }

    /**
     * Обрабатывает запрос на изменение цен и возвращает результат
     * @param string $timestamp
     * @param int $ptid
     * @param float $value
     * @param int $tourid
     * @return float
     */
    public static function _processPrices(string $timestamp, int $ptid, float $value, int $tourid): float {

        $result = 0;

        $dbPrice = current(\travelsoft\booking\stores\Prices::get(array(
                    'filter' => array(
                        'UF_SERVICE_ID' => $tourid,
                        'UF_UNIX_DATE' => $timestamp,
                        'UF_PRICE_TYPE_ID' => $ptid
                    ),
                    'select' => array('ID')
        )));

        if ($dbPrice['ID']) {

            if (intVal($value) <= 0) {

                \travelsoft\booking\stores\Prices::delete($dbPrice['ID']);
                $result = 0;
            } else {

                \travelsoft\booking\stores\Prices::update($dbPrice['ID'], array('UF_GROSS' => $value));
                $result = $value;
            }
        } else {

            if ($value > 0) {

                \travelsoft\booking\stores\Prices::add(array(
                    'UF_SERVICE_ID' => $tourid,
                    'UF_UNIX_DATE' => $timestamp,
                    'UF_PRICE_TYPE_ID' => $ptid,
                    'UF_DATE' => new \Bitrix\Main\Type\DateTime(date('d.m.Y', $timestamp)),
                    'UF_GROSS' => $value
                ));
                $result = $value;
            }
        }

        return $result;
    }

    /**
     * Обработка запроса формы настроек
     * @param array $request
     * @param array $userSettings
     * @return array
     */
    public static function processSettingsFromRequest(array $request, array $userSettings): array {

        $result = $errors = array();

        if (strlen($request['show']) > 0) {

            if (empty(\travelsoft\booking\stores\Tours::getById($request['tourid']))) {

                $errors[] = 'Недопустимый id тура';
            }

            $dates = array_unique(array_filter($request['dates'], function ($date) {

                        if (preg_match('#^\d{2}\.\d{2}\.\d{4}$#', $date) &&
                                MakeTimeStamp($date) >= MakeTimeStamp(date('d.m.Y'))) {

                            return true;
                        }

                        return false;
                    }));

            usort($dates, function ($a, $b) {

                return strtotime($a) > strtotime($b);
            });

            if (!is_array($dates) || empty($dates)) {

                $errors[] = 'Установите даты заездов для заведения цен';
            }

            $dbPriceTypes = \travelsoft\booking\stores\PriceTypes::get();

            $priceTypes = array_unique(array_filter($request['pricetypes'], function ($itemid) use ($dbPriceTypes) {

                        return isset($dbPriceTypes[$itemid]);
                    }));

            if (!is_array($priceTypes) || empty($priceTypes)) {

                $errors[] = 'Выберите типы цен';
            }

            if (empty($errors)) {

                $settings = array(
                    'tourid' => $request['tourid'],
                    'pricetypes' => $priceTypes,
                    'dates' => $dates
                );

                $cs = array();
                foreach ($userSettings as $s) {

                    if ($s['UF_ELEMENT_ID'] == $request['tourid']) {

                        $cs = $s;
                    }
                }

                if ($cs['ID'] > 0) {

                    $UF_SFIELDS = \travelsoft\booking\Utils::ats($settings);
                    # обновляем настройки в базе данных
                    \travelsoft\booking\crm\stores\Settings::update($cs['ID'], array(
                        'UF_SFIELDS' => $UF_SFIELDS
                    ));

                    $userSettings[$cs['ID']] = array(
                        'ID' => $cs['ID'],
                        'UF_USER_ID' => $GLOBALS['USER']->GetID(),
                        'UF_ELEMENT_ID' => $request['tourid'],
                        'UF_SFIELDS' => \travelsoft\booking\Utils::ats($settings)
                    );
                } else {

                    $arAdd = array(
                        'UF_USER_ID' => $GLOBALS['USER']->GetID(),
                        'UF_ELEMENT_ID' => $request['tourid'],
                        'UF_SFIELDS' => \travelsoft\booking\Utils::ats($settings)
                    );
                    # сохраняем настройки в базе данных
                    $csid = \travelsoft\booking\crm\stores\Settings::add($arAdd);

                    $arAdd['ID'] = $csid;
                    $userSettings[$csid] = $arAdd;
                }
            }

            $preparedUserSettings = self::getPreparedUserSettings($userSettings);

            $result = array(
                'errors' => $errors,
                'settings' => $preparedUserSettings,
                'responseBody' => self::getPriceAndQuotasTableAsHtml((array) $preparedUserSettings[$request['tourid']])
            );
        }

        return $result;
    }

    /**
     * Возвращает html таблицы цен и квот
     * @param array $parameters
     * @return string
     */
    public static function getPriceAndQuotasTableAsHtml(array $parameters): string {

        global $APPLICATION;

        $content = '';

        if (!empty($parameters['dates']) && !empty($parameters['pricetypes'])) {

            $header = array(
                array(
                    "id" => "id",
                    "align" => "center",
                    "content" => '<input type="hidden" value="' . $parameters['tourid'] . '" name="prices_and_quotas[tourid]">',
                    "default" => true
            ));

            $timestamps = array_map(function ($date) {
                return strtotime($date);
            }, $parameters['dates']);

            foreach ($parameters['dates'] as $key => $date) {

                $header[] = array(
                    "id" => $timestamps[$key],
                    "align" => "center",
                    "content" => $date,
                    "default" => true
                );
            }

            $tableId = "add-pq";
            $list = new \CAdminList($tableId, null);

            $list->AddHeaders($header);

            self::_setQuotasSoldStopSaleRows($timestamps, $list, $parameters);
            self::_setDurationRows($timestamps, $list, $parameters);
            self::_setPriceTypesRow($timestamps, $list, $parameters);

            ob_start();

            $list->Display();

            $content = ob_get_contents();

            ob_end_clean();

            $content = preg_replace('#(onsubmit=\"(.*)\;\")#', 'onsubmit="return CRMUtils.priceAndQuotasFormAjaxSubmit(this)"', $content);
            $content = preg_replace('#\?mode=frame#', '', $content);
        }

        return $content;
    }

    /**
     * @param array $timestamps
     * @param \CAdminList $list
     * @param array $parameters
     */
    public static function _setPriceTypesRow(array $timestamps, & $list, array $parameters) {

        $spt = $parameters['pricetypes'];

        $priceTypes = array_filter(\travelsoft\booking\stores\PriceTypes::get(), function ($item) use ($spt) {

            return in_array($item['ID'], $spt);
        });

        $prices = array();
        foreach (\travelsoft\booking\stores\Prices::get(array('filter' => array(
                'UF_SERVICE_ID' => $parameters['tourid'],
                '><UF_DATE' => array(
                    new \Bitrix\Main\Type\DateTime($parameters['dates'][0]),
                    new \Bitrix\Main\Type\DateTime($parameters['dates'][count($parameters['dates']) - 1])
                )
    ))) as $price) {
            $prices[$price['UF_PRICE_TYPE_ID']][$price['UF_UNIX_DATE']] = $price;
        }

        foreach ($priceTypes as $priceType) {

            $rowData = array();

            $formElements = self::_getMassEditHiddenFormElements($parameters);

            $formElements[] = array(
                'label' => $priceType["UF_NAME"],
                'element' => 'input',
                'type' => 'text',
                'value' => '',
                'name' => 'prices_and_quotas[mass_edit][prices][' . $priceType['ID'] . ']'
            );

            $rowData["id"] = "<b>" . $priceType["UF_NAME"] . "</b><br>[<a href='javascript: CRMUtils.initPopupForm(" . self::_getMassEditPopupJsonSettings(self::_getMassEditPopupFormHtml($formElements, 'prices-mass-edit-' . $priceType['ID']), 'prices-mass-edit-' . $priceType['ID']) . ")'>Изменить</a>]";

            foreach ($timestamps as $timestamp) {

                $rowData[$timestamp] = $prices[$priceType['ID']][$timestamp]['UF_GROSS'] > 0 ? (float) $prices[$priceType['ID']][$timestamp]['UF_GROSS'] : null;
            }

            self::_setViewField($list->addRow($rowData["id"], $rowData), $rowData, '<input onblur="CRMUtils.triggerPriceAndQuotasFormAjaxSubmit(this);" type="text" name="prices_and_quotas[prices][' . $priceType['ID'] . '][#key#]" value="#value#" size="6">');
        }
    }

    /**
     * Возвращает контент формы массового редактирования
     * @global \CMain $APPLICATION
     * @param array $formElements
     * @param string $formid
     * @return string
     */
    public static function _getMassEditPopupFormHtml(array $formElements, string $formid): string {

        global $APPLICATION;


        $content = '<form id="' . $formid . '" method="post" action="' . $APPLICATION->GetCurPage("lang=" . LANG, array('lang')) . '">';

        $content .= '<input name="sessid" value="' . bitrix_sessid() . '" type="hidden">';

        foreach ($formElements as $formElement) {

            switch ($formElement['element']) {

                case 'input':

                    if ($formElement['label']) {

                        $content .= '<label for="' . $formElement['name'] . '"><b>' . $formElement['label'] . '</b>: </label>';
                    }
                    $content .
                            $content .= '<input ' . ($formElement['checked'] ? 'checked=""' : '') . ' type="' . $formElement['type'] . '" value="' . $formElement['value'] . '" name="' . $formElement['name'] . '">';
                    break;

                case 'select':

                    if ($formElement['label']) {

                        $content .= '<label for="' . $formElement['name'] . '"><b>' . $formElement['label'] . '</b>: </label>';
                    }

                    $content .= '<select name="' . $formElement['name'] . '">';

                    foreach ($formElement['value'] as $params) {

                        $content .= '<option value="' . $params['value'] . '" ' . ($params['selected'] ? 'selected=""' : '') . '>' . $params['title'] . '</option>';
                    }

                    $content .= '</selected>';

                    break;
            }
        }

        $content .= '</form>';

        return $content;
    }

    /**
     * @param array $timestamps
     * @param type $list
     * @param array $parameters
     */
    public static function _setDurationRows(array $timestamps, & $list, array $parameters) {

        $durations = array();
        foreach (\travelsoft\booking\stores\Duration::get(
                array('filter' => array(
                        'UF_SERVICE_ID' => $parameters['tourid'], '><UF_DATE' => array(
                            new \Bitrix\Main\Type\DateTime($parameters['dates'][0]),
                            new \Bitrix\Main\Type\DateTime($parameters['dates'][count($parameters['dates']) - 1])
                        )))
        ) as $duration) {
            $durations[$duration['UF_UNIX_DATE']] = $duration;
        }

        $durationRowData = array();

        $formElements = self::_getMassEditHiddenFormElements($parameters);

        $formElements[] = array(
            'label' => "Продолжительность (дней)",
            'element' => 'input',
            'type' => 'text',
            'value' => '',
            'name' => 'prices_and_quotas[mass_edit][duration][value]'
        );

        $durationRowData["id"] = "<b>Продолжительность (дней)</b><br>[<a href='javascript: CRMUtils.initPopupForm(" . self::_getMassEditPopupJsonSettings(self::_getMassEditPopupFormHtml($formElements, 'duration-mass-edit'), 'duration-mass-edit') . ")'>Изменить</a>]";

        foreach ($timestamps as $timestamp) {

            $durationRowData[$timestamp] = $durations[$timestamp]['UF_DURATION'] > 0 ? intVal($durations[$timestamp]['UF_DURATION']) : null;
        }

        self::_setViewField($list->addRow($durationRowData["id"], $durationRowData), $durationRowData, '<input onblur="CRMUtils.triggerPriceAndQuotasFormAjaxSubmit(this);" type="text" name="prices_and_quotas[duration][#key#]" value="#value#" size="3">');
    }

    /**
     * @param array $timestamps
     * @param \CAdminList $list
     * @param array $parameters
     */
    public static function _setQuotasSoldStopSaleRows(array $timestamps, & $list, array $parameters) {

        $quotas = array();
        foreach (\travelsoft\booking\stores\Quotas::get(
                array('filter' => array(
                        'UF_SERVICE_ID' => $parameters['tourid'], '><UF_DATE' => array(
                            new \Bitrix\Main\Type\DateTime($parameters['dates'][0]),
                            new \Bitrix\Main\Type\DateTime($parameters['dates'][count($parameters['dates']) - 1])
                        )))
        ) as $quota) {
            $quotas[$quota['UF_UNIX_DATE']] = $quota;
        }

        $quotasRowData = $stopSalesRowData = $soldRowData = $onSaleRowData = array();

        $formElements = self::_getMassEditHiddenFormElements($parameters);

        $formElements[] = array(
            'label' => "Квоты",
            'element' => 'input',
            'type' => 'text',
            'value' => '',
            'name' => 'prices_and_quotas[mass_edit][quotas][value]'
        );

        $quotasRowData["id"] = "<b>Квоты</b><br>[<a href='javascript: CRMUtils.initPopupForm(" . self::_getMassEditPopupJsonSettings(self::_getMassEditPopupFormHtml($formElements, 'quotas-mass-edit'), 'quotas-mass-edit') . ")'>Изменить</a>]";

        array_pop($formElements);
        $formElements[] = array(
            'label' => "Stop sale",
            'element' => 'select',
            'value' => array(
                array(
                    'value' => 0,
                    'title' => 'Нет'
                ),
                array(
                    'value' => 1,
                    'title' => 'Да'
                )
            ),
            'name' => 'prices_and_quotas[mass_edit][stop_sale][value]'
        );

        $stopSalesRowData["id"] = "<b>Stop sale</b><br>[<a href='javascript: CRMUtils.initPopupForm(" . self::_getMassEditPopupJsonSettings(self::_getMassEditPopupFormHtml($formElements, 'stop-sale-mass-edit'), 'stop-sale-mass-edit') . ")'>Изменить</a>]";

        $soldRowData["id"] = "Кол-во проданых";

        $onSaleRowData["id"] = "В продаже";

        foreach ($timestamps as $timestamp) {

            $quotasRowData[$timestamp] = $quotas[$timestamp]['UF_QUOTA'] > 0 ? intVal($quotas[$timestamp]['UF_QUOTA']) : null;
            $stopSalesRowData[$timestamp] = boolval($quotas[$timestamp]['UF_STOP']);
            $soldRowData[$timestamp] = $quotas[$timestamp]['UF_SOLD_NUMBER'] > 0 ? intVal($quotas[$timestamp]['UF_SOLD_NUMBER']) : 0;
            $onSaleRowData[$timestamp] = $quotasRowData[$timestamp] - $soldRowData[$timestamp];
            if ($onSaleRowData[$timestamp] < 0) {

                $onSaleRowData[$timestamp] = 0;
            }
        }

        self::_setViewField($list->addRow($quotasRowData["id"], $quotasRowData), $quotasRowData, '<input onblur="CRMUtils.triggerPriceAndQuotasFormAjaxSubmit(this);" type="text" name="prices_and_quotas[quotas][#key#]" value="#value#" size="3">');
        self::_setViewField($list->addRow($soldRowData["id"], $soldRowData), $soldRowData, '<span id="sold-#key#">#value#</span>');
        self::_setViewField($list->addRow($onSaleRowData["id"], $onSaleRowData), $onSaleRowData, '<span id="on-sale-#key#">#value#</span>');
        self::_setViewField($list->addRow($stopSalesRowData["id"], $stopSalesRowData), $stopSalesRowData, '<select onchange="CRMUtils.triggerPriceAndQuotasFormAjaxSubmit(this);" name="prices_and_quotas[stop_sale][#key#]"><option #selected-no# value="0">Нет</option><option #selected-yes# value="1">Да</option></select>');
    }

    /**
     * Возвращает массив скрытых полей формы массового редактирования цен и квот
     * @param array $parameters
     * @return array
     */
    public static function _getMassEditHiddenFormElements(array $parameters): array {

        foreach (array_map(function ($item) {
            return strtotime($item);
        }, $parameters['dates']) as $timestamp) {

            $formElements[] = array(
                'element' => 'input',
                'type' => 'hidden',
                'value' => $timestamp,
                'name' => 'prices_and_quotas[mass_edit][unix_dates][]'
            );
        }

        $formElements[] = array(
            'element' => 'input',
            'type' => 'hidden',
            'name' => 'prices_and_quotas[tourid]',
            'value' => $parameters['tourid']
        );

        return $formElements;
    }

    public static function _getMassEditPopupJsonSettings(string $content, string $formid): string {

        $massEditPopupJsonSettings = \Bitrix\Main\Web\Json::encode(array(
                    'id' => $formid,
                    'title' => 'Форма массового редактирования',
                    'content' => $content,
                    'height' => 100,
                    'buttons' => array(
                        '<input onclick="return CRMUtils.massEditFormAjaxSubmit(\'' . $formid . '\')" type="button" name="savebtn" value="Сохранить" id="savebtn" class="adm-btn-save">',
                        '[code]BX.CDialog.prototype.btnCancel[code]',
                    )
        ));

        # clear [code]
        $massEditPopupJsonSettings = str_replace('"[code]', '', $massEditPopupJsonSettings);
        $massEditPopupJsonSettings = str_replace('[code]"', '', $massEditPopupJsonSettings);

        return $massEditPopupJsonSettings;
    }

    /**
     * @param \CAdminListRow $row
     * @param array $rowData
     * @param string $template
     */
    public static function _setViewField(&$row, array $rowData, string $template) {

        $row->AddViewField("id", $rowData["id"]);

        unset($rowData["id"]);

        foreach ($rowData as $key => $value) {

            $selected_no = 'selected=""';
            $selected_yes = '';
            if ($value) {

                $selected_no = '';
                $selected_yes = 'selected=""';
            }

            $checked = '';
            if ($value) {
                $checked = 'checked=""';
            }

            $row->AddViewField($key, str_replace(array("#key#", "#value#", "#checked#", "#selected-no#", "#selected-yes#"), array($key, $value, $checked, $selected_no, $selected_yes), $template));
        }
    }

    /**
     * Заполнение полей для отображения в форме добавления/редактирования
     * @param array $arFields
     * @param array $fieldsValues
     */
    public static function fillFields(&$arFields, $fieldsValues) {

        foreach ($fieldsValues as $key => $value) {

            if (isset($arFields[$key])) {

                $arFields[$key]['value'] = $value;
            }
        }
    }

    /**
     * Возвращает html формы по заказу
     * @param int $orderId
     * @return string
     */
    public static function getOrderForm(int $orderId): string {

        $namespace = "ORDER";

        $arFields[$namespace] = self::_getOrderFormFields();
        $dbStatuses = Statuses::get(array('select' => array('ID', 'UF_NAME')), false);
        while ($arStatus = $dbStatuses->fetch()) {

            $arFields[$namespace]['UF_STATUS_ID']['def'][$arStatus['ID']] = $arStatus['UF_NAME'];
        }

        $dbManagers = $GLOBALS['USER']->GetList(($b = 'NAME'), ($o = 'ASC'), array('GROUPS_ID' => array(Settings::managersUGroup())), array('SELECT' => array('ID', 'NAME', 'LAST_NAME', 'LOGIN')));
        while ($arManager = $dbManagers->Fetch()) {

            $name = $arManager['NAME'] . ' ' . $arManager['LAST_NAME'];
            if (strlen($name) <= 1) {
                $name = $arManager['LOGIN'];
            }
            $arFields[$namespace]['UF_MANAGER_ID']['def'][$arManager['ID']] = $name;
        }

        $converter = new CurrencyConverter;
        $arCurrencies = $converter->getListOfCurrency();

        foreach ($arCurrencies as $currency) {

            $arFields[$namespace]['UF_CURRENCY']['def'][$currency] = $currency;
        }
        $arFields[$namespace]['UF_COMMENT'] = array('type' => 'textarea', 'title' => 'Комментарий');

        $arOrder = Orders::getById($orderId);

        if ($arOrder['ID'] > 0) {

            $arFields['ID'] = $arOrder['ID'];
            $arFields[$namespace]['UF_DATE'] = array('type' => 'string', 'title' => 'Дата создания');
            self::fillFields($arFields[$namespace], $arOrder);
        }

        if (!empty($_POST[$namespace])) {

            self::fillFields($arFields, $_POST[$namespace]);
        }

        $inputId = '';
        if ($arFields['ID'] > 0) {

            $inputId .= '<input type="hidden" name="ORDER_ID" value="' . $arFields['ID'] . '">';
        }

        return $inputId . self::_getHtmlFormFields($arFields);
    }

    /**
     * Возвращает html формы клиента
     * @param int $userId
     * @return string
     */
    public static function getClientForm(int $userId): string {

        $arFields = self::_getClientFormFields();

        $arSelect = array_merge(
                array_keys($arFields['CLIENT_TOTAL']), array_keys($arFields['CLIENT_PASSPORT']), array_keys($arFields['CLIENT_AGENT'])
        );

        if ($userId > 0) {

            $arUser = current(Users::get(array(
                        'filter' => array('ID' => $userId),
                        'select' => $arSelect)));

            foreach (UserGroups::get(array(
                'filter' => array('USER_ID' => $arUser['ID']))) as $arGroup) {

                if ($arGroup['GROUP_ID'] == Settings::agentsUGroup()) {
                    $arUser['IS_AGENT'] = 'Y';
                    break;
                }
            }

            foreach ($arFields as $namespace => &$arrFields) {

                self::fillFields($arrFields, $arUser);
            }
        }


        foreach ($arFields as $namespace => &$arrFields) {
            if (!empty($_POST[$namespace])) {
                self::fillFields($arrFields, $_POST[$namespace]);
            }
        }

        $aTabs = array(
            array("DIV" => "CLIENT_TOTAL", "TAB" => 'Общая информация'),
            array("DIV" => "CLIENT_PASSPORT", "TAB" => 'Паспортные данные'),
            array("DIV" => "CLIENT_AGENT", "TAB" => 'Реквизиты компании')
        );

        $childTabControl = new \CAdminViewTabControl("childTabControl", $aTabs);

        ob_start();

        echo '<tr><td colspan="2" align="center">';
        $childTabControl->Begin();

        foreach ($aTabs as $aTab) {
            $childTabControl->BeginNextTab();
            echo '<table width="70%">';

            echo self::_getHtmlFormFields(array($aTab['DIV'] => $arFields[$aTab['DIV']]));

            echo '</table>';
        }

        $childTabControl->End();

        echo '</td></tr>';

        $html = ob_get_clean();

        return $html;
    }

    /**
     * Возвращает html полей формы
     * @param array $arFields
     * @return string
     */
    public static function _getHtmlFormFields(array $arFields): string {

        $html = '';
        foreach ($arFields as $namespace => $arFieldsData) {

            foreach ($arFieldsData as $fieldName => $arDataField) {

                switch ($arDataField['type']) {

                    case 'string':

                        $html .= '<tr>
                                    <td width="40%">' . $arDataField['title'] . '</td>
                                    <td width="60%">' . $arDataField['value'] . '</td>
                                  </tr>';

                        break;

                    case 'date':

                        $html .= '<tr><td width="40%">';
                        if ($arDataField['reqired']) {

                            $html .= '<span class="required">*</span>';
                        }
                        $html .= $arDataField['title'] . '</td>';
                        $html .= '<td width="60%">' . \CAdminCalendar::CalendarDate("[$namespace][$fieldName]", $arDataField['value'], 19, true) . '</td></tr>';

                        break;

                    case 'select':

                        $html .= '<tr><td width="40%">';
                        if ($arDataField['required']) {

                            $html .= '<span class="required">*</span>';
                        }
                        $html .= $arDataField['title'] . '</td>';
                        $html .= '<td width="60%"><select name="[' . $namespace . '][' . $fieldName . ']">';
                        foreach ($arDataField['def'] as $value => $title) {
                            $selected = '';
                            if ($value == $arDataField['value']) {
                                $selected = 'selected=""';
                            }
                            $html .= '<option ' . $selected . ' value="' . $value . '">' . $title . '</option>';
                        }
                        $html .= '</select></td></tr>';

                        break;

                    case 'checkbox':
                    case 'radio':
                        $html .= '<tr><td width="40%">';
                        if ($arDataField['required']) {

                            $html .= '<span class="required">*</span>';
                        }

                        $html .= $arDataField['title'] . '</td>';

                        $checked = '';
                        if ($arDataField['def'] == $arDataField['value']) {

                            $checked = 'checked';
                        }
                        $html .= '<td width="60%">'
                                . '<input ' . $checked . ' type="checkbox" name="[' . $namespace . '][' . $fieldName . ']" value="' . $arDataField['def'] . '">
                                    </td></tr>';

                        break;

                    case 'textarea':

                        $html .= '<tr><td width="40%">';
                        if ($arDataField['required']) {

                            $html .= '<span class="required">*</span>';
                        }
                        $html .= $arDataField['title'] . '</td>';
                        $html .= '<td width="60%"><textarea cols="29" rows="10" name="[' . $namespace . '][' . $fieldName . ']">' . $arDataField['value'] . '</textarea>
                                    </td></tr>';
                        break;

                    case 'hidden':

                        $html .= '<input type="hidden" name="[' . $namespace . '][' . $fieldName . ']" value="' . $arDataField['value'] . '">';
                        break;

                    case 'email':
                    case 'tel':
                    case 'text':
                    default:
                        $type = $arDataField['type'] ? $arDataField['type'] : 'text';
                        $html .= '<tr><td width="40%">';
                        if ($arDataField['required']) {

                            $html .= '<span class="required">*</span>';
                        }
                        $html .= $arDataField['title'] . '</td>';
                        $html .= '<td width="60%"><input type="' . $type . '" name="[' . $namespace . '][' . $fieldName . ']" value="' . $arDataField['value'] . '" size="30" maxlength="100">
                                    </td></tr>';
                }
            }
        }

        return $html;
    }

    /**
     * Поля формы заказа
     * @return array
     */
    public static function _getOrderFormFields(): array {

        $arFields['UF_SERVICE_NAME'] = array('type' => 'text', 'title' => 'Название', 'required' => true);
        $arFields['UF_STATUS_ID'] = array('type' => 'select', 'title' => 'Статус', 'required' => true);
        $arFields['UF_MANAGER_ID'] = array('type' => 'select', 'title' => 'Менеджер', 'def' => array(0 => 'Выберите'));
        $arFields['UF_DEP_CITY'] = array('type' => 'text', 'title' => 'Город отправления');
        $arFields['UF_ARR_CITY'] = array('type' => 'text', 'title' => 'Город прибытия');
        $arFields['UF_SERVICE_TYPE'] = array('type' => 'text', 'title' => 'Тип');
        $arFields['UF_FOOD'] = array('type' => 'text', 'title' => 'Питание');
        $arFields['UF_NUMBER'] = array('type' => 'text', 'title' => 'Номер по заявке');
        $arFields['UF_HOTEL'] = array('type' => 'text', 'title' => 'Проживание');
        $arFields['UF_SERVICES'] = array('type' => 'text', 'title' => 'Услуги');
        $arFields['UF_DATE_FROM'] = array('type' => 'date', 'title' => 'Дата начала', 'required' => true);
        $arFields['UF_DATE_TO'] = array('type' => 'date', 'title' => 'Дата окончания', 'required' => true);
        $arFields['UF_ADULTS'] = array('type' => 'text', 'title' => 'Количество взрослых', 'required' => true);
        $arFields['UF_CHILDREN'] = array('type' => 'text', 'title' => 'Количество детей');
        $arFields['UF_COST'] = array('type' => 'text', 'title' => 'Стоимость', 'required' => true);
        $arFields['UF_CURRENCY'] = array('type' => 'select', 'title' => 'Валюта', 'required' => true);

        return $arFields;
    }

    /**
     * Поля формы клиента
     * @return array
     */
    public static function _getClientFormFields(): array {

        $arFields['CLIENT_TOTAL']['ID'] = array('type' => 'hidden');
        $arFields['CLIENT_TOTAL']['NAME'] = array('type' => 'text', 'title' => 'Имя', 'required' => true);
        $arFields['CLIENT_TOTAL']['SECOND_NAME'] = array('type' => 'text', 'title' => 'Отчество');
        $arFields['CLIENT_TOTAL']['LAST_NAME'] = array('type' => 'text', 'title' => 'Фамилия', 'required' => true);
        $arFields['CLIENT_TOTAL']['EMAIL'] = array('type' => 'email', 'title' => 'Email', 'required' => true);
        $arFields['CLIENT_TOTAL']['PERSONAL_PHONE'] = array('type' => 'text', 'title' => 'Телефон');
        $arFields['CLIENT_TOTAL']['IS_AGENT'] = array('type' => 'checkbox', 'def' => 'Y', 'title' => 'Является агентом');
        $arFields['CLIENT_PASSPORT']['UF_PASS_NUMBER'] = array('type' => 'text', 'title' => 'Номер паспорта');
        $arFields['CLIENT_PASSPORT']['UF_PASS_DATE_ISSUE'] = array('type' => 'text', 'title' => 'Дата выдачи паспорта');
        $arFields['CLIENT_PASSPORT']['UF_PASS_ISSUED_BY'] = array('type' => 'text', 'title' => 'Кем выдан паспорт');
        $arFields['CLIENT_AGENT']['UF_LEGAL_NAME'] = array('type' => 'text', 'title' => 'Юр. название');
        $arFields['CLIENT_AGENT']['UF_LEGAL_ADDRESS'] = array('type' => 'text', 'title' => 'Юр. адрес');
        $arFields['CLIENT_AGENT']['UF_BANK_NAME'] = array('type' => 'text', 'title' => 'Название банка');
        $arFields['CLIENT_AGENT']['UF_BANK_CODE'] = array('type' => 'text', 'title' => 'Код банка');
        $arFields['CLIENT_AGENT']['UF_BANK_ADDRESS'] = array('type' => 'text', 'title' => 'Адрес банка');
        $arFields['CLIENT_AGENT']['UF_CHECKING_ACCOUNT'] = array('type' => 'text', 'title' => 'Расчётный счет');
        $arFields['CLIENT_AGENT']['UF_ACCOUNT_CURRENCY'] = array('type' => 'text', 'title' => 'Валюта счета');
        $arFields['CLIENT_AGENT']['UF_UNP'] = array('type' => 'text', 'title' => 'УНП');
        $arFields['CLIENT_AGENT']['UF_OKPO'] = array('type' => 'text', 'title' => 'OKPO');
        $arFields['CLIENT_AGENT']['UF_ACTUAL_ADDRESS'] = array('type' => 'text', 'title' => 'Фактический адрес');

        return $arFields;
    }

    public static function processingOrderForm(): array {
        
    }

    public static function processingClientForm(): array {

        $arErrors = array();
        $arFields = self::_getClientFormFields();
    }

    public static function processingTouristForm(): array {
        
    }

}
