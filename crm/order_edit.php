<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Orders;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Bitrix\Main\Loader::includeModule("travelsoft.booking");

if (!\travelsoft\booking\crm\Utils::access()) {

    $APPLICATION->AuthForm('Доступ запрещен');
}

try {

    $ORDER_ID = intVal($_REQUEST['ORDER_ID']);

    if ($ORDER_ID > 0) {

        $arOrder = Orders::getById($ORDER_ID);

        if (!$arOrder['ID']) {

            throw new Exception('Бронь с ID="' . $ORDER_ID . '" не найдена');
        }
    }

    $aTabs = array(
        array("DIV" => "TOTAL_INFO", "TAB" => 'Бронь', "TITLE" => 'Информация по брони', 'util' => array('func' => '\\travelsoft\\booking\\crm\\Utils::getOrderForm', 'args' => $ORDER_ID)),
        array("DIV" => "CLIENT", "TAB" => 'Клиент', "TITLE" => 'Информация по клиенту', 'util' => array('func' => '\\travelsoft\\booking\\crm\\Utils::getClientForm', 'args' => $arOrder['UF_USER_ID'])),
        array("DIV" => "TOURISTS", "TAB" => 'Туристы', "TITLE" => 'Информация по туристам')
    );



    if ($_POST['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid() && ($_POST['SAVE'] || $_POST['APPLY'])) {

        // PROCESSING OF DATA
    }

    $tabControl = new CAdminTabControl("tabControl", $aTabs);

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    ?>
    <form method="POST" action="<? echo $APPLICATION->GetCurPage() ?>" name="order_form">
        <?
        $tabControl->Begin();

        foreach ($aTabs as $aTab) {

            $tabControl->BeginNextTab();
            if ($aTab['util']) {
                echo call_user_func($aTab['util']['func'], $aTab['util']['args']);
            }
        }
        $tabControl->End();
        ?>

    </form>
<?
} catch (Exception $e) {

    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    CAdminMessage::ShowMessage(array('MESSAGE' => $e->getMessage(), 'TYPE' => 'ERROR'));
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>

