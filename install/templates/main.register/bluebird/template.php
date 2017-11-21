<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponentTemplate $this
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

CJSCore::Init();
?>
<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
    <div class="bx-auth-reg">

        <? if ($USER->IsAuthorized()): ?>

            <p><? echo GetMessage("MAIN_REGISTER_AUTH") ?></p>

        <? else: ?>
            <?
            if (count($arResult["ERRORS"]) > 0):
                foreach ($arResult["ERRORS"] as $key => $error)
                    if (intval($key) == 0 && $key !== 0)
                        $arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", "&quot;" . GetMessage("REGISTER_FIELD_" . $key) . "&quot;", $error);

                ShowError(implode("<br />", $arResult["ERRORS"]));

            elseif ($arResult["USE_EMAIL_CONFIRMATION"] === "Y"):
                ?>
                <p><? echo GetMessage("REGISTER_EMAIL_WILL_BE_SENT") ?></p>
            <? endif ?>

            <form method="post" action="<?= $APPLICATION->GetCurPageParam("", array("isAgent"), false) ?>" name="regform" id="regform" enctype="multipart/form-data">
                <input type="hidden" name="REGISTER[LOGIN]" value="temp_login">
                <?
                if ($arResult["BACKURL"] <> ''):
                    ?>
                    <input type="hidden" name="backurl" value="<?= $arResult["BACKURL"] ?>" />
                    <?
                endif;
                ?>
                <fieldset>
                    <legend><b><?= GetMessage("AUTH_REGISTER") ?></b></legend>
                    <div class="checkbox">
                        <label><input <?
                            if ($_POST['IS_AGENT'] != 'Y') {
                                echo 'checked=""';
                            }
                            ?> id="is-client-btn" type="radio" name="IS_AGENT" value="N"> <b>Я клиент</b></label>
                        <label><input <?
                            if ($_POST['IS_AGENT'] == 'Y' || $_GET['isAgent'] == 'yes') {
                                echo 'checked=""';
                            }
                            ?> id="is-agent-btn" type="radio" name="IS_AGENT" value="Y"> <b>Я агент</b></label>
                    </div>
                    <?
                    // СТАВИМ EMAIL НА ПЕРВОЕ МЕСТО //
                    unset($arResult["SHOW_FIELDS"][array_search("EMAIL", $arResult["SHOW_FIELDS"])]);
                    array_unshift($arResult["SHOW_FIELDS"], "EMAIL");
                    /////////////////////////////////////////////////////////////////

                    foreach ($arResult["SHOW_FIELDS"] as $FIELD):
                        ?>
                        <?
                        
                        if ($FIELD == 'LOGIN') {
                            continue;
                        }
                        ?>
                        <div <? if ($FIELD == "PERSONAL_BIRTHDAY"): ?>data-hide-for-agent="yes" data-show-for-client="yes"<? endif ?> id="birthdate-box" class="form-group posrel <? if ($FIELD == "PERSONAL_BIRTHDAY" && ($_POST['IS_AGENT'] == 'Y' || $_GET['isAgent'] == 'yes')) {
                    echo "hide ";
                } ?>">
                            <label for="REGISTER[<?= $FIELD ?>]"><?= GetMessage("REGISTER_FIELD_" . $FIELD) ?>:<? if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y"): ?><span class="starrequired">*</span><? endif ?></label>
                            <?
                            switch ($FIELD) {

                                case "EMAIL":
                                    ?>

                                    <input <?
                                    if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
                                        echo 'required=""';
                                    }
                                    ?> class="form-control" type="email" name="REGISTER[<?= $FIELD ?>]" value="<?= $arResult["VALUES"][$FIELD] ?>">
                                        <?
                                        break;

                                    case "PERSONAL_PHONE":
                                        ?>

                                    <input <?
                                    if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
                                        echo 'required=""';
                                    }
                                    ?> class="form-control" size="30" type="tel" name="REGISTER[<?= $FIELD ?>]" value="<?= $arResult["VALUES"][$FIELD] ?>">
                                        <?
                                        break;

                                    case "PASSWORD":
                                        ?><input <?
                                    if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
                                        echo 'required=""';
                                    }
                                    ?> class="form-control" size="30" type="password" name="REGISTER[<?= $FIELD ?>]" value="<?= $arResult["VALUES"][$FIELD] ?>" autocomplete="off">
                                        <? /* if ($arResult["SECURE_AUTH"]): ?>
                                          <span class="bx-auth-secure" id="bx_auth_secure" title="<? echo GetMessage("AUTH_SECURE_NOTE") ?>" style="display:none">
                                          <div class="bx-auth-secure-icon"></div>
                                          </span>
                                          <noscript>
                                          <span class="bx-auth-secure" title="<? echo GetMessage("AUTH_NONSECURE_NOTE") ?>">
                                          <div class="bx-auth-secure-icon bx-auth-secure-unlock"></div>
                                          </span>
                                          </noscript>
                                          <script type="text/javascript">
                                          document.getElementById('bx_auth_secure').style.display = 'inline-block';
                                          </script>
                                          <? endif */ ?>
                                        <?
                                    break;
                                case "CONFIRM_PASSWORD":
                                    ?><input <?
                                    if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
                                        echo 'required=""';
                                    }
                                    ?> size="30" type="password" name="REGISTER[<?= $FIELD ?>]" value="<?= $arResult["VALUES"][$FIELD] ?>" autocomplete="off" class="form-control"><?
                                        break;

                                    /* case "PERSONAL_GENDER":
                                      ?><select name="REGISTER[<?= $FIELD ?>]">
                                      <option value=""><?= GetMessage("USER_DONT_KNOW") ?></option>
                                      <option value="M"<?= $arResult["VALUES"][$FIELD] == "M" ? " selected=\"selected\"" : "" ?>><?= GetMessage("USER_MALE") ?></option>
                                      <option value="F"<?= $arResult["VALUES"][$FIELD] == "F" ? " selected=\"selected\"" : "" ?>><?= GetMessage("USER_FEMALE") ?></option>
                                      </select><?
                                      break; */

                                    case "PERSONAL_COUNTRY":
                                    case "WORK_COUNTRY":
                                        ?><select <?
                                    if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
                                        echo 'required=""';
                                    }
                                    ?> class="form-control" name="REGISTER[<?= $FIELD ?>]"><?
                                        foreach ($arResult["COUNTRIES"]["reference_id"] as $key => $value) {
                                            ?><option value="<?= $value ?>"<? if ($value == $arResult["VALUES"][$FIELD]): ?> selected="selected"<? endif ?>><?= $arResult["COUNTRIES"]["reference"][$key] ?></option>
                                            <?
                                    }
                                    ?></select><?
                                    break;

                                case "PERSONAL_PHOTO":
                                case "WORK_LOGO":
                                    ?><input <?
                                        if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
                                            echo 'required=""';
                                        }
                                        ?> class="form-control" size="30" type="file" name="REGISTER_FILES_<?= $FIELD ?>"><?
                                        break;

                                    case "PERSONAL_NOTES":
                                    case "WORK_NOTES":
                                        ?><textarea <?
                                    if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
                                        echo 'required=""';
                                    }
                                    ?> class="form-control" cols="30" rows="5" name="REGISTER[<?= $FIELD ?>]"><?= $arResult["VALUES"][$FIELD] ?></textarea><?
                                        break;
                                    default:
                                        if ($FIELD == "PERSONAL_BIRTHDAY"):
                                            ?><small><?= $arResult["DATE_FORMAT"] ?></small><br /><? endif;
                                        ?><input <?
                                    if ($arResult["REQUIRED_FIELDS_FLAGS"][$FIELD] == "Y") {
                                        echo 'required=""';
                                    }
                                    ?> class="form-control" size="30" type="text" name="REGISTER[<?= $FIELD ?>]" value="<?= $arResult["VALUES"][$FIELD] ?>"><?
                                        if ($FIELD == "PERSONAL_BIRTHDAY")
                                            $APPLICATION->IncludeComponent(
                                                    'bitrix:main.calendar', '', array(
                                                'SHOW_INPUT' => 'N',
                                                'FORM_NAME' => 'regform',
                                                'INPUT_NAME' => 'REGISTER[PERSONAL_BIRTHDAY]',
                                                'SHOW_TIME' => 'N'
                                                    ), null, array("HIDE_ICONS" => "Y")
                                            );
                                        ?><? }
                ?>
                        <? // endif  ?>
                        </div>
                    <? endforeach ?>
                    <? // ********************* User properties ***************************************************?>
                    <?
                    if ($arResult["USER_PROPERTIES"]["SHOW"] == "Y"):
                        $arAgentFields = array(
                            0 => "UF_BIK",
                            1 => "UF_ACCOUNT_CURRENCY",
                            2 => "UF_ACTUAL_ADDRESS",
                            3 => "UF_OKPO",
                            4 => "UF_UNP",
                            5 => "UF_CHECKING_ACCOUNT",
                            6 => "UF_BANK_CODE",
                            7 => "UF_BANK_ADDRESS",
                            8 => "UF_BANK_NAME",
                            9 => "UF_LEGAL_ADDRESS",
                            10 => "UF_LEGAL_NAME",
                        );
                        $agentFieldsHtml = $simpleClientFieldsHtml = $html = '';
                        ?>
                        <?
                        foreach ($arResult["USER_PROPERTIES"]["DATA"] as $FIELD_NAME => $arUserField):

                            $html = '<div class="form-group posrel">
                                <label>' . $arUserField["EDIT_FORM_LABEL"] . ':';
                            if ($arUserField["MANDATORY"] == "Y") {
                                $html .= '<span class="starrequired">*</span>';
                            }

                            $html .= '</label>';

                            ob_start();
                            $APPLICATION->IncludeComponent(
                                    "bitrix:system.field.edit", $arUserField["USER_TYPE"]["USER_TYPE_ID"], array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField, "form_name" => "regform"), null, array("HIDE_ICONS" => "Y"));

                            $html .= ob_get_contents();
                            ob_end_clean();
                            $html .= '</div>';

                            if (in_array($FIELD_NAME, $arAgentFields)) {

                                $agentFieldsHtml .= $html;
                            } else {

                                $simpleClientFieldsHtml .= $html;
                            }
                        endforeach;
                        ?>
                        <? if (strlen($agentFieldsHtml) > 0): ?>

                                <? //= strlen(trim($arParams["USER_PROPERTY_NAME"])) > 0 ? $arParams["USER_PROPERTY_NAME"] : GetMessage("USER_TYPE_EDIT_TAB")  ?>
                            <div id="agent-fields-area" <? if ($_POST['IS_AGENT'] == 'Y' || $_GET['isAgent'] == 'yes'): ?>class="show"<? else: ?>class="hide"<? endif ?>>
                <?= $agentFieldsHtml; ?>
                            </div>
                            <div id="client-fields-area" <? if ($_POST['IS_AGENT'] != 'Y' && $_GET['isAgent'] != 'yes'): ?>class="show"<? else: ?>class="hide"<? endif ?>>
                                <div id="passport-data-block-title" class="register-btn"><b> Паспортные данные <i class="fa fa-caret-up" aria-hidden="true"></i> </b></div>
                                <div id="passport-data-block" class="hide">
                            <?= $simpleClientFieldsHtml; ?>
                                </div>
                            </div>
                        <? endif ?>
                    <? endif; ?>
                    <? // ******************** /User properties ***************************************************   ?>
                    <?
                    /* CAPTCHA */
                    if ($arResult["USE_CAPTCHA"] == "Y") {
                        ?>
                        <div class="form-group">
                            <label><b><?= GetMessage("REGISTER_CAPTCHA_TITLE") ?></b></label><br>
                            <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>" />
                            <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>" width="180" height="40" alt="CAPTCHA" /><br>
                            <label><?= GetMessage("REGISTER_CAPTCHA_PROMT") ?>:<span class="starrequired">*</span></label>
                            <input type="text" name="captcha_word" maxlength="50" value="" class="form-control" />
                        </div>
                        <?
                    }
                    /* !CAPTCHA */
                    ?>
                    <div class="form-group">
                        <input class="register-btn" type="submit" name="register_submit_button" value="<?= GetMessage("AUTH_REGISTER") ?>" />
                    </div>
                    <p><? echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"]; ?></p>
                    <p><span class="starrequired">*</span><?= GetMessage("AUTH_REQ") ?></p>
                    <p><a href="/auth/index.php?login=yes"><b><?= GetMessage("AUTH_AUTH") ?></b></a></p>
                </fieldset>
            </form>
    <? endif ?>
    </div>
    </div>
