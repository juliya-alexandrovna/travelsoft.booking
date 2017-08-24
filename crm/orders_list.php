<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Orders;
use Bitrix\Main\Entity\ExpressionField;

require_once 'header.php';

$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/orders-list.css'>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-3.2.1.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/crm.js?v=b'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/orders_list.js?v=am'></script>");


$sort = new CAdminSorting(\travelsoft\booking\crm\Utils::ORDERS_TABLE_ID, "ID", "DESC");
$list = new CAdminList(\travelsoft\booking\crm\Utils::ORDERS_TABLE_ID, $sort);

if ($arOrdersId = $list->GroupAction()) {

    if ($_REQUEST['action_target'] == 'selected') {

        $arOrdersId = array_keys(Orders::get(array('select' => array('ID'))));
    }

    foreach ($arOrdersId as $ID) {

        switch ($_REQUEST['action']) {

            case "delete":

                Orders::delete($ID);

                break;
        }
    }
}

if ($_REQUEST["by"]) {

    $by = $_REQUEST["by"];
}

if ($_REQUEST["order"]) {

    $order = $_REQUEST["order"];
}

$getParams = array("order" => array($by => $order));

$usePageNavigation = true;
$navParams = CDBResult::GetNavParams(CAdminResult::GetNavSize(
                        \travelsoft\booking\crm\Utils::ORDERS_TABLE_ID, array('nPageSize' => 20)
        ));

$navParams['PAGEN'] = (int) $navParams['PAGEN'];
$navParams['SIZEN'] = (int) $navParams['SIZEN'];

if ($usePageNavigation) {

    $totalCount = Orders::get(array('select' => array(new ExpressionField('CNT', 'COUNT(1)'))), false)->fetch();

    $totalCount = (int) $totalCount['CNT'];

    if ($totalCount > 0) {

        $totalPages = ceil($totalCount / $navParams['SIZEN']);
        if ($navParams['PAGEN'] > $totalPages) {

            $navParams['PAGEN'] = $totalPages;
        }
        $getParams['limit'] = $navParams['SIZEN'];
        $getParams['offset'] = $navParams['SIZEN'] * ($navParams['PAGEN'] - 1);
    } else {

        $navParams['PAGEN'] = 1;
        $getParams['limit'] = $navParams['SIZEN'];
        $getParams['offset'] = 0;
    }
}

$arOrders = Orders::get($getParams);

$arStatusesId = array_map(function ($arItem) {
    return $arItem['UF_STATUS_ID'];
}, $arOrders);

if ($arStatusesId) {
    $arStatuses = travelsoft\booking\stores\Statuses::get(array('filter' => array('ID' => $arStatusesId), 'select' => array('ID', 'UF_NAME')));
}

$dbResult = new CAdminResult($arOrders, \travelsoft\booking\crm\Utils::ORDERS_TABLE_ID);

if ($usePageNavigation) {

    $dbResult->NavStart($getParams['limit'], $navParams['SHOW_ALL'], $navParams['PAGEN']);
    $dbResult->NavRecordCount = $totalCount;
    $dbResult->NavPageCount = $totalPages;
    $dbResult->NavPageNomer = $navParams['PAGEN'];
} else {

    $dbResult->NavStart();
}

$list->NavText($dbResult->GetNavPrint('Страницы'));

$list->AddHeaders(\travelsoft\booking\crm\Utils::getOrdersTableHeaders());

while ($arOrder = $dbResult->Fetch()) {

    \travelsoft\booking\crm\Utils::prepareRowForOrdersTable($list->AddRow($arOrder["ID"], $arOrder), array(
        'STATUSES' => $arStatuses,
        'ORDER' => $arOrder
    ));
}

$list->AddFooter(array(
    array("title" => "Количество элементов", "value" => $dbResult->SelectedRowsCount()),
    array("counter" => true, "title" => "Количество выбранных элементов", "value" => 0)
));

$list->AddGroupActionTable(Array(
    "delete" => "Удалить"
));


$list->AddAdminContextMenu(array(array(
        'TEXT' => "Создать заказ",
        'TITLE' => "Создание заказа",
        'LINK' => 'travelsoft_crm_booking_order_edit.php?lang=' . LANG,
        'ICON' => 'btn_new'
)));

$list->CheckListMode();

$APPLICATION->SetTitle("Список заказов");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>
<script>
    CRM.config.table_id = "<?= \travelsoft\booking\crm\Utils::ORDERS_TABLE_ID ?>";
    CRM.config.sessid = "<?= bitrix_sessid() ?>";
    CRM.config.last_id = "<?= \travelsoft\booking\crm\Utils::getOrderLastId(); ?>";
    CRM.config.notifyIcon = "/local/templates/travelsoft/images/logo.png";
    CRM.config.notifyTitle = 'Новый заказ';
    CRM.config.notifyBody = '';
    CRM.config.notifySound = '/local/modules/travelsoft.booking/crm/audio/notify.mp3';
    CRM.config.time_interval = 60000;
</script>
<?
$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
