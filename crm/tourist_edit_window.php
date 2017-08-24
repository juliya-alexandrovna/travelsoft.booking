<?php
require_once 'header.php';

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_popup_admin.php");

use travelsoft\booking\crm\Utils;

$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/select2.min.css'>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-3.2.1.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/select2.full.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/lib.js?v=b'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/tourist_edit.js?v=f'></script>");

$APPLICATION->SetTitle('Заполнение данных по туристу');

?>
<style>
    img[src="/bitrix/js/main/core/images/hint.gif"] {
        display: none;
    }
</style>
<?

$arResult = Utils::processingTouristEditForm(false);

if ($arResult['result']) {

    $arTourist = current(travelsoft\booking\stores\Tourists::get(array('filter' => array('ID' => $arResult['result']), 'select' => array('ID', 'UF_NAME', 'UF_LAST_NAME', 'UF_SECOND_NAME'))));
    ?>
    <script>

        var value = Number("<?= $arTourist['ID'] ?>");
        var text = "<?=
    implode(' ', array_filter(array($arTourist['UF_NAME'], $arTourist['UF_SECOND_NAME'], $arTourist['UF_LAST_NAME']), function ($item) {
                return strlen($item) > 0;
            }))
    ?>";

        if (typeof window.opener.touristChildWindowData === 'object') {

            window.opener.touristChildWindowData.touristSelect.append('<option selected="" value="' + value + '">' + text + '</option>');
            window.opener.touristChildWindowData.touristSelect.trigger('change');
            window.opener.touristChildWindowData.initTouristSelect2();
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
    'name' => 'tourst_form',
    'id' => 'tourst_form',
    'tabs' => array(
        array(
            "DIV" => "PERSONAL_DATA",
            "TAB" => 'Личные данные',
            'content' => Utils::getEditFieldHtml(
                        'Использовать в качестве шаблона',
                        '<select id="tpl-user"><option selected="" value="">Выбрать из списка</option></select>'
                ) . Utils::getTouristFieldsContent(array(), 'PERSONAL_DATA')
        ),
        array(
            "DIV" => "WORK_DATA",
            "TAB" => 'Данные по работе',
            'content' => Utils::getTouristFieldsContent(array(), 'WORK_DATA')
        ),
        array(
            "DIV" => "ADDITIONAL_DATA",
            "TAB" => 'Дополнительная информация',
            'content' => Utils::getTouristFieldsContent(array(), 'ADDITIONAL_DATA')
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

