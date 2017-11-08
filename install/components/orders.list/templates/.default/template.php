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
if (empty($arResult['ORDERS_LIST'])) {

    return;
}
?>

<table cellspacing="2" class="orders">

    <thead>
        <tr class="two">
            <th><?= GetMessage('ORDERS_LIST_NUMBER') ?></th>
            <th><?= GetMessage('ORDERS_LIST_COUNTRY') ?></th>
            <th><?= GetMessage('ORDERS_LIST_ARR_CITY') ?></th>
            <th><?= GetMessage('ORDERS_LIST_DATE_FROM') ?></th>
            <th><?= GetMessage('ORDERS_LIST_DURATION') ?></th>
            <th><?= GetMessage('ORDERS_LIST_FOOD') ?></th>
            <th><?= GetMessage('ORDERS_LIST_ADULTS') ?></th>
            <th><?= GetMessage('ORDERS_LIST_CHILDREN') ?></th>
            <th><?= GetMessage('ORDERS_LIST_NAME') ?></th>
            <th><?= GetMessage('ORDERS_LIST_CLIENT') ?></th>
            <th><?= GetMessage('ORDERS_LIST_COST') ?></th>
            <th><?= GetMessage('ORDERS_LIST_CURRENCY') ?></th>
            <th><?= GetMessage('ORDERS_LIST_STATUS') ?></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($arResult['ORDERS_LIST'] as $ID => $arOrder) { ?>

            <tr class="<? if ($j % 2 == 0): ?>two<? else: ?>one<? endif ?>">
                <td data-label="<?= GetMessage('ORDERS_LIST_NUMBER') ?>"><?= $ID ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_COUNTRY') ?>"><?= $arOrder['UF_COUNTRY'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_ARR_CITY') ?>"><?= $arOrder['UF_ARR_CITY'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_DATE_FROM') ?>"><?= $arOrder['UF_DATE_FROM'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_DURATION') ?>"><?= $arOrder['UF_DURATION'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_FOOD') ?>"><?= $arOrder['UF_FOOD'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_ADULTS') ?>"><?= $arOrder['UF_ADULTS'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_CHILDREN') ?>"><?= $arOrder['UF_CHILDREN'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_NAME') ?>"><?= $arOrder['UF_SERVICE_NAME'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_CLIENT') ?>"><?= $arOrder['USER_NAME'] . ' ' . $arOrder['USER_LAST_NAME'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_COST') ?>"><?= $arOrder['COST_FORMATTED'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_CURRENCY') ?>"><?= $arOrder['UF_CURRENCY'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_STATUS') ?>"><?= $arOrder['STATUS_NAME'] ?></td>
                <td><a href="<?= str_replace("#ORDER_ID#", $ID, $arParams['DETAIL_PAGE']) ?>" rel="nofollow" class="detail-btn"><?= GetMessage('ORDERS_LIST_DETAIL') ?></a></td>
            </tr>

            <?
        }
        ?>
    </tbody>
</table>
<?
$APPLICATION->IncludeComponent(
        "bitrix:main.pagenavigation", $arParams["PAGE_TEMPLATE"], array(
    "NAV_OBJECT" => $arResult["NAV"],
    "SEF_MODE" => "N",
        ), false
);
?>