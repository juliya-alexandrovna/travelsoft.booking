<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
require_once 'header.php';

$APPLICATION->SetTitle("Цены и наличие мест");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/jquery-ui.min.css'>");
$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/jquery-ui.multidatespicker.css'>");
$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/select2.min.css'>");
$APPLICATION->AddHeadString("<link rel='stylesheet' href='/local/modules/travelsoft.booking/crm/css/styles.css'>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-3.2.1.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-ui.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/jquery-ui.multidatespicker.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/plugins/select2.full.min.js'></script>");
$APPLICATION->AddHeadString("<script src='/local/modules/travelsoft.booking/crm/js/add_prices.js'></script>");

$settings = array();
$errors = array();
$userSettings = \travelsoft\booking\crm\stores\Settings::get();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {

    # обработка запроса формы настроек
    $settingsFormResponse = \travelsoft\booking\crm\Utils::processSettingsFromRequest((array) $_POST['settings'], $userSettings);

    if (!empty($settingsFormResponse)) {

        travelsoft\booking\crm\Utils::sendJsonResponse(Bitrix\Main\Web\Json::encode($settingsFormResponse));
    }

    # обработка запроса на сохранение цен и квот
    $priceAndQuotasFormResponse = travelsoft\booking\crm\Utils::processPriceAndQuotasFormRequest((array) $_POST['prices_and_quotas']);

    if (!empty($priceAndQuotasFormResponse)) {

        travelsoft\booking\crm\Utils::sendJsonResponse(Bitrix\Main\Web\Json::encode($priceAndQuotasFormResponse));
    }
}

$preparedUserSettings = travelsoft\booking\crm\Utils::getPreparedUserSettings($userSettings);

$tabControl = new \CAdminTabControl("tabControl", array(array("DIV" => "edit1", "TAB" => "$$$", "ICON" => "main_user_edit", "TITLE" => "")));
$tabControl->Begin();
$tabControl->BeginNextTab();

# вывод контента
$visible = !empty($preparedUserSettings[$_POST['settings']['tourid']]);
?>
<tr>
    <td>
        <form id="settings-form" method="post" action="<?= $APPLICATION->GetCurPage("lang=" . LANG, array('lang')) ?>">
            <span id="settings" data-settings='<?= Bitrix\Main\Web\Json::encode($preparedUserSettings) ?>'></span>
            <input type="hidden" name="settings[show]" value="show">
            <?= bitrix_sessid_post() ?>
            <div class="select-area">
                <label for="tours-select" class="notice">Выберите Тур: </label>
                <select id="tours-select" name="settings[tourid]">
                    <option></option>   

                    <? foreach (\travelsoft\booking\stores\Tours::get() as $tour) { ?>

                        <option value="<?= $tour['ID'] ?>"<?= ($_POST['settings']['tourid'] == $tour['ID'] ? 'selected=""' : '') ?> ><?= $tour['NAME'] ?></option>
                    <? } ?>

                </select>
            </div>
            <div class="select-area align-left sub-part <?= (!$visible ? 'hidden' : '') ?>">
                <label for="pricetypes-select" class="notice">Выберите типы цен: </label>
                <select multiple="" id="pricetypes-select"  name="settings[pricetypes][]">
                    <? foreach (\travelsoft\booking\stores\PriceTypes::get() as $pricetype) { ?>
                        <option data-group="<?= $pricetype['UF_CODE'] ?>" value="<?= $pricetype['ID'] ?>" <?= (in_array($pricetype['ID'], $selected) ? 'selected=""' : '') ?> ><?= $pricetype['UF_NAME'] ?></option>
                    <? } ?>
                </select>
            </div>
            <div class="align-left sub-part <?= (!$visible ? 'hidden' : '') ?>">
                <label class="notice">Установите даты заездов для заведения цен</label>
                <br>
                <div id="datepicker-area"></div>
                <div id="dates-inputs-area"></div>
            </div>
            <div id="btn-area"><button value="Показать" type="submit" name="settings[show]" class="sub-part <?= (!$visible ? 'hidden' : '') ?> adm-btn-save">Показать</button></div>
        </form>
    </td>
</tr>
<tr>
    <td id="parent-table-form-area"><div id="table-hidder"></div><div id="table-form-area"></div></td>
</tr>
<?
$tabControl->End();

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
