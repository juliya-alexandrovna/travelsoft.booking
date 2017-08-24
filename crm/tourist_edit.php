<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Tourists;
use travelsoft\booking\crm\Utils;

require_once 'header.php';

$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/select2.min.css'>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-3.2.1.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/select2.full.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/lib.js?v=b'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/tourist_edit.js?v=f'></script>");
?>

<style>
    img[src="/bitrix/js/main/core/images/hint.gif"] {
        display: none;
    }
</style>

<?

try {

    $ID = intVal($_REQUEST['ID']);

    $title = 'Добавление туриста';
    $editRequest = false;
    if ($ID > 0) {

        $arTourist = current(Tourists::get(array('filter' => array('ID' => $ID))));

        if (!$arTourist['ID']) {

            throw new Exception('Турист с ID="' . $ID . '" не найден');
        }

        $title = 'Редактирование туриста #' . $arTourist['ID'];
        $editRequest = true;
    }

    $APPLICATION->SetTitle($title);

    $arResult = Utils::processingTouristEditForm();

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

    if (!empty($arResult['errors'])) {

        CAdminMessage::ShowMessage(array(
            "MESSAGE" => implode('<br>', $arResult['errors']),
            "TYPE" => "ERROR"
        ));
    }

    Utils::showEditForm(array(
    'action' => $APPLICATION->GetCurPageParam(),
    'name' => 'tourist_form',
    'id' => 'tourist_form',
    'tabs' => array(
        array(
            "DIV" => "PERSONAL_DATA",
            "TAB" => 'Личные данные',
            'content' => ($editRequest ? "" :Utils::getEditFieldHtml(
                        'Использовать в качестве шаблона',
                        '<select id="tpl-user"><option selected="" value="">Выбрать из списка</option></select>'
                )) . Utils::getTouristFieldsContent($arTourist, 'PERSONAL_DATA')
        ),
        array(
            "DIV" => "WORK_DATA",
            "TAB" => 'Данные по работе',
            'content' => Utils::getTouristFieldsContent($arTourist, 'WORK_DATA')
        ),
        array(
            "DIV" => "ADDITIONAL_DATA",
            "TAB" => 'Дополнительная информация',
            'content' => Utils::getTouristFieldsContent($arTourist, 'ADDITIONAL_DATA')
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
            'value' => 'Отменить'
        )
    )
));
    
} catch (Exception $e) {

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    CAdminMessage::ShowMessage(array('MESSAGE' => $e->getMessage(), 'TYPE' => 'ERROR'));
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>

