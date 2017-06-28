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
$this->setFrameMode(true);

if (empty($arResult['COST_PREPARED'])) {

    return;
}
?>

<table cellspacing="2" class="offers">

    <thead>
        <tr>
            <th>Дата</th>
            <th>Цена за взрослого</th>
            <th>Цена за ребёнка</th>
            <th>Туруслуга</th>
            <th>Свободных мест</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($arResult['COST_PREPARED'] as $ID => $arValues) {

            foreach ($arValues['dates'] as $arrValues) {
                ?>

                <tr>

                    <td data-label="Дата"><?= $arrValues['date_from'] . ' - ' . $arrValues['date_to'] ?></td>
                    <td data-label="Цена за взрослого">
                        <?
                        $aps = implode('<br>', array_map(function ($item) {

                                    return '<span class="offer-price"><i class="cost">' . $item['price'] . '</i> <i class="currency">' . $item['currency'] . "</i></span>";
                                }, $arrValues['prices']['adult']));

                        echo $aps;
                        ?>
                    </td>
                    <td data-label="Цена за ребёнка">
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
                    <td data-label="Туруслуга">
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
                    <td data-label="Свободных мест">
                        <?= (int) $arrValues['quota'] ?>
                    </td>
                </tr>

                <?
            }
        }
        ?>
    </tbody>
</table>