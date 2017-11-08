<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Orders;
use travelsoft\booking\crm\Utils;

require_once 'header.php';

$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/select2.min.css'>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-3.2.1.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/select2.full.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/lib.js?v=aa'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/order_edit.js?v28'></script>");
?>

<style>
    img[src="/bitrix/js/main/core/images/hint.gif"] {
        display: none;
    }
</style>

<?

try {

    $ID = intVal($_REQUEST['ID']);

    $title = 'Создание заказа';
    if ($ID > 0) {

        $arOrder = Orders::getById($ID);

        if (!$arOrder['ID']) {

            throw new Exception('Бронь с ID="' . $ID . '" не найдена');
        }

        $title = 'Редактирование заказа #' . $arOrder['ID'];
    }

    $APPLICATION->SetTitle($title);

    $arResult = Utils::processingOrderEditForm();

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

    if (!empty($arResult['errors'])) {

        CAdminMessage::ShowMessage(array(
            "MESSAGE" => implode('<br>', $arResult['errors']),
            "TYPE" => "ERROR"
        ));
    }

    $arTabs = array(
        array(
            "DIV" => "ORDER",
            "TAB" => 'Заказ',
            "TITLE" => 'Информация по заказу',
            'content' => Utils::getEditOrderFieldsContent((array) $arOrder))
    );

    if ($arOrder['ID'] > 0) {
        $arTabs[] = array(
            "DIV" => "DOCUMENTS",
            "TAB" => "Формирование документов",
            "TITLE" => "Выбор документа для формирования",
            "content" => Utils::getDocumentsForPrintContent(intVal($arOrder['ID']))
        );
        $arTabs[] = array(
            "DIV" => "PAYMENT_HISTORY",
            "TAB" => 'Платежи',
            "TITLE" => 'История платежей',
            'content' => Utils::getPaymentHistoryContent(intVal($arOrder['ID']))
        );
    }

    Utils::showEditForm(array(
        'action' => $APPLICATION->GetCurPageParam(),
        'name' => 'order_form',
        'id' => 'order_form',
        'tabs' => $arTabs,
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

