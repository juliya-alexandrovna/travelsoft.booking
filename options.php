<?php
if (!$USER->isAdmin())
    return;

\Bitrix\Main\Loader::includeModule("highloadblock");
\Bitrix\Main\Loader::includeModule("iblock");

global $APPLICATION;

$mid = "travelsoft.booking";

$dbHLList = Bitrix\Highloadblock\HighloadBlockTable::getList(array(
            "order" => array("ID" => "ASC")
        ))->fetchAll();

foreach ($dbHLList as $arHL) {
    $arHLS[$arHL["ID"]] = $arHL["NAME"];
}

$dbIBList = CIBlock::GetList(
                array(), array("ACTIVE" => "Y")
);
while ($arIB = $dbIBList->Fetch()) {
    $arIBS[$arIB["ID"]] = $arIB["NAME"];
}

$dbGroupsList = Bitrix\Main\GroupTable::getList(array("select" => array("ID", "NAME")))->fetchAll();

for ($i = 0, $cnt = count($dbGroupsList); $i < $cnt; $i++) {
    $arGroups[$dbGroupsList[$i]["ID"]] = $dbGroupsList[$i]["NAME"];
}

$main_options = array(
    'STORES' => array(
        "TOURS_IB" => array("DESC" => "Инфоблок туров", "VALUES" => $arIBS),
        "FOOD_IB" => array("DESC" => "Инфоблок типов питания", "VALUES" => $arIBS),
        "ORDERS_HL" => array("DESC" => "Таблица заказов", "VALUES" => $arHLS),
        "CITIZENSHIP_HL" => array("DESC" => "Таблица гражданства", "VALUES" => $arHLS),
        "PRICE_TYPES_HL" => array("DESC" => "Таблица типов цен", "VALUES" => $arHLS),
        "PRICES_HL" => array("DESC" => "Таблица цен", "VALUES" => $arHLS),
        "QUOTAS_HL" => array("DESC" => "Таблица квот", "VALUES" => $arHLS),
        "STATUSES_HL" => array("DESC" => "Таблица статусов заказа", "VALUES" => $arHLS),
        "TOURISTS_HL" => array("DESC" => "Таблица туристов", "VALUES" => $arHLS),
        "CRMSETTINGS_HL" => array("DESC" => "Таблица настроек crm", "VALUES" => $arHLS)
    ),
    'USER_GROUPS' => array(
        'MANAGERS_USER_GROUPS' => array("DESC" => "Группа пользователей для менеджеров", "VALUES" => $arGroups),
        'AGENTS_USER_GROUPS' => array("DESC" => "Группа пользователей для агентов", "VALUES" => $arGroups),
    )
);

$tabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => "Хранение данных",
        "ICON" => "",
        "TITLE" => "Укажите необходимые инфоблоки, highloadblock'и"
    ),
    array(
        "DIV" => "edit2",
        "TAB" => "Группы пользователей",
        "ICON" => "",
        "TITLE" => "Укажите гуппы пользователей"
    ),
);

$o_tab = new CAdminTabControl("TravelsoftTabControl", $tabs);
if ($REQUEST_METHOD == "POST" && strlen($save . $reset) > 0 && check_bitrix_sessid()) {

    if (strlen($reset) > 0) {
        foreach ($main_options as $name => $desc) {
            \Bitrix\Main\Config\Option::delete($mid, array('name' => $name));
        }
    } else {
        foreach ($main_options as $arBlockOption) {

            foreach ($arBlockOption as $name => $arValues) {
                if (isset($_REQUEST[$name])) {
                    \Bitrix\Main\Config\Option::set($mid, $name, $_REQUEST[$name]);
                }
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(LANGUAGE_ID) . "&" . $o_tab->ActiveTabParam());
}
$o_tab->Begin();
?>

<form method="post" action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<? echo LANGUAGE_ID ?>">
    <?
    $o_tab->BeginNextTab();
    foreach ($main_options["STORES"] as $name => $arValues):
        $cur_opt_val = htmlspecialcharsbx(Bitrix\Main\Config\Option::get($mid, $name));
        $name = htmlspecialcharsbx($name);
        ?>
        <tr>
            <td width="40%">
                <label for="<? echo $name ?>"><? echo $arValues['DESC'] ?>:</label>
            </td>
            <td width="60%">
                <select id="<? echo $name ?>" name="<? echo $name ?>">
                    <? foreach ($arValues['VALUES'] as $key => $value) : ?>
                        <option <? if ($cur_opt_val == $key) : ?>selected<? endif ?> value="<?= $key ?>"><?= $value ?></option>
                    <? endforeach ?>
                </select>
            </td>
        </tr>
    <? endforeach ?>
    <?
    $o_tab->BeginNextTab();
    foreach ($main_options["USER_GROUPS"] as $name => $arValues):
        $cur_opt_val = htmlspecialcharsbx(Bitrix\Main\Config\Option::get($mid, $name));
        $name = htmlspecialcharsbx($name);
        ?>
        <tr>
            <td width="40%">
                <label for="<? echo $name ?>"><? echo $arValues['DESC'] ?>:</label>
            </td>
            <td width="60%">
                <select id="<? echo $name ?>" name="<? echo $name ?>">
                    <? foreach ($arValues['VALUES'] as $key => $value) : ?>
                        <option <? if ($cur_opt_val == $key) : ?>selected<? endif ?> value="<?= $key ?>"><?= $value ?></option>
                    <? endforeach ?>
                </select>
            </td>
        </tr>
    <? endforeach ?>
    <? $o_tab->Buttons(); ?>
    <input type="submit" name="save" value="Сохранить" title="Сохранить" class="adm-btn-save">
    <input type="submit" name="reset" title="Сбросить" OnClick="return confirm('Сбросить')" value="Сбросить">
    <?= bitrix_sessid_post(); ?>
    <? $o_tab->End(); ?>
</form>