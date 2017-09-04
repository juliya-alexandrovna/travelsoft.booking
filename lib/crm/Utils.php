<?php

namespace travelsoft\booking\crm;

use travelsoft\booking\stores\Statuses;
use travelsoft\booking\adapters\CurrencyConverter;
use travelsoft\booking\stores\Orders;
use travelsoft\booking\stores\Users;
use travelsoft\booking\stores\Tourists;
use travelsoft\booking\Settings;
use travelsoft\booking\adapters\Mail;
use travelsoft\booking\stores\Tours;
use travelsoft\booking\Utils as SharedUtils;

/*
 * Функционал для страниц crm
 */

class Utils {

    /**
     * ID таблицы списка заказов
     */
    const ORDERS_TABLE_ID = "ORDERS_LIST";

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
     * Вывод формы редактирования
     * @param array $parameters
     */
    public static function showEditForm(array $parameters) {

        echo '<form enctype="multipart/form-data" action="' . $parameters['action'] . '" method="POST" name="' . $parameters['name'] . '" id="' . $parameters['id'] . '">';

        if ($_REQUEST['ID'] > 0) {

            echo '<input name="ID" value="' . intVal($_REQUEST['ID']) . '" type="hidden">';
        }

        echo '<input type="hidden" name="lang" value="' . LANGUAGE_ID . '">';

        echo bitrix_sessid_post();

        $tabControl = new \CAdminTabControl("tabControl", $parameters['tabs']);

        $tabControl->Begin();

        foreach ($parameters['tabs'] as $tab) {

            $tabControl->BeginNextTab();

            echo $tab['content'];
        }

        $tabControl->Buttons();

        foreach ($parameters['buttons'] as $button) {

            $class = $button['class'] ? 'class="' . $button['class'] . '"' : '';

            $id = $button['id'] ? 'id="' . $button['class'] . '"' : '';

            $onclick = $button['onclick'] ? 'onclick="' . $button['onclick'] . '"' : '';

            echo '<input ' . $onclick . ' type="submit" name="' . $button['name'] . '" ' . $id . ' value="' . $button['value'] . '" ' . $class . '>';
        }

        $tabControl->End();

        echo '</form>';
    }

    /**
     * Является ли запрос запросом от формы редактирования
     * @return bool
     */
    public static function isEditFormRequest(): bool {

        return $_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && ($_POST['SAVE'] || $_POST['APPLY']);
    }

    /**
     * Обработка формы редактирования заказа
     * @global \travelsoft\booking\crm\type $USER_FIELD_MANAGER
     * @return array
     */
    public static function processingOrderEditForm(): array {

        global $USER_FIELD_MANAGER;

        $url = 'travelsoft_crm_booking_orders_list.php?lang=' . LANGUAGE_ID;

        if (strlen($_POST['CANCEL']) > 0) {

            LocalRedirect($url);
        }

        $arErrors = array();

        if (self::isEditFormRequest()) {

            $data = array();

            $USER_FIELD_MANAGER->EditFormAddFields('HLBLOCK_' . Settings::ordersStoreId(), $data);

            if ($data['UF_USER_ID'] <= 0) {

                $arErrors[] = 'Не указан клиент';
            }

            $data['UF_TOURISTS_ID'] = array_filter($data['UF_TOURISTS_ID'], function ($item) {

                return $item > 0;
            });

            if (empty($data['UF_TOURISTS_ID'])) {

                $arErrors[] = 'Должен быть указан хотябы один турист';
            }

            if ($data['UF_STATUS_ID'] <= 0) {

                $arErrors[] = 'Не указан статус брони';
            }

            // проверка даты начала на момент создания заказа
            if (!$_REQUEST['ID'] && strlen($data['UF_DATE_FROM']) == 0) {

                $arErrors[] = 'Не указана дата начала';
            }

            $quota = intVal($data['UF_ADULTS'] + $data['UF_CHILDREN']);
            if (!$_REQUEST['ID'] && $quota <= 0) {

                $arErrors[] = 'Укажите количество взрослых или детей';
            }

            // проверка квот на момент создания заказа
            if (!$_REQUEST['ID'] && $data['UF_SERVICE_ID'] > 0 && $quota > 0 &&
                    !SharedUtils::checkQuota($data['UF_SERVICE_ID'], $data['UF_DATE_FROM'], $quota)) {

                $arErrors[] = 'Нет квот для бронирования. Измените параметры и попробуйте снова';
            }

            if (empty($arErrors)) {

                if ($_REQUEST['ID'] > 0) {

                    $ID = intval($_REQUEST['ID']);
                    $data['UF_DATE'] = \travelsoft\booking\adapters\Date::createFromTimetamp(time());
                    $result = Orders::update($ID, $data);
                } else {

                    $result = Orders::add($data);

                    if ($result > 0 && $data['UF_SERVICE_ID'] > 0) {

                        # увеличиваем количество проданных
                        SharedUtils::increaseNumberOfSold($data['UF_SERVICE_ID'], $data['UF_DATE_FROM'], $quota);
                    }
                }

                if ($result) {

                    LocalRedirect($url);
                }
            }
        }

        return array('errors' => $arErrors, 'result' => $result);
    }

