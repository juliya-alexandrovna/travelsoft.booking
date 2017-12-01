<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\crm\stores\CashDesks;
use Bitrix\Main\Entity\ExpressionField;
use travelsoft\booking\crm\Settings;

require_once 'header.php';

$sort = new CAdminSorting(Settings::CASH_DESKS_HTML_TABLE_ID, "ID", "DESC");
$list = new CAdminList(Settings::CASH_DESKS_HTML_TABLE_ID, $sort);

if ($arCashDesksId = $list->GroupAction()) {

    if ($_REQUEST['action_target'] == 'selected') {

        $arCashDesksId = array_keys(CashDesks::get(array('select' => array('ID'))));
    }

    foreach ($arCashDesksId as $ID) {

        switch ($_REQUEST['action']) {

            case "delete":

                CashDesks::delete($ID);

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
Settings::CASH_DESKS_HTML_TABLE_ID, array('nPageSize' => 20)
        ));

$navParams['PAGEN'] = (int) $navParams['PAGEN'];
$navParams['SIZEN'] = (int) $navParams['SIZEN'];

if ($usePageNavigation) {

    $totalCount = CashDesks::get(array('select' => array(new ExpressionField('CNT', 'COUNT(1)'))), false)->fetch();

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

$arCashDesks = CashDesks::get($getParams);

$dbResult = new CAdminResult($arCashDesks, Settings::CASH_DESKS_HTML_TABLE_ID);

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
        "id" => "UF_NAME",
        "content" => "Название",
        "sort" => "UF_NAME",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_ACTIVE",
        "content" => "Активность",
        "sort" => "UF_ACTIVE",
        "align" => "center",
        "default" => true
    )
));

while ($arCashDesk = $dbResult->Fetch()) {

    $row = &$list->AddRow($arCashDesk["ID"], $arCashDesk);
    
    $row->AddViewField('UF_ACTIVE', $arCashDesk['UF_ACTIVE'] == 1 ? "Да" : "Нет");
    
    $row->AddActions(array(
        array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => "Изменить",
            "ACTION" => 'BX.adminPanel.Redirect([], "'.Settings::CASH_DESK_EDIT_URL.'?ID=' . $arCashDesk["ID"] . '", event);'
        ),
        array(
            "ICON" => "delete",
            "DEFAULT" => true,
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Действительно хотите удалить кассу')) " . $list->ActionDoGroup($arCashDesk["ID"], "delete")
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
        'TEXT' => "Создать кассу",
        'TITLE' => "Создание кассы",
        'LINK' => Settings::CASH_DESK_EDIT_URL . '?lang=' . LANG,
        'ICON' => 'btn_new'
)));

$list->CheckListMode();

$APPLICATION->SetTitle("Кассы");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
