<?php

namespace travelsoft\booking\crm;

/*
 * Функционал для страниц crm
 */

/**
 * Отправка json-строки
 * @global \CMain $APPLICATION
 * @param string $body
 */
function sendJsonResponse(string $body) {

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
function getPreparedUserSettings(array $userSettings): array {

    $result = array();

    foreach ($userSettings as $us) {

        $result[$us['UF_ELEMENT_ID']] = \travelsoft\sta($us['UF_SFIELDS']);
    }

    return $result;
}

/**
 * Обрабатывает запрос от формы изменения цен и квот и возвращает результат
 * @param array $request
 * @return array
 */
function processPriceAndQuotasFormRequest(array $request): array {

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

                $response['quotas'][$timestamp] = _processQuotas($timestamp, intVal($value), $dbTour['ID']);
            }
        }

        # обработка stop sale
        if ($req['stop_sale']) {

            foreach ($req['stop_sale'] as $timestamp => $value) {

                $response['stop_sale'][$timestamp] = _processStopSale($timestamp, boolval($value), $dbTour['ID']);
            }
        }

        # обработка цен
        if ($req['prices']) {

            foreach ($req['prices'] as $ptid => $value) {

                foreach ($value as $timestamp => $vvalue) {

                    $response['prices'][$ptid][$timestamp] = _processPrices($timestamp, $ptid, (float) $vvalue, $dbTour['ID']);
                }
            }
        }

        # обработка запроса на массовое редактирование
        if ($req['mass_edit']) {

            $me = $req['mass_edit'];

            if (!empty($me['unix_dates'])) {

                if ($me['quotas']) {

                    foreach ($me['unix_dates'] as $timestamp) {

                        $response['quotas'][$timestamp] = _processQuotas($timestamp, intVal($me['quotas']['value']), $dbTour['ID']);
                    }
                }

                if ($me['stop_sale']) {

                    foreach ($me['unix_dates'] as $timestamp) {

                        $response['stop_sale'][$timestamp] = _processStopSale($timestamp, boolval($me['stop_sale']['value']), $dbTour['ID']);
                    }
                }

                if ($me['prices']) {

                    foreach ($me['unix_dates'] as $timestamp) {

                        foreach ($me['prices'] as $ptid => $value) {

                            $response['prices'][$ptid][$timestamp] = _processPrices($timestamp, intVal($ptid), (float) $value, $dbTour['ID']);
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
function _processQuotas(string $timestamp, int $value, int $tourid): array {

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
 * Обрабатывает запрос на изменение stop sale и возвращает результат
 * @param string $timestamp
 * @param bool $value
 * @param int $tourid
 * @return int
 */
function _processStopSale(string $timestamp, bool $value, int $tourid): int {

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
function _processPrices(string $timestamp, int $ptid, float $value, int $tourid): float {

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
            $result = null;
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
function processSettingsFromRequest(array $request, array $userSettings): array {

    $result = $errors = array();

    if (strlen($request['show']) > 0) {

        if (empty(\travelsoft\booking\stores\Tours::getById($request['tourid']))) {

            $errors[] = 'Недопустимый id тура';
        }

        $dates = array_unique(array_filter($request['dates'], function ($date) {

                    if (preg_match('#^\d{2}\.\d{2}\.\d{4}$#', $date)) {

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

                $UF_SFIELDS = \travelsoft\ats($settings);
                # обновляем настройки в базе данных
                \travelsoft\booking\stores\crm\Settings::update($cs['ID'], array(
                    'UF_SFIELDS' => $UF_SFIELDS
                ));

                $userSettings[$cs['ID']] = array(
                    'ID' => $cs['ID'],
                    'UF_USER_ID' => $GLOBALS['USER']->GetID(),
                    'UF_ELEMENT_ID' => $request['tourid'],
                    'UF_SFIELDS' => \travelsoft\ats($settings)
                );
            } else {

                $arAdd = array(
                    'UF_USER_ID' => $GLOBALS['USER']->GetID(),
                    'UF_ELEMENT_ID' => $request['tourid'],
                    'UF_SFIELDS' => \travelsoft\ats($settings)
                );
                # сохраняем настройки в базе данных
                $csid = \travelsoft\booking\stores\crm\Settings::add($arAdd);

                $arAdd['ID'] = $csid;
                $userSettings[$csid] = $arAdd;
            }
        }

        $preparedUserSettings = getPreparedUserSettings($userSettings);

        $result = array(
            'errors' => $errors,
            'settings' => $preparedUserSettings,
            'responseBody' => getPriceAndQuotasTableAsHtml((array) $preparedUserSettings[$request['tourid']])
        );
    }

    return $result;
}

/**
 * Возвращает html таблицы цен и квот
 * @param array $parameters
 * @return string
 */
function getPriceAndQuotasTableAsHtml(array $parameters): string {

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

        _setQuotasSoldStopSaleRows($timestamps, $list, $parameters);
        _setPriceTypesRow($timestamps, $list, $parameters);

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
function _setPriceTypesRow(array $timestamps, & $list, array $parameters) {

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

        $formElements = _getMassEditHiddenFormElements($parameters);

        $formElements[] = array(
            'label' => $priceType["UF_NAME"],
            'element' => 'input',
            'type' => 'text',
            'value' => '',
            'name' => 'prices_and_quotas[mass_edit][prices][' . $priceType['ID'] . ']'
        );

        $rowData["id"] = "<b>" . $priceType["UF_NAME"] . "</b><br>[<a href='javascript: CRMUtils.initPopupForm(" . _getMassEditPopupJsonSettings(_getMassEditPopupFormHtml($formElements, 'prices-mass-edit-' . $priceType['ID']), 'prices-mass-edit-' . $priceType['ID']) . ")'>Изменить</a>]";

        foreach ($timestamps as $timestamp) {

            $rowData[$timestamp] = $prices[$priceType['ID']][$timestamp]['UF_GROSS'] > 0 ? (float) $prices[$priceType['ID']][$timestamp]['UF_GROSS'] : null;
        }

        _setViewField($list->addRow($rowData["id"], $rowData), $rowData, '<input onblur="CRMUtils.triggerPriceAndQuotasFormAjaxSubmit(this);" type="text" name="prices_and_quotas[prices][' . $priceType['ID'] . '][#key#]" value="#value#" size="6">');
    }
}

/**
 * Возвращает контент формы массового редактирования
 * @global \CMain $APPLICATION
 * @param array $formElements
 * @return string
 */
function _getMassEditPopupFormHtml(array $formElements, string $formid): string {

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
 * @param \CAdminList $list
 * @param array $parameters
 */
function _setQuotasSoldStopSaleRows(array $timestamps, & $list, array $parameters) {

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

    $formElements = _getMassEditHiddenFormElements($parameters);

    $formElements[] = array(
        'label' => "Квоты",
        'element' => 'input',
        'type' => 'text',
        'value' => '',
        'name' => 'prices_and_quotas[mass_edit][quotas][value]'
    );

    $quotasRowData["id"] = "<b>Квоты</b><br>[<a href='javascript: CRMUtils.initPopupForm(" . _getMassEditPopupJsonSettings(_getMassEditPopupFormHtml($formElements, 'quotas-mass-edit'), 'quotas-mass-edit') . ")'>Изменить</a>]";

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

    $stopSalesRowData["id"] = "<b>Stop sale</b><br>[<a href='javascript: CRMUtils.initPopupForm(" . _getMassEditPopupJsonSettings(_getMassEditPopupFormHtml($formElements, 'stop-sale-mass-edit'), 'stop-sale-mass-edit') . ")'>Изменить</a>]";

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

    _setViewField($list->addRow($quotasRowData["id"], $quotasRowData), $quotasRowData, '<input onblur="CRMUtils.triggerPriceAndQuotasFormAjaxSubmit(this);" type="text" name="prices_and_quotas[quotas][#key#]" value="#value#" size="3">');
    _setViewField($list->addRow($soldRowData["id"], $soldRowData), $soldRowData, '<span id="sold-#key#">#value#</span>');
    _setViewField($list->addRow($onSaleRowData["id"], $onSaleRowData), $onSaleRowData, '<span id="on-sale-#key#">#value#</span>');
    _setViewField($list->addRow($stopSalesRowData["id"], $stopSalesRowData), $stopSalesRowData, '<select onchange="CRMUtils.triggerPriceAndQuotasFormAjaxSubmit(this);" name="prices_and_quotas[stop_sale][#key#]"><option #selected-no# value="0">Нет</option><option #selected-yes# value="1">Да</option></select>');
}

/**
 * Возвращает массив скрытых полей формы массового редактирования цен и квот
 * @param array $parameters
 * @return array
 */
function _getMassEditHiddenFormElements(array $parameters): array {

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

function _getMassEditPopupJsonSettings(string $content, string $formid): string {

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
function _setViewField(&$row, array $rowData, string $template) {

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
