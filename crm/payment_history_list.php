<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\PaymentHistory;
use travelsoft\booking\stores\PaymentsTypes;
use travelsoft\booking\crm\stores\CashDesks;
use travelsoft\booking\stores\Orders;
use travelsoft\booking\stores\Users;
use Bitrix\Main\Entity\ExpressionField;
use travelsoft\booking\crm\Settings;
use travelsoft\booking\crm\Utils;

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

$getParams = array("filter" => Utils::getPaymentHistoryFilter(), "order" => array($by => $order));

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
$arCashDesks = CashDesks::get(array('select' => array("ID", "UF_NAME")));
$arFilterSelectCashDesksData = Utils::getReferencesSelectData($arCashDesks, 'UF_NAME', 'ID');

// linked payments types
$arPaymentsTypes = PaymentsTypes::get(array('select' => array("ID", "UF_NAME")));
$arFilterSelectPaymentTypeData = Utils::getReferencesSelectData($arPaymentsTypes, 'UF_NAME', 'ID');

// linked creaters
$arCreaters = Users::get(array('select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL')), true, function (&$arCreater) {
            $arCreater['FULL_NAME'] = Users::getFullUserNameWithEmailByFields($arCreater);
        });
$arFilterSelectCreatersData = Utils::getReferencesSelectData($arCreaters, 'FULL_NAME', 'ID');

// linked orders
$arOrders = Orders::get(array('select' => array("ID")));
$arFilterSelectOrdersData = Utils::getReferencesSelectData($arOrders, 'ID', 'ID');

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

    $row->AddViewField('UF_ORDER_ID', '<a target="__blank" href="' . Settings::ORDER_EDIT_URL . '?ID=' . $arPaymentElementHistory['UF_ORDER_ID'] . '">' . $arPaymentElementHistory['UF_ORDER_ID'] . '</a>');
    $row->AddViewField('ID', '<a href="' . Settings::PAYMENT_HISTORY_EDIT_URL . '?ID=' . $arPaymentElementHistory['ID'] . '">' . $arPaymentElementHistory['ID'] . '</a>');

    if (isset($arCashDesks[$arPaymentElementHistory['UF_CASH_DESK_ID']])) {
        $row->AddViewField("UF_CASH_DESK_ID", $arCashDesks[$arPaymentElementHistory['UF_CASH_DESK_ID']]['UF_NAME']);
    }

    if (isset($arPaymentsTypes[$arPaymentElementHistory['UF_PAYMENT_TYPE_ID']])) {
        $row->AddViewField("UF_PAYMENT_TYPE_ID", $arPaymentsTypes[$arPaymentElementHistory['UF_PAYMENT_TYPE_ID']]['UF_NAME']);
    }

    if (isset($arCreaters[$arPaymentElementHistory['UF_CREATER']])) {
        $row->AddViewField("UF_CREATER", $arCreaters[$arPaymentElementHistory['UF_CREATER']]['FULL_NAME_WITH_EMAIL']);
    }

    $row->AddActions(array(
        array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => "Изменить",
            "ACTION" => 'BX.adminPanel.Redirect([], "' . Settings::PAYMENT_HISTORY_EDIT_URL . '?ID=' . $arPaymentElementHistory["ID"] . '", event);'
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

$arCurrencies = (new travelsoft\booking\adapters\CurrencyConverter)->getListOfCurrency();

\travelsoft\booking\crm\Utils::showFilterForm(
        array(
            'table_id' => Settings::PAYMENT_HISTORY_HTML_TABLE_ID,
            'form_elements' => array(
                array(
                    'label' => 'Дата создания c',
                    'view' => CAdminCalendar::CalendarDate('UF_DATE_CREATE[0]', $_GET['UF_DATE_CREATE'][0], 19, true)
                ),
                array(
                    'label' => 'Дата создания по',
                    'view' => CAdminCalendar::CalendarDate('UF_DATE_CREATE[1]', $_GET['UF_DATE_CREATE'][1], 19, true)
                ),
                array(
                    'label' => 'Создатель',
                    'view' => SelectBoxFromArray("UF_CREATER", $arFilterSelectCreatersData, $_GET['UF_CREATER'], "", 'class="adm-filter-select"', false, "find_form")
                ),
                array(
                    'label' => 'Номер путевки',
                    'view' => SelectBoxFromArray("UF_ORDER_ID", $arFilterSelectOrdersData, $_GET['UF_ORDER_ID'], "", 'class="adm-filter-select"', false, "find_form")
                ),
                array(
                    'label' => 'Касса',
                    'view' => SelectBoxFromArray("UF_CASH_DESK_ID", $arFilterSelectCashDesksData, $_GET['UF_CASH_DESK_ID'], "", 'class="adm-filter-select"', false, "find_form")
                ),
                array(
                    'label' => 'Тип платежа',
                    'view' => SelectBoxFromArray("UF_PAYMENT_TYPE_ID", $arFilterSelectPaymentTypeData, $_GET['UF_PAYMENT_TYPE_ID'], "", 'class="adm-filter-select"', false, "find_form")
                ),
                array(
                    'label' => 'Валюта',
                    'view' => SelectBoxFromArray("UF_CURRENCY", array('REFERENCE' => $arCurrencies, 'REFERENCE_ID' => $arCurrencies), $_GET['UF_CURRENCY'], "", 'class="adm-filter-select"', false, "find_form")
                )
            )
        )
);

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
