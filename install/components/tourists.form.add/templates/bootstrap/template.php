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


$col = 6;

?>
<h2><?= GetMessage('TOURISTS_FORM_TITLE') ?></h2>

<?if ($arResult['ADDITING_ALLOWED']) {
    require 'form.php';
} else {
    require 'info.php';
}?>