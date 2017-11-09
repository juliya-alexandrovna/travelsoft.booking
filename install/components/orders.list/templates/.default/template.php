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

 /**
* Возвращает массив array("REFERENCE" => array(), "REFERENCE_ID" => array())для SelectBoxFromArray, SelectBoxMFromArray
* @param array $arElements
* @param string $referenceField
* @param string $referenceIdField
* @return array
*/
function getReferencesSelectData(array $arElements, string $referenceField, string $referenceIdField): array {

   $arData = array(
       "REFERENCE" => array(),
       "REFERENCE_ID" => array()
   );

   $arData['REFERENCE'][] = '...';
   $arData['REFERENCE_ID'][] = '';

   foreach ($arElements as $arElement) {

       $arData['REFERENCE'][] = $arElement[$referenceField];
       $arData['REFERENCE_ID'][] = $arElement[$referenceIdField];
   }

   return $arData;
}

$formName = 'order-filter';
?>
<form name="<?= $formName ?>" id="<?= $formName ?>" action="<?= $APPLICATION->GetCurPage(false) ?>" method="get">
    <fieldset>
        <legend><b><?= GetMessage('FILTER_TITLE')?></b></legend>
    <?= bitrix_sessid_post()?>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <div class="form-group posrel">
                <label for="UF_DATE_FROM"><?= GetMessage('FILTER_DATE_FROM')?></label>
                <input class="form-control" name="UF_DATE_FROM" value="<?= htmlspecialchars($_REQUEST['UF_DATE_FROM'])?>">
                <?
                $APPLICATION->IncludeComponent(
                        "bitrix:main.calendar", "", array(
                    "SHOW_INPUT" => "N",
                    "FORM_NAME" => $formName,
                    "INPUT_NAME" => 'UF_DATE_FROM',
                    "SHOW_TIME" => 'N',
                        ), $component, array("HIDE_ICONS" => "Y"));
                ?>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <div class="form-group posrel">
                <label for="UF_DATE"><?= GetMessage('FILTER_DATE')?></label>
                <input class="form-control" name="UF_DATE" value="<?= htmlspecialchars($_REQUEST['UF_DATE'])?>">
                <?
                $APPLICATION->IncludeComponent(
                        "bitrix:main.calendar", "", array(
                    "SHOW_INPUT" => "N",
                    "FORM_NAME" => $formName,
                    "INPUT_NAME" => 'UF_DATE',
                    "SHOW_TIME" => 'N',
                        ), $component, array("HIDE_ICONS" => "Y"));
                ?>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <div class="form-group posrel">
                <label for="UF_SERVICE_NAME"><?= GetMessage('FILTER_SERVICE_NAME')?></label>
                <?= \SelectBoxFromArray("UF_SERVICE_NAME", getReferencesSelectData($arResult['VARS']['SERVICES_NAMES'], 'UF_SERVICE_NAME', 'UF_SERVICE_NAME'), $_GET['UF_SERVICE_NAME'], "", 'class="select-2 form-control"', false, $formName)?>
            </div>
        </div> 
    </div>
    <div class="row">
         <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <div class="form-group posrel">
                <label for="UF_STATUS_ID"><?= GetMessage('FILTER_STATUS')?></label>
                <?= \SelectBoxFromArray("UF_STATUS_ID", getReferencesSelectData($arResult['VARS']['STATUSES'], 'UF_NAME', 'ID'), $_GET['UF_STATUS_ID'], "", 'class="select-2 form-control"', false, $formName)?>
            </div>
        </div>
        <?if ($arResult['GET_FILTER_BY_CLIENT']):?>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
            <div class="form-group posrel">
                <label for="UF_USER_ID"><?= GetMessage('FILTER_CLIENT')?></label>
                <?= \SelectBoxFromArray("UF_USER_ID", getReferencesSelectData($arResult['VARS']['CLIENTS'], 'FULL_NAME_WITH_EMAIL', 'ID'), $_GET['UF_USER_ID'], "", 'class="select-2 form-control"', false, $formName)?>
            </div>
        </div>
        <?endif?>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="text-right">
                <button type="submit" name="SHOW_FROM_FILTER" value="SHOW_FROM_FILTER" class="filter-btn"><?= GetMessage('FILTER_SHOW_FROM_FILTER')?></button>
                <button type="submit" name="CANCEL" value="CANCEL" class="filter-btn"><?= GetMessage('FILTER_CANCEL')?></button>
            </div>
        </div>
    </div>
    </fieldset>
</form>

<?if (empty($arResult['ORDERS_LIST'])) {

    return;
}?>

<table cellspacing="2" class="orders">

    <thead>
        <tr class="two">
            <th><?= GetMessage('ORDERS_LIST_NUMBER') ?></th>
            <th><?= GetMessage('ORDERS_LIST_DATE_FROM') ?></th>
            <th><?= GetMessage('ORDERS_LIST_NAME') ?></th>
            <th><?= GetMessage('ORDERS_LIST_CLIENT') ?></th>
            <th><?= GetMessage('ORDERS_LIST_ADULTS') ?></th>
            <th><?= GetMessage('ORDERS_LIST_CHILDREN') ?></th>
            <th><?= GetMessage('ORDERS_LIST_DURATION') ?></th>
            <th><?= GetMessage('ORDERS_LIST_FOOD') ?></th>
            <th><?= GetMessage('ORDERS_LIST_COST') ?></th>
            <th><?= GetMessage('ORDERS_LIST_CURRENCY') ?></th>
            <th><?= GetMessage('ORDERS_LIST_DATE') ?></th>
            <th><?= GetMessage('ORDERS_LIST_STATUS') ?></th>
<!--            <th><?= GetMessage('ORDERS_LIST_COUNTRY') ?></th>
            <th><?= GetMessage('ORDERS_LIST_ARR_CITY') ?></th>-->
            <th></th>
        </tr>
    </thead>
    <tbody>
<? foreach ($arResult['ORDERS_LIST'] as $ID => $arOrder) { ?>

            <tr class="<? if ($j % 2 == 0): ?>two<? else: ?>one<? endif ?>">
                <td data-label="<?= GetMessage('ORDERS_LIST_NUMBER') ?>"><?= $ID ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_DATE_FROM') ?>"><?= $arOrder['UF_DATE_FROM'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_NAME') ?>"><?= $arOrder['UF_SERVICE_NAME'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_CLIENT') ?>"><?= $arOrder['USER_NAME'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_ADULTS') ?>"><?= $arOrder['UF_ADULTS'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_CHILDREN') ?>"><?= $arOrder['UF_CHILDREN'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_DURATION') ?>"><?= $arOrder['UF_DURATION'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_FOOD') ?>"><?= $arOrder['UF_FOOD'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_COST') ?>"><?= $arOrder['FORMATTED_CURRENT_TOTAL_COST'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_CURRENCY') ?>"><?= $arOrder['CURRENT_COST_CURRENCY'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_DATE') ?>"><?= $arOrder['UF_DATE'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_STATUS') ?>"><?= $arOrder['STATUS_NAME'] ?></td>
    <!--                <td data-label="<?= GetMessage('ORDERS_LIST_COUNTRY') ?>"><?= $arOrder['UF_COUNTRY'] ?></td>
                <td data-label="<?= GetMessage('ORDERS_LIST_ARR_CITY') ?>"><?= $arOrder['UF_ARR_CITY'] ?></td>-->
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