<?php
if (!$USER->isAdmin())
    return;

\Bitrix\Main\Loader::includeModule("highloadblock");
\Bitrix\Main\Loader::includeModule("iblock");
\Bitrix\Main\Loader::includeModule("travelsoft.booking");

global $APPLICATION;

$mid = "travelsoft.booking";

function renderOptions($arOptions, $mid) {

    foreach ($arOptions as $name => $arValues) {
        
        $cur_opt_val = htmlspecialcharsbx(Bitrix\Main\Config\Option::get($mid, $name));
        $name = htmlspecialcharsbx($name);
        
        $options .= '<tr>';
        $options .= '<td width="40%">';
        $options .= '<label for="' . $name . '">' . $arValues['DESC'] . ':</label>';
        $options .= '</td>';
        $options .= '<td width="60%">';
        if ($arValues['TYPE'] == 'select') {
            
            $options .= '<select id="' . $name . '" name="' . $name . '">';
           foreach ($arValues['VALUES'] as $key => $value) {
                $options .= '<option '.($cur_opt_val == $key ? 'selected' : '').' value="'.$key.'">'.$value.'</option>';
            }
            $options .= '</select>';
            
        } elseif ($arValues['TYPE'] == 'text') {
            
            $options .= '<input type="text" name="'.$name.'" value="'.$cur_opt_val.'">';
        }
        $options .= '</td>';
        $options .= '</tr>';
    }
    echo $options;
}

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

$dbStatuses = travelsoft\booking\stores\Statuses::get();
foreach ($dbStatuses as $arStatus) {
    $arStatuses[$arStatus['ID']] = $arStatus['UF_NAME'];
}

$dbMails = CEventMessage::GetList($by = "site_id", $order = "desc", array('TYPE_ID' => "TRAVELSOFT_BOOKING"));
while ($arMail = $dbMails->Fetch()) {
    $arMails[$arMail['ID']] = $arMail['SUBJECT'] . "(" . $arMail['ID'] . ")";
}

$dbGroupsList = Bitrix\Main\GroupTable::getList(array("select" => array("ID", "NAME")))->fetchAll();

for ($i = 0, $cnt = count($dbGroupsList); $i < $cnt; $i++) {
    $arGroups[$dbGroupsList[$i]["ID"]] = $dbGroupsList[$i]["NAME"];
}

$main_options = array(
    'STORES' => array(
        "TOURS_IB" => array("DESC" => "Инфоблок туров", "VALUES" => $arIBS, 'TYPE' => 'select'),
        "FOOD_IB" => array("DESC" => "Инфоблок типов питания", "VALUES" => $arIBS, 'TYPE' => 'select'),
        "ORDERS_HL" => array("DESC" => "Таблица заказов", "VALUES" => $arHLS, 'TYPE' => 'select'),
        "CITIZENSHIP_HL" => array("DESC" => "Таблица гражданства", "VALUES" => $arHLS, 'TYPE' => 'select'),
        "PRICE_TYPES_HL" => array("DESC" => "Таблица типов цен", "VALUES" => $arHLS, 'TYPE' => 'select'),
        "PRICES_HL" => array("DESC" => "Таблица цен", "VALUES" => $arHLS, 'TYPE' => 'select'),
        "QUOTAS_HL" => array("DESC" => "Таблица квот", "VALUES" => $arHLS, 'TYPE' => 'select'),
        "DURATION_HL" => array("DESC" => "Таблица продолжительности услуги", "VALUES" => $arHLS, 'TYPE' => 'select'),
        "STATUSES_HL" => array("DESC" => "Таблица статусов заказа", "VALUES" => $arHLS, 'TYPE' => 'select'),
        "TOURISTS_HL" => array("DESC" => "Таблица туристов", "VALUES" => $arHLS, 'TYPE' => 'select'),
        "CRMSETTINGS_HL" => array("DESC" => "Таблица настроек crm", "VALUES" => $arHLS, 'TYPE' => 'select')
    ),
    'USER_GROUPS' => array(
        'MANAGERS_USER_GROUPS' => array("DESC" => "Группа пользователей для менеджеров", "VALUES" => $arGroups, 'TYPE' => 'select'),
        'AGENTS_USER_GROUPS' => array("DESC" => "Группа пользователей для агентов", "VALUES" => $arGroups, 'TYPE' => 'select'),
    ),
    'ORDERS' => array(
        'STATUS_ID_FOR_ORDER_CREATION' => array('DESC' => "При создании заказа устанавливать статус", "VALUES" => $arStatuses, 'TYPE' => 'select'),
        'MAIL_ID_FOR_CLIENT_MAKE_ORDER' => array('DESC' => "Письмо клиенту при создании заказа", "VALUES" => $arMails, 'TYPE' => 'select'),
        'MAIL_ID_FOR_AGENT_MAKE_ORDER' => array('DESC' => "Письмо агенту при создании заказа", "VALUES" => $arMails, 'TYPE' => 'select'),
        'MAIL_ID_FOR_MANAGER_MAKE_ORDER' => array('DESC' => "Письмо менеджеру при создании заказа", "VALUES" => $arMails, 'TYPE' => 'select'),
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
    array(
        "DIV" => "edit3",
        "TAB" => "Заказы",
        "ICON" => "",
        "TITLE" => "Укажите параметры работы с заказами"
    )
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

    foreach ($main_options as $arOption) {
        $o_tab->BeginNextTab();
        renderOptions($arOption, $mid);
    }
    $o_tab->Buttons(); ?>
    <input type="submit" name="save" value="Сохранить" title="Сохранить" class="adm-btn-save">
    <input type="submit" name="reset" title="Сбросить" OnClick="return confirm('Сбросить')" value="Сбросить">
    <?= bitrix_sessid_post(); ?>
    <? $o_tab->End(); ?>
</form>