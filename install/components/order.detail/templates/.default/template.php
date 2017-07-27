<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
if (empty($arResult['ORDER'])) {

    return;
}
?>

    <table class="order-info">
        <tbody>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_NUMBER') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_NUMBER') ?>"><?= $arResult['ORDER']['ID']?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_NAME') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_NAME') ?>"><?= $arResult['ORDER']['UF_SERVICE_NAME'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_STATUS') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_STATUS') ?>"><?= $arResult['ORDER']['STATUS'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_DATE_CREATE') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_DATE_CREATE') ?>"><?= $arResult['ORDER']['UF_DATE']->toString() ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_DATE_FROM') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_DATE_FROM') ?>"><?= $arResult['ORDER']['UF_DATE_FROM']->toString() ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_DATE_TO') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_DATE_TO') ?>"><?= $arResult['ORDER']['UF_DATE_TO']->toString() ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_DURATION') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_DURATION') ?>"><?= $arResult['ORDER']['UF_DURATION'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_COUNTRY') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_COUNTRY') ?>"><?=  $arResult['ORDER']['UF_COUNTRY']?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_DEP_CITY') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_DEP_CITY') ?>"><?= $arResult['ORDER']['UF_DEP_CITY'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_ARR_CITY') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_ARR_CITY') ?>"><?= $arResult['ORDER']['UF_ARR_CITY'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_HOTEL') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_HOTEL') ?>"><?= $arResult['ORDER']['UF_HOTEL'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_FOOD') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_FOOD') ?>"><?= $arResult['ORDER']['UF_FOOD'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_ADULTS') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_ADULTS') ?>"><?= $arResult['ORDER']['UF_ADULTS'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_CHILDREN') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_CHILDREN') ?>"><?= $arResult['ORDER']['UF_CHILDREN'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_CLIENT') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_CLIENT') ?>"><?= $arResult['ORDER']['UF_CNAME'] . ' ' . $arResult['ORDER']['UF_CLAST_NAME']?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_PHONE') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_PHONE') ?>"><?= $arResult['ORDER']['UF_CPHONE'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_COST') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_COST') ?>"><?= $arResult['ORDER']['COST_FORMATTED'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_CURRENCY') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_CURRENCY') ?>"><?= $arResult['ORDER']['UF_CURRENCY'] ?></td>
            </tr>
            <tr>
                <td class="order-field-title"><b><?= GetMessage('DETAIL_ORDER_COMMENT') ?></b>:</td>
                <td data-label="<?= GetMessage('DETAIL_ORDER_COMMENT') ?>"><?= $arResult['ORDER']['UF_COMMENT'] ?></td>
            </tr>
        </tbody>
    </table>
