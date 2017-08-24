<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
//use Bitrix\Main\Entity\ExpressionField;
use travelsoft\booking\stores\Users;

require_once 'header.php';

$TABLE_ID = "CLIENTS_LIST";

$sort = new CAdminSorting($TABLE_ID, "ID", "DESC");
$list = new CAdminList($TABLE_ID, $sort);

if ($arClientsId = $list->GroupAction()) {

    if ($_REQUEST['action_target'] == 'selected') {

        $arClientsId = array_keys(Users::get(array('select' => array('ID'))));
    }

    foreach ($arClientsId as $ID) {

        switch ($_REQUEST['action']) {

            case "delete":

                Users::delete($ID);

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

$getParams = array(
    "order" => array($by => $order),
    'filter' => array('GROUPS_ID' => array(\travelsoft\booking\Settings::clientsUGroup())),
    'select' => array('ID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHONE')
);

//$dbGroups = CGroup::GetList(($by = "c_sort"), ($order = "desc"));
//$agent = travelsoft\booking\Settings::managersUGroup();
//while ($arGroup = $dbGroups->Fetch()) {
//
//    if ($agent != $arGroup['ID']) {
//
//        $getParams['filter']['GROUPS_ID'][] = $arGroup['ID'];
//    }
//};
//
//$getParams['filter']['GROUPS_ID'] = implode(' | ', $getParams['filter']['GROUPS_ID']);

//$usePageNavigation = true;
//$navParams = CDBResult::GetNavParams(CAdminResult::GetNavSize(
//                        $TABLE_ID, array('nPageSize' => 20)
//        ));
//
//if ($navParams['SHOW_ALL']) {
//
//    $usePageNavigation = false;
//} else {
//
//    $navParams['PAGEN'] = (int) $navParams['PAGEN'];
//    $navParams['SIZEN'] = (int) $navParams['SIZEN'];
//}
//if ($usePageNavigation) {
//
//    $totalCount = Users::get(array('filter' => array('!GROUPS_ID' => array(travelsoft\booking\Settings::managersUGroup())),'select' => array(new ExpressionField('CNT', 'COUNT(1)'))), false)->fetch();
//
//    $totalCount = (int) $totalCount['CNT'];
//
//    if ($totalCount > 0) {
//
//        $totalPages = ceil($totalCount / $navParams['SIZEN']);
//        if ($navParams['PAGEN'] > $totalPages) {
//
//            $navParams['PAGEN'] = $totalPages;
//        }
//        $getParams['limit'] = $navParams['SIZEN'];
//        $getParams['offset'] = $navParams['SIZEN'] * ($navParams['PAGEN'] - 1);
//    } else {
//
//        $navParams['PAGEN'] = 1;
//        $getParams['limit'] = $navParams['SIZEN'];
//        $getParams['offset'] = 0;
//    }
//}

$arClients = Users::get($getParams);

$dbResult = new CAdminResult($arClients, $TABLE_ID);

//if ($usePageNavigation) {
//
//    $dbResult->NavStart($getParams['limit'], $navParams['SHOW_ALL'], $navParams['PAGEN']);
//    $dbResult->NavRecordCount = $totalCount;
//    $dbResult->NavPageCount = $totalPages;
//    $dbResult->NavPageNomer = $navParams['PAGEN'];
//} else {

$dbResult->NavStart();
//}

$list->NavText($dbResult->GetNavPrint('Страницы'));

$list->AddHeaders(array(
    array(
        "id" => "ID",
        "content" => "ID клиента",
        "sort" => "ID",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "NAME",
        "content" => "Имя",
        "sort" => "NAME",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "SECOND_NAME",
        "content" => "Отчество",
        "sort" => "SECOND_NAME",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "LAST_NAME",
        "content" => "Фамилия",
        "sort" => "LAST_NAME",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "EMAIL",
        "content" => "Email",
        "sort" => "EMAIL",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "PERSONAL_PHONE",
        "content" => "Телефон",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "IS_AGENT",
        "content" => "Является агентом",
        "align" => "center",
        "default" => true
    )
));

while ($arResult = $dbResult->Fetch()) {

    $row = &$list->AddRow($arResult["ID"], $arResult);

    $isAgent = 'Нет';
    if (in_array(travelsoft\booking\Settings::agentsUGroup(), $GLOBALS['USER']->GetUserGroup($arResult['ID']))) {

        $isAgent = 'Да';
    }

    $row->AddViewField("IS_AGENT", $isAgent);

    $row->AddActions(array(
        array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => "Изменить",
            "ACTION" => $list->ActionRedirect("travelsoft_crm_booking_client_edit.php?ID=" . $arResult["ID"])
        ),
        array(
            "ICON" => "delete",
            "DEFAULT" => true,
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Действительно хотите удалить клиента')) " . $list->ActionDoGroup($arResult["ID"], "delete")
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
        'TEXT' => "Добавить клиента",
        'TITLE' => "Добавить клиента",
        'LINK' => 'travelsoft_crm_booking_client_edit.php?lang=' . LANG,
        'ICON' => 'btn_new'
)));

$list->CheckListMode();

$APPLICATION->SetTitle("Список клиентов");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
