<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * @var array $arResult
 * @var CatalogSectionComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

$this->setFrameMode(true);

if (empty($arResult['COST_PREPARED'])) {

    return;
}
?>

<table cellspacing="2" class="offers">

    <thead>
        <tr>
            <th><?= GetMessage('TOURS_OFFERS_DATE_TITLE')?></th>
            <th><?= GetMessage('TOURS_OFFERS_PRICE_ADULT_TITLE')?></th>
            <th><?= GetMessage('TOURS_OFFERS_PRICE_CHILDREN_TITLE')?></th>
            <th><?= GetMessage('TOURS_OFFERS_PRICE_TOURSERVICE_TITLE')?></th>
            <th><?= GetMessage('TOURS_OFFERS_PLACES_TITLE')?></th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($arResult['COST_PREPARED'] as $ID => $arValues) {

            foreach ($arValues['dates'] as $arrValues) {
                ?>

                <tr>

                    <td data-label="<?= GetMessage('TOURS_OFFERS_DATE_TITLE')?>"><?= $arrValues['date_from'] . ' - ' . $arrValues['date_to'] ?></td>
                    <td data-label="<?= GetMessage('TOURS_OFFERS_PRICE_ADULT_TITLE')?>">
                        <?
                        $aps = implode('<br>', array_map(function ($item) {

                                    return '<span class="offer-price"><i class="cost">' . $item['price'] . '</i> <i class="currency">' . $item['currency'] . "</i></span>";
                                }, $arrValues['prices']['adult']));

                        echo $aps;
                        ?>
                    </td>
                    <td data-label="<?= GetMessage('TOURS_OFFERS_PRICE_CHILDREN_TITLE')?>">
                        <?
                        if (!empty($arrValues['prices']['children'])) {

                            echo implode('<br>', array_map(function ($item) {

                                        return '<span class="offer-price"><i class="cost">' . $item['price'] . '</i> <i class="currency">' . $item['currency'] . "</i></span>";
                                    }, $arrValues['prices']['children']));
                        } else {

                            echo $aps;
                        }
                        ?>
                    </td>
                    <td data-label="<?= GetMessage('TOURS_OFFERS_PRICE_TOURSERVICE_TITLE')?>">
                        <?
                        if (!empty($arrValues['prices']['adult_tour_service'])) {

                            echo implode('<br>', array_map(function ($item) {

                                        return '<span class="offer-price"><i class="cost">' . $item['price'] . '</i> <i class="currency">' . $item['currency'] . "</i></span>";
                                    }, $arrValues['prices']['adult_tour_service']));
                        } elseif (!empty($arrValues['prices']['children_tour_service'])) {

                            echo implode('<br>', array_map(function ($item) {

                                        return '<span class="offer-price"><i class="cost">' . $item['price'] . '</i> <i class="currency">' . $item['currency'] . "</i></span>";
                                    }, $arrValues['prices']['children_tour_service']));
                        }
                        ?>
                    </td>
                    <td data-label="<?= GetMessage('TOURS_OFFERS_PLACES_TITLE')?>">
                        <?= (int) $arrValues['quota'] ?>
                    </td>
                </tr>

                <?
            }
        }
        ?>
    </tbody>
</table>