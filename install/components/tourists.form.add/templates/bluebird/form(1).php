<?
/**
 * редактирование/добавление информации по туристам
 * @param array $arResult
 * @param int $col колонки в html сетке
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

use travelsoft\booking\Utils;

/* if (count($arResult['FORM_DATA']) < 2) {
  $col = 12;
  } */

$formName = 'tourists-form';

$oldFileTpl = '<input type="hidden" name="TOURISTS[#KEY#][#FIELD_NAME#_del][#KEY#]" value="Y">';
$oldFileTpl .= '<input type="hidden" name="TOURISTS[#KEY#][#FIELD_NAME#_old_id][#KEY#]" value="#OLD_FILE_ID#">';
$oldFileTpl .= '<div><span>' . GetMessage('TOURIST_ADDED_FILE') . ' </span><span class="original-file-name">#ORIGINAL_FILE_NAME#</span></div>';

CJSCore::Init();
?>
<form enctype="multipart/form-data" action="<?= $APPLICATION->GetCurPageParam() ?>" method="POST" id="<?= $formName ?>" name="<?= $formName ?>">

<?= bitrix_sessid_post() ?>

    <?
    if ($_SESSION['__TRAVELSOFT']['SUCCESS_TOURISTS_EDIT_FORM']) {

        ShowMessage(array('TYPE' => 'OK', 'MESSAGE' => GetMessage('SUCCESS_TOURISTS_EDIT_FORM')));
        unset($_SESSION['__TRAVELSOFT']['SUCCESS_TOURISTS_EDIT_FORM']);
    }
    ?>

    <div class="row">