    /**
     * HTML полей для формы редактирования заказа
     * @global type $USER_FIELD_MANAGER
     * @param array $data
     * @return string
     */
    public static function getEditOrderFieldsContent(array $data = null): string {

        global $USER_FIELD_MANAGER;

        $arUserFields = $USER_FIELD_MANAGER->getUserFieldsWithReadyData("HLBLOCK_" . Settings::ordersStoreId(), $data, LANGUAGE_ID);

        $isFormRequest = self::isEditFormRequest();

        $content = '';

        foreach ($arUserFields as $arUserField) {

            if (key_exists($arUserField['FIELD_NAME'], $_POST)) {

                $arUserField['VALUE'] = $_POST[$arUserField['FIELD_NAME']];
            }

            switch ($arUserField['FIELD_NAME']) {

                case 'UF_COST':

                    $calculationBtn = '<span id="calculation-btn-area" style="display:none">&nbsp;<a href="javascript:CRMUtils.calculate()">Рассчитать стоимость</a></span>';
                    if ($arUserFields['UF_SERVICE_ID']['VALUE'] > 0) {

                        $calculationBtn = '';
                    }

                    $content .= self::getEditFieldHtml(
                                    $arUserField['EDIT_FORM_LABEL'] . ':', '<input name="' . $arUserField['FIELD_NAME'] . '" value="' . $arUserField['VALUE'] . '" type="text">' . $calculationBtn);

                    break;

                case 'UF_DURATION':

                    $content .= self::getEditFieldHtml(
                                    $arUserField['EDIT_FORM_LABEL'] . ':', '<input name="' . $arUserField['FIELD_NAME'] . '" value="' . $arUserField['VALUE'] . '" type="text">');

                    break;

                case 'UF_SERVICE_ID':

                    if ($_REQUEST['ID'] > 0) {

                        $content .= self::getEditFieldHtml(
                                        '', '<input name="' . $arUserField['FIELD_NAME'] . '" value="' . $arUserField['VALUE'] . '" type="hidden">', false, true
                        );
                    } else {

                        $arUserField['EDIT_FORM_LABEL'] .= '(выбрать, если бронируется тур из системы)';

                        $option = '<option selected="" value="">Выбрать из списка</option>';
                        if ($arUserField['VALUE'] > 0) {

                            $arTour = current(Tours::get(array('filter' => array('ID' => $arUserField['VALUE']), 'select' => array('ID', 'NAME'))));
                            $option = '<option selected="" value="' . $arTour['ID'] . '">' . $arTour['NAME'] . '</option>';
                        }

                        $content .= self::getEditFieldHtml(
                                        $arUserField['EDIT_FORM_LABEL'] . ':', '<select style="width:180px;" class="tour-select" name="' . $arUserField['FIELD_NAME'] . '">' . $option . '</select>'
                        );
                    }

                    break;

                case 'UF_MANAG_ID':

                    $option = '<option selected="" value="">Выбрать из списка</option>';
                    if ($arUserField['VALUE'] > 0) {

                        $arUser = current(Users::get(array('filter' => array('ID' => $arUserField['VALUE']), 'select' => array('ID', 'NAME', 'LAST_NAME'))));
                        $option = '<option selected="" value="' . $arUser['ID'] . '">' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'] . '</option>';
                    }

                    $content .= self::getEditFieldHtml(
                                    $arUserField['EDIT_FORM_LABEL'] . ':', '<select style="width:180px;" class="manager-select" name="' . $arUserField['FIELD_NAME'] . '">' . $option . '</select>');

                    break;

                case 'UF_USER_ID':

                    $option = '<option selected="" value="">Выбрать из списка</option>';
                    if ($arUserField['VALUE'] > 0) {

                        $arUser = current(Users::get(array('filter' => array('ID' => $arUserField['VALUE']), 'select' => array('ID', 'NAME', 'LAST_NAME'))));
                        $option = '<option selected="" value="' . $arUser['ID'] . '">' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'] . '</option>';
                    }

                    $content .= self::getEditFieldHtml(
                                    $arUserField['EDIT_FORM_LABEL'] . ':', '<select style="width:180px;" class="client-select" name="' . $arUserField['FIELD_NAME'] . '">' . $option . '</select> или <a href="javascript: CRMUtils.addClient()">Добавить нового</a>', true
                    );

                    break;

                case 'UF_TOURISTS_ID':

                    $table = '<table id="tourists-add-table"><tbody>';
                    $select = '<tr><td><select id="tourist-select-1" style="width:180px;" class="tourist-select" name="' . $arUserField['FIELD_NAME'] . '[]"><option value="" selected="">Выбрать из списка</option></select> или <a href="javascript: CRMUtils.addTourist(\'tourist-select-1\')">Добавить нового</a></td></tr>';
                    if (!empty($arUserField['VALUE']) && is_array($arUserField['VALUE'])) {

                        $arTourists = Tourists::get(array('filter' => array('ID' => $arUserField['VALUE']), 'select' => array('ID', 'UF_NAME', 'UF_LAST_NAME')));

                        if (!empty($arTourists)) {

                            $select = '';
                        }

                        foreach ($arTourists as $arTourist) {

                            $select .= '<tr><td><select id="tourist-select-' . $arTourist['ID'] . '" style="width:180px;" class="tourist-select" name="' . $arUserField['FIELD_NAME'] . '[]"><option value="' . $arTourist['ID'] . '">' . $arTourist['UF_NAME'] . ' ' . $arTourist['UF_LAST_NAME'] . '</option></select> или <a href="javascript: CRMUtils.addTourist(\'tourist-select-' . $arTourist['ID'] . '\')">Добавить нового</a></td></tr>';
                        }
                    }

                    $table .= $select . '</tbody><tfoot><tr><td><input value="+ Еще" type="button" onclick="CRMUtils.addTouristField()"><td></tr></tfoot></table>';

                    $content .= self::getEditFieldHtml($arUserField['EDIT_FORM_LABEL'] . ':', $table, true);

                    break;

                case 'UF_COMMENT':

                    $content .= self::getEditFieldHtml(
                                    $arUserField['EDIT_FORM_LABEL'] . ':', '<textarea cols="30" rows="10" name="' . $arUserField['FIELD_NAME'] . '">' . $arUserField['VALUE'] . '</textarea>');

                    break;

                case 'UF_DATE':

                    if ($arUserField['VALUE']) {

                        $content .= self::getEditFieldHtml(
                                        $arUserField['EDIT_FORM_LABEL'] . ':', $arUserField['VALUE']);
                    }

                    break;

                case 'UF_STATUS_ID':

                    $dbStatuses = Statuses::get(array('select' => array('ID', 'UF_NAME')), false);
                    $select = '<select name="' . $arUserField['FIELD_NAME'] . '">';
                    while ($arStatus = $dbStatuses->fetch()) {

                        $select .= '<option ' . ($arStatus['ID'] === $arUserField['VALUE'] ? 'selected=""' : '') . ' value="' . $arStatus['ID'] . '">' . $arStatus['UF_NAME'] . '</option>';
                    }
                    $select .= '</select>';

                    $content .= self::getEditFieldHtml($arUserField['EDIT_FORM_LABEL'] . ':', $select, true);

                    break;

                case 'UF_CURRENCY':

                    $converter = new CurrencyConverter;
                    $arCurrencies = $converter->getListOfCurrency();
                    $select = '<select onchange="CRMUtils.convertingCurrency(this)" data-currency-in="' . $arUserField['VALUE'] . '" name="' . $arUserField['FIELD_NAME'] . '">';
                    foreach ($arCurrencies as $currency) {

                        $select .= '<option ' . ($arUserField['VALUE'] == $currency ? 'selected=""' : '') . ' value="' . $currency . '">' . $currency . '</option>';
                    }
                    $select .= '</select>';

                    $content .= self::getEditFieldHtml($arUserField['EDIT_FORM_LABEL'] . ':', $select);

                    break;

                case 'UF_DATE_FROM':
                case 'UF_DATE_TO':
                case 'UF_ADULTS':
                case 'UF_CHILDREN':

                    if ($_REQUEST['ID'] > 0) {
                        $arUserField['EDIT_IN_LIST'] = 'N';
                    }

                default:

                    $content .= $USER_FIELD_MANAGER->GetEditFormHtml($isFormRequest, $_POST[$arUserField['FIELD_NAME']], $arUserField);
            }
        }

        return $content;
    }

