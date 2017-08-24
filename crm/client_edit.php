<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Users;
use travelsoft\booking\crm\Utils;

require_once 'header.php';

$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/select2.min.css'>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-3.2.1.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/select2.full.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/lib.js?v=a'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/order_edit.js?v=X'></script>");
?>

<style>
    img[src="/bitrix/js/main/core/images/hint.gif"] {
        display: none;
    }
</style>

<?

try {

    $ID = intVal($_REQUEST['ID']);

    $title = 'Добавление клиента';
    if ($ID > 0) {

        $arUser = current(Users::get(array('filter' => array('ID' => $ID), 'select' => array('UF_*'))));

        if (!$arUser['ID']) {

            throw new Exception('Клиент с ID="' . $ID . '" не найдена');
        }

        $title = 'Редактирование клиента #' . $arUser['ID'];
    }

    $APPLICATION->SetTitle($title);

    $arResult = Utils::processingClientEditForm();

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

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
                'content' => Utils::getClientEditFieldsContent($arUser, 'PERSONAL_DATA')
            ),
            array(
                "DIV" => "PASSPORT_DATA",
                "TAB" => 'Паспортные данные',
                'content' => Utils::getClientEditFieldsContent($arUser, 'PASSPORT_DATA')
            ),
            array(
                "DIV" => "COMPANY_DATA",
                "TAB" => 'Реквизиты компании',
                'content' => Utils::getClientEditFieldsContent($arUser, 'COMPANY_DATA')
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

