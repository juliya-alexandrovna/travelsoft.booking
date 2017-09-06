<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\crm\stores\PaymentHistory;
use travelsoft\booking\stores\PaymentsTypes;
use travelsoft\booking\crm\stores\CashDesks;
use travelsoft\booking\stores\Orders;
use travelsoft\booking\stores\Users;
use Bitrix\Main\Entity\ExpressionField;
use travelsoft\booking\crm\Settings;

require_once 'header.php';

$sort = new CAdminSorting(Settings::PAYMENT_HISTORY_HTML_TABLE_ID, "ID", "DESC");
$list = new CAdminList(Settings::PAYMENT_HISTORY_HTML_TABLE_ID, $sort);

if ($arPaymentId = $list->GroupAction()) {

    if ($_REQUEST['action_target'] == 'selected') {

        $arPaymentId = array_keys(PaymentHistory::get(array('select' => array('ID'))));
    }

    foreach ($arPaymentId as $ID) {

        switch ($_REQUEST['action']) {

            case "delete":

                PaymentHistory::delete($ID);

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
Settings::PAYMENT_HISTORY_HTML_TABLE_ID, array('nPageSize' => 20)
        ));

$navParams['PAGEN'] = (int) $navParams['PAGEN'];
$navParams['SIZEN'] = (int) $navParams['SIZEN'];

if ($usePageNavigation) {

    $totalCount = PaymentHistory::get(array('select' => array(new ExpressionField('CNT', 'COUNT(1)'))), false)->fetch();

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

$arPaymentElementHistories = PaymentHistory::get($getParams);

// linked cash desks
$arCashDesksId = array_filter(array_map(function ($arItem) {return (int)$arItem['UF_CASH_DESK_ID'];}, $arPaymentElementHistories), function ($item) {
    return $item > 0;
});

if (!empty($arCashDesksId)) {
    $arCashDesks = CashDesks::get(array('filter' => $arCashDesksId, 'select' => array("ID", "UF_NAME")));
}

// linked payments types
$arPaymentsTypesId = array_filter(array_map(function ($arItem) {return (int)$arItem['UF_PAYMENT_TYPE_ID'];}, $arPaymentElementHistories), function ($item) {
    return $item > 0;
});

if (!empty($arPaymentsTypesId)) {
    $arPaymentsTypes = PaymentsTypes::get(array('filter' => $arPaymentsTypesId, 'select' => array("ID", "UF_NAME")));
}

// linked creaters
$arCreatersId = array_filter(array_map(function ($arItem) {return (int)$arItem['UF_CREATER'];}, $arPaymentElementHistories), function ($item) {
    return $item > 0;
});

if (!empty($arCreatersId)) {
    $arCreaters = Users::get(array('filter' => array('ID' => implode('|', $arCreatersId)), 'select' => array('ID','NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL')));
}

$dbResult = new CAdminResult($arPaymentElementHistories, Settings::PAYMENT_HISTORY_HTML_TABLE_ID);

if ($usePageNavigation) {

    $dbResult->NavStart($getParams['limit'], $navParams['SHOW_ALL'], $navParams['PAGEN']);
    $dbResult->NavRecordCount = $totalCount;
    $dbResult->NavPageCount = $totalPages;
    $dbResult->NavPageNomer = $navParams['PAGEN'];
} else {

    $dbResult->NavStart();
}

$list->NavText($dbResult->GetNavPrint('Страницы'));

$list->AddHeaders(array(
    array(
        "id" => "ID",
        "content" => "ID",
        "sort" => "ID",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_PRICE",
        "content" => "Сумма",
        "sort" => "UF_PRICE",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_CURRENCY",
        "content" => "Валюта",
        "sort" => "UF_CURRENCY",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_CASH_DESK_ID",
        "content" => "Касса",
        "sort" => "UF_CASH_DESK_ID",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_PAYMENT_TYPE_ID",
        "content" => "Тип платежа",
        "sort" => "UF_PAYMENT_TYPE_ID",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_ORDER_ID",
        "content" => "Номер путевки",
        "sort" => "UF_ORDER_ID",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_CREATER",
        "content" => "Создатель",
        "sort" => "UF_CREATER",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_DATE_CREATE",
        "content" => "Дата создания",
        "sort" => "UF_DATE_CREATE",
        "align" => "center",
        "default" => true
    )
));

while ($arPaymentElementHistory = $dbResult->Fetch()) {
    
    $row = &$list->AddRow($arPaymentElementHistory["ID"], $arPaymentElementHistory);
    
    $row->AddViewField('UF_ORDER_ID', '<a target="__blank" href="'.Settings::ORDER_EDIT_URL.'?ID='.$arPaymentElementHistory['UF_ORDER_ID'].'">'.$arPaymentElementHistory['UF_ORDER_ID'].'</a>');
    $row->AddViewField('ID', '<a href="'.Settings::PAYMENT_HISTORY_EDIT_URL.'?ID='.$arPaymentElementHistory['ID'].'">'.$arPaymentElementHistory['ID'].'</a>');
    
    if (isset($arCashDesks[$arPaymentElementHistory['UF_CASH_DESK_ID']])) {
        $row->AddViewField("UF_CASH_DESK_ID", $arCashDesks[$arPaymentElementHistory['UF_CASH_DESK_ID']]['UF_NAME']);
    }
    
    if (isset($arPaymentsTypes[$arPaymentElementHistory['UF_PAYMENT_TYPE_ID']])) {
        $row->AddViewField("UF_PAYMENT_TYPE_ID", $arPaymentsTypes[$arPaymentElementHistory['UF_PAYMENT_TYPE_ID']]['UF_NAME']);
    }
    
    if (isset($arCreaters[$arPaymentElementHistory['UF_CREATER']])) {
        $row->AddViewField("UF_CREATER", Users::getFullUserNameWithEmailByFields($arCreaters[$arPaymentElementHistory['UF_CREATER']]));
    }
    
    
    
    $row->AddActions(array(
        array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => "Изменить",
            "ACTION" => 'BX.adminPanel.Redirect([], "'.Settings::PAYMENT_HISTORY_EDIT_URL.'?ID=' . $arPaymentElementHistory["ID"] . '", event);'
        ),
        array(
            "ICON" => "delete",
            "DEFAULT" => true,
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Действительно хотите удалить элемент истории платежа')) " . $list->ActionDoGroup($arPaymentElementHistory["ID"], "delete")
        )
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
        'TEXT' => "Создать элемент истории платежа",
        'TITLE' => "Создание элемента истории платежа",
        'LINK' => Settings::PAYMENT_HISTORY_EDIT_URL . '?lang=' . LANG,
        'ICON' => 'btn_new'
)));

$list->CheckListMode();

$APPLICATION->SetTitle("История платежей");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