<? foreach ($arResult['FORM_DATA'] as $key => $arFormData) : ?>

            <? if ($key !== 0 && ($key + 1) % 2 === 0): ?> <div class="row"> <? endif ?>

                <div class="col-md-<?= $col ?> col-lg-<?= $col ?> col-sm-<?= $col ?>">

    <?
    $first = true;
    foreach ($arFormData as $fieldName => $arFieldData):
        ?>

                        <? if ($first): ?>
                            <div class="booking_step row"><label class="booking_step_activ"><i class="fa"><?= $key + 1 ?></i> <?= GetMessage('TOURIST_TITLE') ?></label></div>
                                
            <?
            Utils::showError((array) $arResult['ERRORS'][$key], 'TOURIST_SAVE_ERROR', true);
            $first = false;
        endif;
        
        ?>
                        <? if ($arFieldData['USER_TYPE']['USER_TYPE_ID'] == 'file'): ?>

                            <div class="form-group posrel">

                                <label for="<?= $fieldName ?>">
									<?= $arFieldData['EDIT_FORM_LABEL'] ?><? if ($arFieldData['required']): ?><span class="starrequired">*</span><? endif ?> | <a href="/upload/files/oprosnik.doc">Скачать анкету для заполения</a> <? Utils::showError((array) $arResult['ERRORS'][$key], 'WRONG_' . $fieldName) ?>
                                </label>
                                <div id="old-file-area-<?= $key ?>">
            <?
            if ($arFieldData['VALUE'] > 0) {
                echo str_replace(array(
                    "#KEY#",
                    "#FIELD_NAME#",
                    "#OLD_FILE_ID#",
                    "#ORIGINAL_FILE_NAME#"
                        ), array(
                    $key,
                    $fieldName,
                    $arFieldData['VALUE'],
                    htmlspecialcharsbx($arFieldData['DETAILS']['ORIGINAL_NAME'])
                        ), $oldFileTpl);
            }
            ?>
                                </div>
                                <input type="file" name="TOURISTS[<?= $key ?>][<?= $fieldName ?>]">
                            </div>

        <? elseif ($arFieldData['USER_TYPE']['USER_TYPE_ID'] == 'boolean'): ?>

                            <div class="checkbox">

                                <label for="<?= $fieldName ?>">

                                    <input type="hidden" name="TOURISTS[<?= $key ?>][<?= $fieldName ?>]" value="0">
                                    <input <? if ($arFieldData['VALUE'] == 1) : ?>checked=""<? endif ?> type="checkbox" name="TOURISTS[<?= $key ?>][<?= $fieldName ?>]" value="1"> <b><?= $arFieldData['EDIT_FORM_LABEL'] ?></b><? if ($arFieldData['required']): ?><span class="starrequired">*</span><? endif ?> <? Utils::showError((array) $arResult['ERRORS'][$key], 'WRONG_' . $fieldName) ?>

                                </label>
                            </div>

        <? else: ?>

                            <div class="form-group posrel">

                                <label for="<?= $fieldName ?>">
            <?= $arFieldData['EDIT_FORM_LABEL'] ?><? if ($arFieldData['required']): ?><span class="starrequired">*</span><? endif ?> <? Utils::showError((array) $arResult['ERRORS'][$key], 'WRONG_' . $fieldName) ?>
                                </label>
                                    <? if ($fieldName == 'ID' && !empty($arResult['TOURISTS'])) : ?>
                                    <select data-cleanerlink='cleaner-<?= $key ?>' id='tourist-<?= $key ?>' data-key="<?= $key ?>" name="TOURISTS[<?= $key ?>][<?= $fieldName ?>]" class="form-control tourists-select">
                                        <option value=""><?= GetMessage('TOURIST_FORM_SELECT_TITLE') ?></option>
                <? foreach ($arResult['TOURISTS'] as $ID => $arTourist): ?>
                                            <option <? if ($arFieldData['VALUE'] == $ID): ?>selected=""<? endif ?> value="<?= $ID ?>"><?= Utils::gluingAnArray(array($arTourist['UF_NAME'], $arTourist['UF_SECOND_NAME'], $arTourist['UF_LAST_NAME'])); ?>
                                        <? endforeach ?>
                                    </select>
                                    <span id="cleaner-<?= $key ?>" data-selectlink='tourist-<?= $key ?>' <? if ($arFieldData['VALUE'] <= 0): ?>style="display: none"<? endif ?> class="cleaners">&times;</span>
                                    <div class="add-tourist-title"><b><?= GetMessage('TOURIST_ADD_BLOCK_TITLE') ?></b></div>
            <? else: ?>
                                    <input class="form-control" type="text" name="TOURISTS[<?= $key ?>][<?= $fieldName ?>]" value="<?= $arFieldData['VALUE'] ?>">
                                    <?
                                    if ($arFieldData['USER_TYPE']['USER_TYPE_ID'] == 'date') {
                                        $APPLICATION->IncludeComponent(
                                                "bitrix:main.calendar", "", array(
                                            "SHOW_INPUT" => "N",
                                            "FORM_NAME" => $formName,
                                            "INPUT_NAME" => 'TOURISTS[' . $key . '][' . $fieldName . ']',
                                            "SHOW_TIME" => 'N',
                                                ), $component, array("HIDE_ICONS" => "Y"));
                                    }
                                    ?>
                                <? endif ?>
                            </div>

        <? endif ?>
                    <? endforeach ?>

                </div>
    <? if (($key + 2) % 2 != 0): ?> </div> <? endif ?>
            <? endforeach ?>

    </div>

    <div><button id="add-tourists-btn" class="btn form-control" name="SAVE" value="SAVE" type="submit"><?= GetMessage('TOURIST_FORM_BTN_TITLE') ?></button></div>

</form> 