</div>
<script>
    BX.ready(function () {

        var areaTypes = ['client', 'agent'];

        function initToggle(currentType) {

            var attrs = {};

            BX.bind(BX('is-' + currentType + '-btn'), 'click', function (e) {

                for (var j = 0; j < areaTypes.length; j++) {
                    BX.removeClass(BX(areaTypes[j] + '-fields-area'), 'show');
                    BX.addClass(BX(areaTypes[j] + '-fields-area'), 'hide');
                }

                BX.addClass(BX(currentType + '-fields-area'), 'show');
                BX.removeClass(BX(currentType + '-fields-area'), 'hide');

                attrs = {};
                attrs['data-hide-for-' + currentType] = 'yes';
                BX.findChild(BX('regform'), {attribute: attrs}, true, true).forEach(function (el) {
                    BX.addClass(el, 'hide');
                    BX.removeClass(el, 'show');
                });

                attrs = {};
                attrs['data-show-for-' + currentType] = 'yes';
                BX.findChild(BX('regform'), {attribute: attrs}, true, true).forEach(function (el) {
                    BX.removeClass(el, 'hide');
                    BX.addClass(el, 'show');
                });
            });

        }

        for (var i = 0; i < areaTypes.length; i++) {
            initToggle(areaTypes[i]);
        }

        /**
         * Показать/скрыть блок с паспортными данными
         */
        BX.bind(BX('passport-data-block-title'), 'click', function (e) {

            var passportDataBlock = BX('passport-data-block');
            var caret = null;

            if (BX.hasClass(passportDataBlock, "hide")) {

                BX.removeClass(passportDataBlock, 'hide');
                BX.addClass(passportDataBlock, 'show');

                caret = BX.findChild(this, {tag: 'i', className: "fa-caret-up"}, true);

                BX.addClass(caret, 'fa-caret-down');
                BX.removeClass(caret, 'fa-caret-up');

            } else {

                BX.addClass(passportDataBlock, 'hide');
                BX.removeClass(passportDataBlock, 'show');

                caret = BX.findChild(this, {tag: 'i', className: "fa-caret-down"}, true);

                BX.addClass(caret, 'fa-caret-up');
                BX.removeClass(caret, 'fa-caret-down');

            }

        });

    });
</script>