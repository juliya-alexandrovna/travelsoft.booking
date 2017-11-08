<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */

require_once 'header.php';

use travelsoft\booking\crm\Utils;

$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/select2.min.css'>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-3.2.1.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/select2.full.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/lib.js?v=aa'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/order_edit.js?v=O'></script>");
?>

<style>
    img[src="/bitrix/js/main/core/images/hint.gif"] {
        display: none;
    }
</style>

<?

try {

    $ID = intVal($_REQUEST['ID']);

    $title = 'Добавление кассы';
    if ($ID > 0) {

        $arCashDesk = current(travelsoft\booking\crm\stores\CashDesks::get(array('filter' => array('ID' => $ID))));

        if (!$arCashDesk['ID']) {

            throw new Exception('Клиент с ID="' . $ID . '" не найдена');
        }

        $title = 'Редактирование кассы #' . $arCashDesk['ID'];
    }

    $APPLICATION->SetTitle($title);

    $arResult = Utils::processingCashDeskEditForm();

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

    if (!empty($arResult['errors'])) {

        CAdminMessage::ShowMessage(array(
            "MESSAGE" => implode('<br>', $arResult['errors']),
            "TYPE" => "ERROR"
        ));
    }

    Utils::showEditForm(array(
        'action' => $APPLICATION->GetCurPageParam(),
        'name' => 'cash_desk_form',
        'id' => 'cash_desk_form',
        'tabs' => array(
            array(
                "DIV" => "CASH_DESK_FORM",
                "TAB" => 'Касса',
                'content' => Utils::getCashDeskEditFieldsContent($arCashDesk, 'CASH_DESK_FORM')
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