    /**
     * HTML поля редактирования
     * @param string $label
     * @param string $field
     * @param bool $required
     * @param bool $hide
     * @return string
     */
    public static function getEditFieldHtml(string $label, string $field, bool $required = false, bool $hide = false): string {

        if ($required) {
            $label .= '<span class="required">*</span>';
        }

        $content .= '<tr ' . ($hide ? 'style="display:none"' : "") . '>';
        $content .= '<td width="40%">' . $label . '</td>';
        $content .= '<td width="60%">' . $field . '</td>';
        $content .= '</tr>';

        return $content;
    }

    /**
     * Список полей клиента
     * @param string $firstKey
     * @return array
     */
    public static function getClientFields(string $firstKey = null): array {

        $arFields['PERSONAL_DATA']['NAME'] = array('type' => 'text', 'title' => 'Имя', 'required' => true, 'validator' => '_stringLessThenTwo');
        $arFields['PERSONAL_DATA']['SECOND_NAME'] = array('type' => 'text', 'title' => 'Отчество');
        $arFields['PERSONAL_DATA']['LAST_NAME'] = array('type' => 'text', 'title' => 'Фамилия', 'required' => true, 'validator' => '_stringLessThenTwo');
        $arFields['PERSONAL_DATA']['EMAIL'] = array('type' => 'email', 'title' => 'Email');
        $arFields['PERSONAL_DATA']['PERSONAL_PHONE'] = array('type' => 'text', 'title' => 'Телефон', 'required' => true, 'validator' => '_checkPhone');
        $arFields['PERSONAL_DATA']['IS_AGENT'] = array('type' => 'checkbox', 'def' => 'Y', 'title' => 'Является агентом');
        $arFields['PASSPORT_DATA']['UF_PASS_NUMBER'] = array('type' => 'text', 'title' => 'Номер паспорта');
        $arFields['PASSPORT_DATA']['UF_PASS_SERIES'] = array('type' => 'text', 'title' => 'Серия паспорта');
        $arFields['PASSPORT_DATA']['UF_PASS_PERNUM'] = array('type' => 'text', 'title' => 'Личный номер');
        $arFields['PASSPORT_DATA']['UF_PASS_DATE_ISSUE'] = array('type' => 'date', 'title' => 'Дата выдачи паспорта');
        $arFields['PASSPORT_DATA']['UF_PASS_ACTEND'] = array('type' => 'date', 'title' => 'Дата окончания активности');
        $arFields['PASSPORT_DATA']['UF_PASS_ISSUED_BY'] = array('type' => 'text', 'title' => 'Кем выдан паспорт');
        $arFields['COMPANY_DATA']['UF_LEGAL_NAME'] = array('type' => 'text', 'title' => 'Юр. название');
        $arFields['COMPANY_DATA']['UF_LEGAL_ADDRESS'] = array('type' => 'text', 'title' => 'Юр. адрес');
        $arFields['COMPANY_DATA']['UF_BANK_NAME'] = array('type' => 'text', 'title' => 'Название банка');
        $arFields['COMPANY_DATA']['UF_BANK_CODE'] = array('type' => 'text', 'title' => 'Код банка');
        $arFields['COMPANY_DATA']['UF_BANK_ADDRESS'] = array('type' => 'text', 'title' => 'Адрес банка');
        $arFields['COMPANY_DATA']['UF_CHECKING_ACCOUNT'] = array('type' => 'text', 'title' => 'Расчётный счет');
        $arFields['COMPANY_DATA']['UF_ACCOUNT_CURRENCY'] = array('type' => 'text', 'title' => 'Валюта счета');
        $arFields['COMPANY_DATA']['UF_UNP'] = array('type' => 'text', 'title' => 'УНП');
        $arFields['COMPANY_DATA']['UF_OKPO'] = array('type' => 'text', 'title' => 'OKPO');
        $arFields['COMPANY_DATA']['UF_ACTUAL_ADDRESS'] = array('type' => 'text', 'title' => 'Фактический адрес');

        if (isset($arFields[$firstKey])) {

            return $arFields[$firstKey];
        } else {

            return $arFields;
        }
    }