<script>
    BX.ready(function () {

        'use strict';

<? if ($_SESSION['__TRAVELSOFT']['SUCCESS_TOURISTS_EDIT_FORM']) { ?>

            BX.scrollToNode(BX('<?= $formName ?>'));

<? } else { ?>

            var node = BX.findChild(BX('<?= $formName ?>'), {className: 'error'}, true);

            if (node) {

                BX.scrollToNode(node);
            }

<? } ?>

<? if (!empty($arResult['TOURISTS'])) { ?>

            /**
             * Clear inputs of torist
             * @param {String} key
             * @returns {undefined}
             */
            function clearTouristInputs(key) {

                var els;

                if (key) {
                    els = BX.findChildren(BX('<?= $formName ?>'), {tag: 'input', attribute: {name: new RegExp('TOURISTS\\[' + key + '\\]')}}, true, true);

                    if (BX.type.isArray(els)) {

                        for (var i = 0; i < els.length; i++) {

                            if (els[i].classList.contains('tourists-select')) {
                                continue;
                            }

                            els[i].value = '';
                        }
                    }

                    BX('old-file-area-' + key).innerHTML = '';
                }
            }

            /**
             * Инициализация cleaners туристов
             * @returns {undefined}            
             */
            function initCleaners() {

                var cleaners = BX.findChildren(BX('<?= $formName ?>'), {className: 'cleaners'}, true, true),
                        event = new Event('change'),
                        select;

                if (BX.type.isArray(cleaners)) {

                    for (var i = 0; i < cleaners.length; i++) {


                        if (cleaners[i].dataset.selectlink) {
                            cleaners[i].onclick = function () {
                                select = BX(this.dataset.selectlink);
                                select.value = '';
                                select.dispatchEvent(event);
                            };
                        }
                    }
                }

            }

            /**
             * Инициализация selects туристов
             * @returns {undefined}
             */
            function initSelects() {

                var touristsData = JSON.parse('<?= \Bitrix\Main\Web\Json::encode($arResult['TOURISTS']) ?>');

                if (typeof touristsData === 'object') {

                    var tourists = BX.findChildren(BX('<?= $formName ?>'), {className: 'tourists-select'}, true, true);

                    var oldfilecontent = '';

                    if (BX.type.isArray(tourists)) {

                        for (var i = 0; i < tourists.length; i++) {

                            tourists[i].onchange = function () {

                                var key = this.dataset.key,
                                        value = this.value,
                                        el;

                                if (key) {

                                    if (typeof touristsData[value] === 'object') {

                                        BX.show(BX(this.dataset.cleanerlink));

                                        for (var property in touristsData[value]) {

                                            if (touristsData[value].hasOwnProperty(property)) {

                                                if (property === 'ID') {

                                                    continue;
                                                }

                                                if (property === 'UF_FILE') {

                                                    if (touristsData[value][property] > 0) {
                                                        oldfilecontent = '<?= $oldFileTpl ?>';

                                                        while (/\#KEY\#/.test(oldfilecontent)) {
                                                            oldfilecontent = oldfilecontent.replace("\#KEY\#", key);
                                                        }

                                                        while (/\#OLD_FILE_ID\#/.test(oldfilecontent)) {
                                                            oldfilecontent = oldfilecontent.replace("\#OLD_FILE_ID\#", touristsData[value][property]);
                                                        }

                                                        while (/\#FIELD_NAME\#/.test(oldfilecontent)) {
                                                            oldfilecontent = oldfilecontent.replace("\#FIELD_NAME\#", property);
                                                        }

                                                        while (/\#ORIGINAL_FILE_NAME\#/.test(oldfilecontent)) {
                                                            oldfilecontent = oldfilecontent.replace("\#ORIGINAL_FILE_NAME\#", touristsData[value].DETAILS_BY_FILE.ORIGINAL_NAME);
                                                        }

                                                        BX("old-file-area-" + key).innerHTML = oldfilecontent;
                                                    }
                                                    continue;
                                                }

                                                el = BX.findChild(
                                                        BX('<?= $formName ?>'), {tag: 'input', attribute: {name: 'TOURISTS[' + key + '][' + property + ']'}}, true);

                                                if (el) {

                                                    el.value = '';

                                                    if (touristsData[value][property]) {

                                                        el.value = touristsData[value][property];
                                                    }
                                                }

                                            }
                                        }

                                    } else {

                                        clearTouristInputs(key);
                                        BX.hide(BX(this.dataset.cleanerlink));
                                    }
                                }
                            };
                        }
                    }
                }
            }

            initCleaners();
            initSelects();

<? } ?>
    });
</script>