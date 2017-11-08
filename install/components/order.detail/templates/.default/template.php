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

<div class="row" id="tourists-info-block">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <h1>Информация по путевке</h1>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_NUMBER') ?></b>: <?= $arResult['ORDER']['ID'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_NAME') ?></b>: <?= $arResult['ORDER']['UF_SERVICE_NAME'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_STATUS') ?></b>: <?= $arResult['ORDER']['STATUS'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_DATE_CREATE') ?></b>: <?= $arResult['ORDER']['UF_DATE'] ? $arResult['ORDER']['UF_DATE']->toString() : '' ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_DATE_FROM') ?></b>: <?= $arResult['ORDER']['UF_DATE_FROM'] ? $arResult['ORDER']['UF_DATE_FROM']->toString() : '' ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_DATE_TO') ?></b>: <?= $arResult['ORDER']['UF_DATE_TO'] ? $arResult['ORDER']['UF_DATE_TO']->toString() : '' ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_DURATION') ?></b>: <?= $arResult['ORDER']['UF_DURATION'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_COUNTRY') ?></b>: <?= $arResult['ORDER']['UF_COUNTRY'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_DEP_CITY') ?></b>: <?= $arResult['ORDER']['UF_DEP_CITY'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_ARR_CITY') ?></b>: <?= $arResult['ORDER']['UF_ARR_CITY'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_HOTEL') ?></b>: <?= $arResult['ORDER']['UF_HOTEL'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_FOOD') ?></b>: <?= $arResult['ORDER']['UF_FOOD'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_ADULTS') ?></b>: <?= $arResult['ORDER']['UF_ADULTS'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_CHILDREN') ?></b>: <?= $arResult['ORDER']['UF_CHILDREN'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_CLIENT') ?></b>: <?= $arResult['ORDER']['USER_NAME'] . ' ' . $arResult['ORDER']['USER_LAST_NAME'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_PHONE') ?></b>: <?= $arResult['ORDER']['USER_PHONE'] ?>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_COST') ?></b>: <?= $arResult['ORDER']['COST_FORMATTED'] ?>
    </div>
    
    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_CURRENCY') ?></b>: <?= $arResult['ORDER']['UF_CURRENCY'] ?>
    </div>
    
    <div class="col-lg-6 col-md-6 col-sm-6">
        <b><?= GetMessage('DETAIL_ORDER_COMMENT') ?></b>: <?= $arResult['ORDER']['UF_COMMENT'] ?>
    </div>
</div>