    /**
     * Возвращает HTML полей для формы редактирования клиента
     * @param array $data
     * @param string $tabName
     * @return string
     */
    public static function getClientEditFieldsContent(array $data = null, string $tabName): string {

        $varsFromForm = self::isEditFormRequest();

        $arFields = self::getClientFields($tabName);

        $content = '';

        foreach ($arFields as $code => $arFieldData) {

            $value = $data[$code];

            if ($code == 'IS_AGENT') {

                $arGroups = $GLOBALS['USER']->GetUserGroup($data['ID']);

                $agent = Settings::agentsUGroup();

                if (in_array($agent, $arGroups)) {

                    $value = 'Y';
                }
            }

            if ($varsFromForm) {

                $value = $_POST[$code];
            }

            switch ($arFieldData['type']) {

                case 'date':

                    $content .= self::getEditFieldHtml(
                                    $arFieldData['title'], \CAdminCalendar::CalendarDate($code, $value, 19, true), (bool) $arFieldData['required']
                    );
                    break;

                case 'email':
                case 'text':

                    $content .= self::getEditFieldHtml(
                                    $arFieldData['title'], '<input name="' . $code . '" type="' . $arFieldData['type'] . '" value="' . $value . '">', (bool) $arFieldData['required']
                    );

                    break;

                case 'checkbox':

                    $content .= self::getEditFieldHtml(
                                    $arFieldData['title'], '<input name="' . $code . '" ' . ($value == $arFieldData['def'] ? 'checked=""' : '') . ' type="' . $arFieldData['type'] . '" value="' . $arFieldData['def'] . '">'
                    );

                    break;
            }
        }

        return $content;
    }

