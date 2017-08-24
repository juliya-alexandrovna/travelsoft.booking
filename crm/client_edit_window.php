<?php
require_once 'header.php';

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_popup_admin.php");

use travelsoft\booking\crm\Utils;

$APPLICATION->SetTitle('Заполнение данных клиента');

$arResult = Utils::processingClientEditForm(false);

if ($arResult['result']) {

    $arUser = current(travelsoft\booking\stores\Users::get(array('filter' => array('ID' => $arResult['result']), 'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'))));
    ?>
    <script>

        var value = Number("<?= $arUser['ID'] ?>");
        var text = "<?=
    implode(' ', array_filter(array($arUser['NAME'], $arUser['SECOND_NAME'], $arUser['LAST_NAME']), function ($item) {
                return strlen($item) > 0;
            }))
    ?>";

        if (typeof window.opener.clientChildWindowData === 'object') {

            window.opener.clientChildWindowData.clientSelect.append('<option selected="" value="' + value + '">' + text + '</option>');
            window.opener.clientChildWindowData.clientSelect.trigger('change');
            window.opener.clientChildWindowData.initClientSelect2();
        }

        window.close();
    </script>
    <?
}

if (!empty($arResult['errors'])) {

    CAdminMessage::ShowMessage(array(
        "MESSAGE" => implode('<br>', $arResult['errors']),
        "TYPE" => "ERROR"
    ));
}

Utils::showEditForm(array(
    'action' => $APPLICATION->GetCurPageParam(),
    'name' => 'client_form',
    'id' => 'client_form',
    'tabs' => array(
        array(
            "DIV" => "PERSONAL_DATA",
            "TAB" => 'Личные данные',
            'content' => Utils::getClientEditFieldsContent(array(), 'PERSONAL_DATA')
        ),
        array(
            "DIV" => "PASSPORT_DATA",
            "TAB" => 'Паспортные данные',
            'content' => Utils::getClientEditFieldsContent(array(), 'PASSPORT_DATA')
        ),
        array(
            "DIV" => "COMPANY_DATA",
            "TAB" => 'Реквизиты компании',
            'content' => Utils::getClientEditFieldsContent(array(), 'COMPANY_DATA')
        )
    ),
    'buttons' => array(
        array(
            'class' => 'adm-btn-save',
            'name' => 'SAVE',
            'value' => 'Сохранить'
        ),
        array(
            'name' => 'CANCEL',
            'value' => 'Отменить',
            'onclick' => 'window.close(); return false;'
        )
    )
));

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_popup_admin.php");

