<?

/** @global CMain $APPLICATION */
/** @global CDatabase $DB */

/** @global CUser $USER */
use travelsoft\booking\stores\Tours;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

Bitrix\Main\Loader::includeModule("travelsoft.booking");

if (!travelsoft\booking\crmAccess()) {

    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$tableId = "tours_list";

$sort = new CAdminSorting($tableId, "ID", "DESC");
$list = new CAdminList($tableId, $sort);

if ($list->EditAction()) {

    foreach ($FIELDS as $id => $arFields) {

        if (!$list->IsUpdated($id)) {

            continue;
        }
        $id = intVal($id);
        if (!empty(Tours::getById($id))) {

            foreach ($arFields as $key => $value) {

                $arSave[$key] = $value;
            }

            if (!Tours::update($id, $arSave)) {

                $list->AddGroupError("Не удалось обновить тур", $id);
            }
        } else {

            $list->AddGroupError("Не удалось найти тур", $id);
        }
    }
}

if ($list->GroupAction()) {
    
}

if ($_REQUEST["by"]) {

    $by = $_REQUEST["by"];
}

if ($_REQUEST["order"]) {

    $order = $_REQUEST["order"];
}

$dbResult = new CAdminResult(Tours::get(array("order" => array($by => $order), "select" => array("ID", "NAME", "ACTIVE"))), $tableId);

$dbResult->NavStart();

$list->NavText($dbResult->GetNavPrint('Страницы'));

$list->AddHeaders(array(
    array(
        "id" => "ID",
        "content" => "ID",
        "sort" => "id",
        "align" => "left",
        "default" => true
    ),
    array(
        "id" => "NAME",
        "content" => "Название",
        "sort" => "name",
        "align" => "left",
        "default" => true
    ),
    array(
        "id" => "ACTIVE",
        "content" => "Активен",
        "align" => "center",
        "default" => true
    )
));

while ($arResult = $dbResult->Fetch()) {

    $row = &$list->AddRow($arResult["ID"], $arResult);

    $row->AddInputField("NAME", array("size" => 35));
    $row->AddViewField("NAME", '<a href="travelsoft_crm_booking_tour_edit.php?ID=' . $arResult["ID"] . '&lang=' . LANG . '">' . $arResult["NAME"] . '</a>');
    $row->AddCheckField("ACTIVE");

    $row->AddActions(array(
        array(
            "ICON" => "edit",
            "DEFAULT" => true,
            "TEXT" => "Изменить",
            "ACTION" => $list->ActionRedirect("travelsoft_crm_booking_tour_edit.php?ID=" . $arResult["ID"])
        ),
        array(
            "ICON" => "delete",
            "DEFAULT" => true,
            "TEXT" => "Удалить",
            "ACTION" => "if(confirm('Действительно хотите удалить тур')) " . $list->ActionDoGroup($arResult["ID"], "delete")
        )
    ));
}

$list->AddFooter(array(
    array("title" => "Количество элементов", "value" => $dbResult->SelectedRowsCount()),
    array("counter" => true, "title" => "Количество выбранных элементов", "value" => 0)
));

$list->AddGroupActionTable(Array(
    "delete" => "Удалить",
    "activate" => "Активировать",
    "deactivate" => "Деактивировать",
));

$list->CheckListMode();

$APPLICATION->SetTitle("Список туров");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$list->DisplayList();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