    /**
     * Обработка формы редактирования данных о клиенте
     * @param bool $useRedirectIfOk
     * @return array
     */
    public static function processingClientEditForm(bool $useRedirectIfOk = true): array {

        $url = 'travelsoft_crm_booking_clients_list.php?lang=' . LANGUAGE_ID;

        if (strlen($_POST['CANCEL']) > 0) {

            LocalRedirect($url);
        }

        $arErrors = array();

        if (self::isEditFormRequest()) {

            $arFields = self::getClientFields();

            $arSave = array();

            foreach ($arFields as $arrFields) {

                foreach ($arrFields as $fieldName => $arFieldData) {

                    if ($fieldName == 'EMAIL' && strlen($_POST['EMAIL']) <= 0) {

                        $_POST['EMAIL'] = time() . '_freek@freek.fr';
                    }

                    if ($arFieldData['required']) {

                        call_user_func_array(array(self, $arFieldData['validator']), array($_POST[$fieldName], $fieldName, $arErrors));
                    }

                    $arSave[$fieldName] = $_POST[$fieldName];
                }
            }

            $USER_ID = intVal($_REQUEST['ID']);

            self::_checkEmailOnUniq($arSave['EMAIL'], $USER_ID, $arErrors);

            if (empty($arErrors)) {

                $arSave['LOGIN'] = $arSave['EMAIL'];

                if ($USER_ID > 0) {

                    $arSave['GROUP_ID'] = $GLOBALS['USER']->GetUserGroup($USER_ID);
                    $agent = Settings::agentsUGroup();

                    if ($arSave['IS_AGENT'] == 'Y') {

                        if (!in_array($agent, $arSave['GROUP_ID'])) {

                            $arSave['GROUP_ID'][] = $agent;
                        }
                    } elseif (in_array($agent, $arSave['GROUP_ID'])) {

                        unset($arSave['GROUP_ID'][array_search($agent, $arSave['GROUP_ID'])]);
                    }

                    unset($arSave['IS_AGENT']);

                    $result = Users::update($USER_ID, $arSave);
                } else {

                    $arSave['PASSWORD'] = randString(7, array(
                        "abcdefghijklnmopqrstuvwxyz",
                        "ABCDEFGHIJKLNMOPQRSTUVWX­YZ",
                        "0123456789",
                        "!@#\$%^&*()",
                    ));

                    $arSave['CONFIRM_PASSWORD'] = $arSave['PASSWORD'];

                    $def_group = \Bitrix\Main\Config\Option::get("main", "new_user_registration_def_group");
                    if ($def_group != "") {
                        $arSave['GROUP_ID'] = explode(",", $def_group);
                    }

                    if ($arSave['IS_AGENT'] == 'Y') {

                        $arSave['GROUP_ID'][] = Settings::agentsUGroup();
                    }

                    unset($arSave['IS_AGENT']);

                    if (!($result = Users::add($arSave))) {

                        $arErrors[] = 'Возникла ошибка при создании клиента';
                    } else {

                        Mail::sendNewClientRegisterInfo(array(
                            'NAME' => $arSave['NAME'],
                            'LAST_NAME' => $arSave['LAST_NAME'],
                            'EMAIL' => $arSave['EMAIL'],
                            'USER_ID' => $USER_ID,
                            'LOGIN' => $arSave['EMAIL'],
                            'PASSWORD' => $arSave['PASSWORD'],
                            'MESSAGE' => 'Вы успешно зарегистрированны на сайте',
                            'URL_LOGIN' => $arSave['EMAIL']
                        ));
                    }
                }
            }
        }

        if ($result && $useRedirectIfOk) {

            LocalRedirect($url);
        }

        return array('errors' => $arErrors, 'result' => $result);
    }

