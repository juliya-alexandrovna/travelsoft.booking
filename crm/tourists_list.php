<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Tourists;
use Bitrix\Main\Entity\ExpressionField;

require_once 'header.php';

$TABLE_ID = \travelsoft\booking\crm\Settings::TOURISTS_HTML_TABLE_ID;

$sort = new CAdminSorting($TABLE_ID, "ID", "DESC");
$list = new CAdminList($TABLE_ID, $sort);

if ($arTouristsId = $list->GroupAction()) {

    if ($_REQUEST['action_target'] == 'selected') {

        $arTouristsId = array_keys(Tourists::get(array('select' => array('ID'))));
    }

    foreach ($arTouristsId as $ID) {

        switch ($_REQUEST['action']) {

            case "delete":

                Tourists::delete($ID);

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

$getParams = array("filter" => travelsoft\booking\crm\Utils::getTouristsFilter(), "order" => array($by => $order), 'select' => array('ID', 'UF_NAME', 'UF_LAST_NAME', 'UF_SECOND_NAME'));

$usePageNavigation = true;
$navParams = CDBResult::GetNavParams(CAdminResult::GetNavSize(
                        $TABLE_ID, array('nPageSize' => 20)
        ));

$navParams['PAGEN'] = (int) $navParams['PAGEN'];
$navParams['SIZEN'] = (int) $navParams['SIZEN'];



if ($usePageNavigation) {

    $arTotalCount = Tourists::get(array('select' => array(new ExpressionField('CNT', 'COUNT(1)'))), false)->fetch();

    $totalCount = (int) $arTotalCount['CNT'];

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

$arTourists = Tourists::get($getParams);

$dbResult = new CAdminResult($arTourists, $TABLE_ID);

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
        "content" => "ID туриста",
        "sort" => "ID",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_NAME",
        "content" => "Имя",
        "sort" => "UF_NAME",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_SECOND_NAME",
        "content" => "Отчество",
        "sort" => "UF_SECOND_NAME",
        "align" => "center",
        "default" => true
    ),
    array(
        "id" => "UF_LAST_NAME",
        "content" => "Фамилия",
        "align" => "center",
        "default" => true
    )
));

while ($arResult = $dbResult->Fetch()) {

    $row = &$list->AddRow($arResult["ID"], $arResult);

    $row->AddActions(array(
        array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => "Изменить",
            "ACTION" => $list->ActionRedirect("travelsoft_crm_booking_tourist_edit.php?ID=" . $arResult["ID"])
        ),
        array(
            "ICON" => "delete",
            "DEFAULT" => true,
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Действительно хотите удалить туриста')) " . $list->ActionDoGroup($arResult["ID"], "delete")
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
        'TEXT' => "Добавить туриста",
        'TITLE' => "Добавить туриста",
        'LINK' => 'travelsoft_crm_booking_tourist_edit.php?lang=' . LANG,
        'ICON' => 'btn_new'
)));

$list->CheckListMode();

$APPLICATION->SetTitle("Список туристов");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

\travelsoft\booking\crm\Utils::showFilterForm(
        array(
            'table_id' => $TABLE_ID,
            'form_elements' => array(
                array(
                    'label' => 'Нужна виза',
                    'view' => SelectBoxFromArray("UF_NEED_VISA", array(
                        "REFERENCE" => array("Нет", "Да"),
                        "REFERENCE_ID" => array(0, 1)
                            ), $_GET['UF_NEED_VISA'], "", 'class="adm-filter-select"', false, "find_form")
                ),
                array(
                    'label' => 'Нужна страховка',
                    'view' => SelectBoxFromArray("UF_NEED_INSUR", array(
                        "REFERENCE" => array("Нет", "Да"),
                        "REFERENCE_ID" => array(0, 1)
                            ), $_GET['UF_NEED_INSUR'], "", 'class="adm-filter-select"', false, "find_form")
                )
            ),
        )
);

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
