<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\PaymentsTypes;
use Bitrix\Main\Entity\ExpressionField;
use travelsoft\booking\crm\Settings;

require_once 'header.php';

$sort = new CAdminSorting(Settings::PAYMENTS_TYPES_HTML_TABLE_ID, "ID", "DESC");
$list = new CAdminList(Settings::PAYMENTS_TYPES_HTML_TABLE_ID, $sort);

if ($arPayentTypesId = $list->GroupAction()) {

    if ($_REQUEST['action_target'] == 'selected') {

        $arPayentTypesId = array_keys(PaymentsTypes::get(array('select' => array('ID'))));
    }

    foreach ($arPayentTypesId as $ID) {

        switch ($_REQUEST['action']) {

            case "delete":

                PaymentsTypes::delete($ID);

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
Settings::PAYMENTS_TYPES_HTML_TABLE_ID, array('nPageSize' => 20)
        ));

$navParams['PAGEN'] = (int) $navParams['PAGEN'];
$navParams['SIZEN'] = (int) $navParams['SIZEN'];

if ($usePageNavigation) {

    $totalCount = PaymentsTypes::get(array('select' => array(new ExpressionField('CNT', 'COUNT(1)'))), false)->fetch();

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

$arPayentTypes = PaymentsTypes::get($getParams);

$dbResult = new CAdminResult($arPayentTypes, Settings::PAYMENTS_TYPES_HTML_TABLE_ID);

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
    ),
    array(
        "id" => "UF_SHOW_FOR_USER",
        "content" => "Показывать пользователю в качетве варианта оплаты",
        "sort" => "UF_SHOW_FOR_USER",
        "align" => "center",
        "default" => true
    )
));

while ($arPayentType = $dbResult->Fetch()) {

    $row = &$list->AddRow($arPayentType["ID"], $arPayentType);
    
    $row->AddViewField('UF_ACTIVE', $arPayentType['UF_ACTIVE'] == 1 ? "Да" : "Нет");
    $row->AddViewField('UF_SHOW_FOR_USER', $arPayentType['UF_SHOW_FOR_USER'] == 1 ? "Да" : "Нет");
    
    $row->AddActions(array(
        array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => "Изменить",
            "ACTION" => 'BX.adminPanel.Redirect([], "'.Settings::PAYMENT_TYPE_EDIT_URL.'?ID=' . $arPayentType["ID"] . '", event);'
        ),
        array(
            "ICON" => "delete",
            "DEFAULT" => true,
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Действительно хотите удалить тип оплаты')) " . $list->ActionDoGroup($arPayentType["ID"], "delete")
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
        'TEXT' => "Создать тип оплаты",
        'TITLE' => "Создание типа оплаты",
        'LINK' => Settings::PAYMENT_TYPE_EDIT_URL . '?lang=' . LANG,
        'ICON' => 'btn_new'
)));

$list->CheckListMode();

$APPLICATION->SetTitle("Типы оплаты");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