    /**
     * Возвращает список полей по туристу
     * @param string $firtsKey
     * @return array
     */
    public static function getTouristFields(string $firtsKey = null): array {

        $arFields = array(
            'PERSONAL_DATA' => array(
                'UF_USER_ID', 'UF_NAME', 'UF_NAME_LAT', 'UF_LAST_NAME', 'UF_LAST_NAME_LAT', 'UF_SECOND_NAME',
                'UF_PASS_SERIES', 'UF_PASS_PERNUM', 'UF_PASS_NUMBER', 'UF_PASS_ISSUED_BY', 'UF_PASS_DATE_ISSUE',
                'UF_PASS_ACTEND', 'UF_CITIZENSHIP', 'UF_BIRTHDATE', 'UF_BIRTHCOUNTRY', 'UF_BIRTHCITY',
                'UF_NEED_VISA', 'UF_HAVE_VISA', 'UF_VISA_DATE_FROM', 'UF_VISA_DATE_TO', 'UF_MALE'
            ),
            'WORK_DATA' => array(
                'UF_PLACE_WORK', 'UF_WORK_ZIP', 'UF_WORK_STREET', 'UF_WORK_REGION', 'UF_WORK_PHONE', 'UF_WORK_OFFICE',
                'UF_WORK_HOUSING', 'UF_WORK_HOME', 'UF_WORK_DISTRICT', 'UF_WORK_COUNTRY', 'UF_POSITION'
            ),
            'ADDITIONAL_DATA' => array(
                'UF_STREET', 'UF_SMS_SUBSCR', 'UF_REGULAR_CUSTOMER', 'UF_REGION', 'UF_PROMOCODE',
                'UF_MOB_PHONE', 'UF_HOME', 'UF_FLAT', 'UF_EMAIL_SUBSCR', 'UF_EMAIL', 'UF_DISTRICT', 'UF_CUSTOMER_CARD',
                'UF_COUNTRY', 'UF_COMMENT', 'UF_CITY', 'UF_APPEAL', 'UF_ADVERTISING', 'UF_ACTION', 'UF_ZIP'
            )
        );

        if (isset($arFields[$firtsKey])) {

            return $arFields[$firtsKey];
        } else {

            return $arFields;
        }
    }

    /**
     * Возвращает HTML для формы редактирования туриста
     * @global \travelsoft\booking\crm\type $USER_FIELD_MANAGER
     * @param array $data
     * @param type $tabName
     * @return string
     */
    public static function getTouristFieldsContent(array $data = null, $tabName): string {

        $arFields = self::getTouristFields($tabName);

        global $USER_FIELD_MANAGER;

        $arUserFields = array_filter(
                $USER_FIELD_MANAGER->getUserFieldsWithReadyData("HLBLOCK_" . Settings::touristsStoreId(), $data, LANGUAGE_ID), function ($arItem) use ($arFields) {
            return in_array($arItem['FIELD_NAME'], $arFields);
        });

        $arRequired = array(
            'UF_NAME', 'UF_NAME_LAT', 'UF_LAST_NAME', 'UF_LAST_NAME_LAT', 'UF_SECOND_NAME',
            'UF_PASS_SERIES', 'UF_PASS_PERNUM', 'UF_PASS_NUMBER', 'UF_PASS_ISSUED_BY', 'UF_CITIZENSHIP');

        $isFormRequest = self::isEditFormRequest();

        foreach ($arUserFields as $arUserField) {

            if ($arUserField['FIELD_NAME'] == 'UF_USER_ID') {

                if ($data['UF_USER_ID'] > 0) {
                    $content .= self::getEditFieldHtml(
                                    '', '<input type="hidden" value="' . $value . '" name="' . $arUserField['FIELD_NAME'] . '">', false, true);
                }
                continue;
            }

            if (in_array($arUserField['FIELD_NAME'], $arRequired)) {

                $value = $arUserField['VALUE'];
                if ($_POST[$arUserField['FIELD_NAME']]) {

                    $value = $_POST[$arUserField['FIELD_NAME']];
                }

                $content .= self::getEditFieldHtml(
                                $arUserField['EDIT_FORM_LABEL'], '<input type="text" value="' . $value . '" name="' . $arUserField['FIELD_NAME'] . '">', true);
                continue;
            }

            $content .= $USER_FIELD_MANAGER->GetEditFormHtml($isFormRequest, $_POST[$arUserField['FIELD_NAME']], $arUserField);
        }

        return $content;
    }

