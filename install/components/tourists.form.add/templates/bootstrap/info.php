<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/**
 * вывод инфрмации по туристам
 * @param array $arResult
 * @param int $col колонки в html сетке
 */
?>
<?
$first = true;
foreach ($arResult['FORM_DATA'] as $key => $arFormData):
    ?>
    <div class="row">
        <div class="col-md-<?= $col ?> col-lg-<?= $col ?> col-sm-<?= $col ?>">
            <?
            foreach ($arFormData as $fieldName => $arFieldData):
                if (
                        $arFieldData['USER_TYPE']['USER_TYPE_ID'] == 'boolean' ||
                        $arFieldData['USER_TYPE']['USER_TYPE_ID'] == 'file' ||
                        $fieldName === 'ID'
                ) {
                    continue;
                }
                ?>
        <? if ($first): ?>
                    <div class="booking_step row"><label class="booking_step_activ"><i class="fa"><?= $key + 1 ?></i> <?= GetMessage('TOURIST_TITLE') ?></label></div>

                    <?
                    $first = false;
                endif;
                ?>
                <div>
                    <span class="field-name"><b><?= $arFieldData['EDIT_FORM_LABEL'] ?></b></span>: <span class="field-value"><?= $arFieldData['VALUE'] ?></span>
                </div>
    <? endforeach ?>
        </div>
    </div>
<? endforeach ?>
