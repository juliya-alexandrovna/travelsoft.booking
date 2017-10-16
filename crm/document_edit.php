<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Documents;
use travelsoft\booking\crm\Utils;

require_once 'header.php';
?>

<style>
    img[src="/bitrix/js/main/core/images/hint.gif"] {
        display: none;
    }
</style>

<?

try {

    $ID = intVal($_REQUEST['ID']);

    $title = 'Добавление документа';
    if ($ID > 0) {

        $arDouments = Documents::getById($ID);

        if (!$arDouments['ID']) {

            throw new Exception('Документ с ID="' . $ID . '" не найдена');
        }

        $title = 'Редактирование документа #' . $arDouments['ID'];
    }

    $APPLICATION->SetTitle($title);

    $arResult = Utils::processingDocumentEditForm();

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
            "TITLE" => 'Документ',
            'content' => Utils::getDocumentFieldsContent((array) $arDouments)
    ));

    Utils::showEditForm(array(
        'action' => $APPLICATION->GetCurPageParam(),
        'name' => 'document_form',
        'id' => 'document_form',
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