    /**
     * Обработка формы редактирования данных по туристу
     * @global \travelsoft\booking\crm\type $USER_FIELD_MANAGER
     * @param bool $useRedirectIfOk
     * @return array
     */
    public static function processingTouristEditForm(bool $useRedirectIfOk = true): array {

        global $USER_FIELD_MANAGER;

        $url = 'travelsoft_crm_booking_tourists_list.php?lang=' . LANGUAGE_ID;

        if (strlen($_POST['CANCEL']) > 0) {

            LocalRedirect($url);
        }

        $arErrors = array();

        if (self::isEditFormRequest()) {

            $data = array();

            $USER_FIELD_MANAGER->EditFormAddFields('HLBLOCK_' . Settings::touristsStoreId(), $data);

            if (strlen($data['UF_NAME']) <= 0) {

                $arErrors[] = 'Не указано имя туриста';
            }

            if (strlen($data['UF_LAST_NAME']) <= 0) {

                $arErrors[] = 'Не указана фамилия туриста';
            }

            if (strlen($data['UF_NAME_LAT']) <= 0) {

                $arErrors[] = 'Не указано имя туриста латиницей';
            }

            if (strlen($data['UF_LAST_NAME_LAT']) <= 0) {

                $arErrors[] = 'Не указана фамилия туриста латиницей';
            }

            if (strlen($data['UF_PASS_SERIES']) <= 0) {

                $arErrors[] = 'Не указан серия паспорта';
            }

            if (strlen($data['UF_PASS_PERNUM']) <= 0) {

                $arErrors[] = 'Не указан личный номер туриста';
            }

            if (strlen($data['UF_PASS_NUMBER']) <= 0) {

                $arErrors[] = 'Не указан номер паспорта туриста';
            }

            if (strlen($data['UF_CITIZENSHIP']) <= 0) {

                $arErrors[] = 'Не указано гражданство туриста';
            }

            if (empty($arErrors)) {

                if ($_REQUEST['ID'] > 0) {

                    $ID = intval($_REQUEST['ID']);
                    $result = Tourists::update($ID, $data);
                } else {

                    $result = Tourists::add($data);
                }

                if ($result && $useRedirectIfOk) {

                    LocalRedirect($url);
                }
            }
        }

        return array('errors' => $arErrors, 'result' => $result);
    }

    /**
     * Возвращает массив полей шапки таблицы заказов
     * @return array
     */
    public static function getOrdersTableHeaders(): array {

        $arHeaders = array(
            array(
                "id" => "ID",
                "content" => "Номер брони",
                "sort" => "ID",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_STATUS_ID",
                "content" => "Статус брони",
                "sort" => "UF_STATUS_ID",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_SERVICE_NAME",
                "content" => "Услуги",
                "sort" => "UF_SERVICE_NAME",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_CLIENT_NAME",
                "content" => "Клиент",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "CLIENT_IS_AGENT",
                "content" => "Является агентом",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_CLIENT_PHONE",
                "content" => "Телефон клиента",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_DATE",
                "content" => "Дата создания брони",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_DATE_FROM",
                "content" => "Дата начала",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_DATE_TO",
                "content" => "Дата окончания",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_DURATION",
                "content" => "Продолжительность (дней)",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_ADULTS",
                "content" => "Количество взрослых",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_CHILDREN",
                "content" => "Количество детей",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_COST",
                "content" => "Стоимость",
                "align" => "center",
                "default" => true
            ),
            array(
                "id" => "UF_CURRENCY",
                "content" => "Валюта",
                "align" => "center",
                "default" => true
            )
        );

        return $arHeaders;
    }

