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

$ID = md5(serialize($arParams));
?>
<a rel="nofollow" id="booking-btn-<?= $ID ?>" <? if ($arResult['STYLE']): ?>style="<?= $arResult['STYLE'] ?>"<? endif ?> href="<?= $arResult['LINK'] ?>" class="booking-btn"><?= GetMessage('BOOKING_BTN_TITLE') ?></a>

<? if ($arParams['USE_FRAME_MODE'] == 'Y'): ?>

    <script>

        BX.ready(function () {

            var popup;

            var preloader;

            BX.bind(BX('booking-btn-<?= $ID ?>'), 'click', function (e) {

                if (popup) {

                    popup.show();

                } else {

                    preloader = BX.PopupWindowManager.create('popup-preloader-<?= $ID ?>', null, {

                        content: '<div class="popup-preloader-area"><img src="<?= $templateFolder . '/images/spiner-yellow.gif' ?>"></div>',
                        closeIcon: {
                            right: "10px",
                            top: "6px"
                        },
                        overlay: {
                            backgroundColor: '#ddd', opacity: '80'
                        }
                    });

                    preloader.show();

                    BX.ajax.get("<?= $arResult['LINK'] ?>", "", function (content) {

                        popup = BX.PopupWindowManager.create('popup-<?= $ID ?>', null, {

                            content: '<div class="popup-content-area" id="popup-content-area-<?= $ID ?>">' + content + '</div>',
                            closeIcon: {
                                right: "10px",
                                top: "6px"
                            },
                            lightShadow: true,
                            autoHide: true,
                            closeByEsc: true,
                            overlay: {
                                backgroundColor: '#ddd', opacity: '80'
                            }
                        });

                        preloader.close();

                        popup.show();

                    });

                }

                e.preventDefault();
            });
        });

    </script>

    <?


endif?>