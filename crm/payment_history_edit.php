<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */

require_once 'header.php';

use travelsoft\booking\crm\Utils;
?>

<style>
    img[src="/bitrix/js/main/core/images/hint.gif"] {
        display: none;
    }
</style>

<?

$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/select2.min.css'>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-3.2.1.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/select2.full.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/payment_history_edit.js?v=a'></script>");

try {

    $ID = intVal($_REQUEST['ID']);

    $title = 'Добавление элемента истории платежа';
    if ($ID > 0) {

        $arPaymentHistory = travelsoft\booking\crm\stores\PaymentHistory::getById($ID);

        if (!$arPaymentHistory['ID']) {

            throw new Exception('Элемент истории платежа с ID="' . $ID . '" не найден');
        }

        $title = 'Редактирование элемента истории платежа #' . $arPaymentHistory['ID'];
    }

    $APPLICATION->SetTitle($title);

    $arResult = Utils::processingPaymentHistoryEditForm();

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

    if (!empty($arResult['errors'])) {

        CAdminMessage::ShowMessage(array(
            "MESSAGE" => implode('<br>', $arResult['errors']),
            "TYPE" => "ERROR"
        ));
    }

    Utils::showEditForm(array(
        'action' => $APPLICATION->GetCurPageParam(),
        'name' => 'payment_history_form',
        'id' => 'payment_history_form',
        'tabs' => array(
            array(
                "DIV" => "PAYMENT_HISTORY_FORM",
                "TAB" => 'Элемент истории платежа',
                'content' => Utils::getPaymentHistoryEditFieldsContent((array)$arPaymentHistory)
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