    /**
     * Подготовка строки таблицы заказов
     * @param \CAdminListRow $row
     */
    public static function prepareRowForOrdersTable(\CAdminListRow &$row, array $arData) {

        $newOrderLabel = '';
        if (isset($arData['STATUSES'][$arData['ORDER']['UF_STATUS_ID']])) {

            $row->AddViewField("UF_STATUS_ID", $arData['STATUSES'][$arData['ORDER']['UF_STATUS_ID']]["UF_NAME"]);
            if ($arData['ORDER']['UF_STATUS_ID'] == Settings::defStatus()) {

                $newOrderLabel = '<span class="new-order-label">new</span> ';
            }
        }

        $row->AddViewField("ID", $newOrderLabel . '<a target="__blank" href="travelsoft_crm_booking_order_edit.php?ID=' . $arData['ORDER']['ID'] . '&lang=' . LANG . '">' . $arData['ORDER']["ID"] . '</a>');

        if ($arData['ORDER']['UF_USER_ID']) {

            $arUser = Users::getById($arData['ORDER']['UF_USER_ID']);
        }

        if (strlen($arUser['NAME']) > 0) {
            $CNAME = $arUser['NAME'];
        }

        if (strlen($CNAME) > 0) {

            if (strlen($arUser['SECOND_NAME']) > 0) {
                $CNAME .= ' ' . $arUser['SECOND_NAME'];
            }

            if (strlen($arUser['LAST_NAME']) > 0) {
                $CNAME .= ' ' . $arUser['LAST_NAME'];
            }

            if (strlen($arUser['EMAIL']) > 0) {
                $CNAME .= '[' . $arUser['EMAIL'] . ']';
            }
        }

        $isAgent = "Нет";
        if ($arUser['ID'] > 0 && in_array(Settings::agentsUGroup(), $GLOBALS['USER']->GetUserGroup($arUser['ID']))) {

            $isAgent = 'Да';
        }

        $row->AddViewField('CLIENT_IS_AGENT', $isAgent);

        $row->AddViewField("UF_CLIENT_NAME", '<a target="__blank" href="travelsoft_crm_booking_client_edit.php?lang=' . LANG . '&ID=' . $arData['ORDER']['UF_USER_ID'] . '">' . $CNAME . '</a>');

        if ($arUser['PERSONAL_PHONE']) {

            $row->AddViewField("UF_CLIENT_PHONE", $arUser['PERSONAL_PHONE']);
        }

        $row->AddActions(array(
            array(
                "ICON" => "edit",
                "DEFAULT" => true,
                "TEXT" => "Изменить",
                "ACTION" => 'BX.adminPanel.Redirect([], "travelsoft_crm_booking_order_edit.php?ID=' . $arData['ORDER']["ID"] . '", event);'
            ),
            array(
                "ICON" => "delete",
                "DEFAULT" => true,
                "TEXT" => "Удалить",
                "ACTION" => "if(confirm('Действительно хотите удалить бронь')) GetAdminList('/bitrix/admin/travelsoft_crm_booking_orders_list.php?ID=" . $arData['ORDER']['ID'] . "&action_button=delete&lang=" . LANGUAGE_ID . "&sessid=" . bitrix_sessid() . "');"
            )
        ));
    }

    /**
     * Возвращает максимальный id списка заказов
     * @return int
     */
    public static function getOrderLastId() {
        $result = Orders::get(array('select' => array(new \Bitrix\Main\Entity\ExpressionField('MAX_ID', 'max(ID)'))), false)->fetch();
        return intVal($result['MAX_ID']);
    }

    /**
     * Строка >= 2 символов ?
     * @param string $value
     * @param string $fieldName
     * @param array $arErrors
     */
    protected static function _stringLessThenTwo(string $value, string $fieldName, array &$arErrors) {

        if (strlen($value) < 2) {

            $arErrors[] = 'Поле ' . $fieldName . ' должно быть не менее 2-ух символов';
        }
    }

    /**
     * Проверка телефона
     * @param string $value
     * @param string $fieldName
     * @param array $arErrors
     */
    protected static function _checkPhone(string $value, string $fieldName, array &$arErrors) {

        if (preg_match('#^\+?[0-9]{5,}$#', $value) !== 1) {

            $arErrors[] = 'Поле ' . $fieldName . ' должно соответствовать формату (+XXX XX XXXXXXX)';
        }
    }

    /**
     * Проверка email
     * @param mixed string $value
     * @param mixed string $fieldName
     * @param array $arErrors
     */
    protected static function _checkEmail(string $value, string $fieldName, array &$arErrors) {

        if (!check_email($value)) {

            $arErrors[] = 'Правильно заполнитель поле ' . $fieldName;
        }
    }

    /**
     * Проверка email на уникальность
     * @param string $value
     * @param int $userId
     * @param array $arErrors
     */
    protected static function _checkEmailOnUniq(string $value, int $userId = null, array &$arErrors) {

        $arUsers = Users::get(array('filter' => array('EMAIL' => $value, "!ID" => $userId), 'select' => array('ID')));

        if (!empty($arUsers)) {
            $arErrors[] = 'Такой email уже зарегистрирован в системе';
        }
    }

}
