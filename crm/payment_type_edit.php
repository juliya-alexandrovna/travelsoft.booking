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

try {

    $ID = intVal($_REQUEST['ID']);

    $title = 'Добавление типа оплаты';
    if ($ID > 0) {

        $arPaymentType = \travelsoft\booking\stores\PaymentsTypes::getById($ID);

        if (!$arPaymentType['ID']) {

            throw new Exception('Тип оплаты с ID="' . $ID . '" не найдена');
        }

        $title = 'Редактирование типа оплаты #' . $arPaymentType['ID'];
    }

    $APPLICATION->SetTitle($title);

    $arResult = Utils::processingPaymentTypeEditForm();

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

    if (!empty($arResult['errors'])) {

        CAdminMessage::ShowMessage(array(
            "MESSAGE" => implode('<br>', $arResult['errors']),
            "TYPE" => "ERROR"
        ));
    }

    Utils::showEditForm(array(
        'action' => $APPLICATION->GetCurPageParam(),
        'name' => 'payment_type_form',
        'id' => 'payment_type_form',
        'tabs' => array(
            array(
                "DIV" => "PAYMENT_TYPE_FORM",
                "TAB" => 'Типы оплаты',
                'content' => Utils::getPaymentsTypesEditFieldsContent((array)$arPaymentType)
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
