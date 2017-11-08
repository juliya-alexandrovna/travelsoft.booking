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
function _er(string $code, bool $div = false, string $message = '') {

    $message = strlen($message) > 0 ? $message : GetMessage($code);

    if ($div) {
        echo '<div class="error">' . $message . '</div>';
    } else {
        echo '<span class="error">' . $message . '</span>';
    }
}

$arSource = $arResult['COST']->getSource();
$arSourceByOffer = current($arSource[$arResult['OFFER']['ID']]);
?>

<form class="booking-form" id="booking-form-<?= $arResult['HASH'] ?>" method="POST" action="<?= $APPLICATION->GetCurPageParam() ?>">
    <?= bitrix_sessid_post() ?>
    <input name="HASH" type="hidden" value="<?= $arResult['HASH'] ?>">
    <div class="row">

        <h2 id="booking-form-title-<?= $arResult['HASH'] ?>" class="border-bottom-grey"><?= GetMessage('BOOKING_FORM_TITLE') ?></h2>
        <?
        if (in_array('ORDER_CREATION_FAIL', $arResult['CODE_ERRORS'])) {
            _er('BOOKING_FORM_ORDER_CREATION_FAIL', true);
        }
        ?>
        <div class="desc-block">

            <div class="desc-part"><?= GetMessage('BOOKING_FORM_SERVICE_TYPE') ?>: <b><?= $arResult['OFFER']['NAME'] ?></b></div>
            <div class="desc-part"><?= GetMessage('BOOKING_FORM_DATE_FROM') ?>: <b><?= $arResult['DATE_FROM'] ?></b></div>
            <div class="desc-part"><?= GetMessage('BOOKING_FORM_DATE_TO') ?>: <b><?= $arResult['DATE_TO'] ?></b></div>
            <div class="desc-part"><?= GetMessage('BOOKING_FORM_DURATION') ?>: <b><?= $arResult['DURATION'] ?></b></div>
            <div id="quota-in-sale-<?= $arResult['HASH'] ?>" class="desc-part"><?= GetMessage('BOOKING_FORM_QUOTA') ?>: <b><?= $arResult['QUOTA'] ?></b></div>
        </div>
        <?/* if ($arResult['IS_AUTH_USER']): ?>
            <div class="desc-block">
                <blockquote><p><?= GetMessage('BOOKING_FORM_ORDER_INFORMATION') ?></p></blockquote>
            </div>
        <? endif; */?>
        <div class="contact-info-block">
            <? if (!$arResult['IS_AUTH_USER']): ?>

                    <input type="hidden" name="FIRST_TIME" value="N">
                <div class="booking_step row"><label class="booking_step_activ"><i class="fa">1</i> <?= GetMessage('BOOKING_FORM_STEP_1') ?></label></div>
                <div class="form-group <? if (in_array('WRONG_USER_EMAIL', $arResult['CODE_ERRORS']) || in_array('USER_NOT_FOUND', $arResult['CODE_ERRORS'])): ?>has-error<? endif ?>">

                    <label for="USER_EMAIL"><?= GetMessage('BOOKING_FORM_USER_EMAIL') ?></label>
                    <?
                    if (in_array('WRONG_USER_EMAIL', $arResult['CODE_ERRORS'])) {
                        _er('BOOKING_FORM_WRONG_USER_EMAIL');
                    }
                    if (in_array('USER_NOT_FOUND', $arResult['CODE_ERRORS'])) {
                        _er('BOOKING_FORM_USER_NOT_FOUND');
                    }
                    if (in_array('REGISTER_FAIL', $arResult['CODE_ERRORS'])) {
                        _er('BOOKING_FORM_REGISTER_FAIL');
                    }
                    ?>
                    <input class="form-control" name="USER_EMAIL" value="<?= $arResult['USER_EMAIL'] ?>" type="email">
                </div>

                <div class="form-group <? if (in_array('WORNG_PASSWORD', $arResult['CODE_ERRORS']) || in_array('WRONG_ENTERED_USER_PASSWORD', $arResult['CODE_ERRORS'])): ?>has-error<? endif ?>">
                    <label for="PASSWORD"><?= GetMessage('BOOKING_FORM_PASSWORD') ?></label>
                    <?
                    if (in_array('WORNG_PASSWORD', $arResult['CODE_ERRORS'])) {
                        _er('WORNG_PASSWORD', false, $arResult['SYSTEM_ERRORS_MESSAGES']['WORNG_PASSWORD']);
                    }
                    if (in_array('WRONG_ENTERED_USER_PASSWORD', $arResult['CODE_ERRORS'])) {
                        _er('BOOKING_FORM_WRONG_ENTERED_USER_PASSWORD');
                    }
                    ?>
                    <input class="form-control" name="PASSWORD" type="password">
                </div>
                <div class="first-time-block hidden form-group <? if (in_array('WRONG_PASSWORD_CONFORMATION', $arResult['CODE_ERRORS'])): ?>has-error<? endif ?>">
                    <label for="CONFIRM_PASSWORD"><?= GetMessage('BOOKING_FORM_CONFIRM_PASSWORD') ?></label>
                    <?
                    if (in_array('WRONG_PASSWORD_CONFORMATION', $arResult['CODE_ERRORS'])) {
                        _er('BOOKING_FORM_WRONG_PASSWORD_CONFORMATION');
                    }
                    ?>
                    <input class="form-control" name="CONFIRM_PASSWORD" type="password">
                </div>
                <div class="switches">
                    <a rel="nofollow" class="switch register" href="#"><?= GetMessage('BOOKING_FORM_REGISTER_CLIENT_LINK')?> &nbsp;&nbsp;&nbsp;&nbsp;</a>
                    <a rel="nofollow" class="register" href="/auth/?register=yes&isAgent=yes"><?= GetMessage('BOOKING_FORM_REGISTER_AGENT_LINK')?></a>
                    <a rel="nofollow" class="auth switch hidden" href="#"><?= GetMessage('BOOKING_FORM_AUTH_CLIENT_LINK')?> &nbsp;&nbsp;&nbsp;&nbsp;</a>
                    <a rel="nofollow" class="auth switch hidden" href="#"><?= GetMessage('BOOKING_FORM_AUTH_AGENT_LINK')?></a>
                </div>
                <div class="booking_step row"><label class="booking_step_activ"><i class="fa">2</i> <?= GetMessage('BOOKING_FORM_STEP_2') ?></label></div>
                <div class="first-time-block hidden">

                    <div class="form-group <? if (in_array('WRONG_USER_LAST_NAME', $arResult['CODE_ERRORS'])): ?>has-error<? endif ?>">

                        <label for="USER_LAST_NAME"><?= GetMessage('BOOKING_FORM_USER_LAST_NAME') ?></label>
                        <?
                        if (in_array('WRONG_USER_LAST_NAME', $arResult['CODE_ERRORS'])) {
                            _er('BOOKING_FORM_WRONG_USER_LAST_NAME');
                        }
                        ?>
                        <input class="form-control" name="USER_LAST_NAME" value="<?= $arResult['USER_LAST_NAME'] ?>" type="text">
                    </div>

                    <div class="form-group <? if (in_array('WRONG_USER_NAME', $arResult['CODE_ERRORS'])): ?>has-error<? endif ?>">
                        <label for="USER_NAME"><?= GetMessage('BOOKING_FORM_USER_NAME') ?></label>
                        <?
                        if (in_array('WRONG_USER_NAME', $arResult['CODE_ERRORS'])) {
                            _er('BOOKING_FORM_WRONG_USER_NAME');
                        }
                        ?>
                        <input class="form-control" name="USER_NAME" value="<?= $arResult['USER_NAME'] ?>" type="text">
                    </div>
                </div>

            <? endif ?>
            <? if ($arResult['IS_AUTH_USER']): ?>
                <div class="booking_step row"><label class="booking_step_activ"><i class="fa">1</i> <?= GetMessage('BOOKING_FORM_STEP_2') ?></label></div>
            <? endif; ?>
            <div class="form-group <? if (in_array('WRONG_USER_PHONE', $arResult['CODE_ERRORS'])): ?>has-error<? endif ?>">
                <label for="USER_PHONE"><?= GetMessage('BOOKING_FORM_USER_PHONE') ?></label>
                <?
                if (in_array('WRONG_USER_NAME', $arResult['CODE_ERRORS'])) {
                    _er('BOOKING_FORM_WRONG_USER_NAME');
                }
                ?>
                <input class="form-control" name="USER_PHONE" value="<?= $arResult['USER_PHONE'] ?>" type="tel">
            </div>

            <div class="form-group">
                <label for="USER_COMMENT"><?= GetMessage('BOOKING_FORM_USER_COMMENT') ?></label>
                <textarea class="form-control" name="USER_COMMENT"><?= $arResult['USER_COMMENT'] ?></textarea>
            </div>

        </div>
        <div class="booking_step row"><label class="booking_step_activ"><i class="fa"><? if ($arResult['IS_AUTH_USER']): ?>2<? else: ?>3<? endif ?></i> <?= GetMessage('BOOKING_FORM_STEP_3') ?></label></div>
        <div class="cost-block">

            <?
            if (in_array('WRONG_PEOPLE_COUNT', $arResult['CODE_ERRORS']) || in_array('QUOTA_OVERLOAD', $arResult['CODE_ERRORS'])) {
                $hasError = true;
            }
            ?>
            <?
            if (in_array('WRONG_PEOPLE_COUNT', $arResult['CODE_ERRORS'])) {
                _er('BOOKING_FORM_WRONG_PEOPLE_COUNT', true);
            }
            if (in_array('QUOTA_OVERLOAD', $arResult['CODE_ERRORS'])) {
                _er('BOOKING_FORM_QUOTA_OVERLOAD', true);
            }
            ?>
            <div class="form-group <? if ($hasError): ?>has-error<? endif ?>">
                <div class="row">
                    <div class="col-md-4 col-xs-4 col-sm-4">
                        <label for="ADULTS"><?= GetMessage('BOOKING_FORM_ADULTS') ?></label>
                        <?
                        $p = $arResult['ADULT_PRICE'];
                        if ($arResult['ADULT_TOUR_SERVICE_PRICE_FORMATTED']) {
                            $p += $arResult['ADULT_TOUR_SERVICE_PRICE_FORMATTED'];
                        }
                        ?>
                        <input data-price="<?= $p ?>" type="number" min="0" name="ADULTS" class="form-control people-cnt" value="<?= $arResult['ADULTS'] ?>">
                    </div>
                    <div class="col-md-1 col-xs-1 col-sm-1 pdt-27">
                        <span class="factor">X</span>
                    </div>
                    <div class="col-md-5 col-xs-5 col-sm-5 <? if ($arResult['ADULT_TOUR_SERVICE_PRICE_FORMATTED']): ?>pdt-15<? else: ?>pdt-27<? endif ?> text-center">
                        <span class="adult-cost">
                            <b><?= $arResult['ADULT_PRICE_FORMATTED'] ?></b>
                            (<?= $arSourceByOffer['prices']['adult']['price'] . ' ' . $arSourceByOffer['prices']['adult']['currency'] ?>)
                            <? if ($arResult['ADULT_TOUR_SERVICE_PRICE_FORMATTED']): ?>
                                <br>+<br>
                                <b><?= $arResult['ADULT_TOUR_SERVICE_PRICE_FORMATTED'] ?></b>
                                <?= GetMessage('BOOKING_FORM_TOUR_SERVICE') ?>
                            <? endif ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group <? if ($hasError): ?>has-error<? endif ?>">
                <div class="row">
                    <div class="col-md-4 col-xs-4 col-sm-4">
                        <label for="CHILDREN"><?= GetMessage('BOOKING_FORM_CHILDREN') ?></label>
                        <?
                        $p = $arResult['CHILDREN_PRICE'];
                        if ($arResult['CHILDREN_TOUR_SERVICE_PRICE_FORMATTED']) {
                            $p += $arResult['CHILDREN_TOUR_SERVICE_PRICE_FORMATTED'];
                        }
                        ?>
                        <input data-price="<?= $p ?>" type="number" min="0" name="CHILDREN" class="form-control people-cnt" value="<?= $arResult['CHILDREN'] ?>">
                    </div>
                    <div class="col-md-1 col-xs-1 col-sm-1 pdt-27">
                        <span class="factor">X</span>
                    </div>
                    <div class="col-md-5 col-xs-5 col-sm-5 <? if ($arResult['CHILDREN_TOUR_SERVICE_PRICE_FORMATTED']): ?>pdt-15<? else: ?>pdt-27<? endif ?> text-center">
                        <span class="children-cost">
                            <b><?= $arResult['CHILDREN_PRICE_FORMATTED'] ?></b>
                            (<?= $arSourceByOffer['prices']['children']['price'] . ' ' . $arSourceByOffer['prices']['children']['currency'] ?>)
                            <? if ($arResult['CHILDREN_TOUR_SERVICE_PRICE_FORMATTED']): ?>
                                <br>+<br>
                                <b><?= $arResult['CHILDREN_TOUR_SERVICE_PRICE_FORMATTED'] ?></b>
                                <?= GetMessage('BOOKING_FORM_TOUR_SERVICE') ?>
                            <? endif ?>
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <b><?= GetMessage('BOOKING_FORM_TOTAL_COST') ?> <span id="total-<?= $arResult['HASH'] ?>">0.00</span> <?= $arParams['CONVERT_IN_CURRENCY_ISO'] ?></b>
            </div>
        </div>

        <button id="booking-btn-<?= $arResult['HASH'] ?>" class="booking-btn form-control" name="BOOKING_NOW" value="BOOKING_NOW" type="submit"><?= GetMessage('BOOKING_FORM_BUTTON_TITLE') ?></button>
        <div class="booking_step row"><label class="booking_step_activ"><i class="fa"><? if ($arResult['IS_AUTH_USER']): ?>3<? else: ?>4<? endif ?></i> <?= GetMessage('BOOKING_FORM_STEP_4') ?></label></div>
    </div>
</form>

<script>

    top.BX.loadCSS('<?= $templateFolder . '/s.css?v13' ?>');
    top.BX.ready(function () {

        try {

            var form = top.BX('booking-form-<?= $arResult['HASH'] ?>');

            function _cleanErrors() {

                top.BX.findChildren(form, {className: 'form-group'}, true).forEach(function (el) {

                    top.BX.removeClass(el, 'has-error');
                });

                top.BX.findChildren(form, {className: 'error'}, true).forEach(function (el) {

                    top.BX.remove(el);
                });

            }

            // PRICE TO FORMAT
            function _formatPrice(price) {

                var n = 2;
                var s = ' ';
                var c = '.';

                var re = '\\d(?=(\\d{3})+' + (n > 0 ? '\\D' : '$') + ')';
                var num = price.toFixed(Math.max(0, ~~n));

                return (c ? num.replace('.', c) : num).replace(new RegExp(re, 'g'), '$&' + (s || ','));
            }

            // SCROLL TO ERROR
            function _scrollToError() {
                top.BX.scrollToNode(top.BX.findChild(form, {className: 'error'}, true));
            }

            // GET ERROR HTML
            function _er(code, div, message) {

                var messages = {

                    BOOKING_FORM_ORDER_CREATION_FAIL: '<?= GetMessage('BOOKING_FORM_ORDER_CREATION_FAIL') ?>',
                    BOOKING_FORM_REGISTER_FAIL: '<?= GetMessage('BOOKING_FORM_REGISTER_FAIL') ?>',
                    BOOKING_FORM_WRONG_USER_EMAIL: '<?= GetMessage('BOOKING_FORM_WRONG_USER_EMAIL') ?>',
                    BOOKING_FORM_USER_NOT_FOUND: '<?= GetMessage('BOOKING_FORM_USER_NOT_FOUND') ?>',
                    BOOKING_FORM_WRONG_PASSWORD: '<?= GetMessage('BOOKING_FORM_WORNG_PASSWORD') ?>',
                    BOOKING_FORM_WRONG_ENTERED_USER_PASSWORD: '<?= GetMessage('BOOKING_FORM_WRONG_ENTERED_USER_PASSWORD') ?>',
                    BOOKING_FORM_WRONG_PASSWORD_CONFORMATION: '<?= GetMessage('BOOKING_FORM_WRONG_PASSWORD_CONFORMATION') ?>',
                    BOOKING_FORM_WRONG_USER_LAST_NAME: '<?= GetMessage('BOOKING_FORM_WRONG_USER_LAST_NAME') ?>',
                    BOOKING_FORM_WRONG_USER_NAME: '<?= GetMessage('BOOKING_FORM_WRONG_USER_NAME') ?>',
                    BOOKING_FORM_WRONG_USER_PHONE: '<?= GetMessage('BOOKING_FORM_WRONG_USER_PHONE') ?>',
                    BOOKING_FORM_WRONG_PEOPLE_COUNT: '<?= GetMessage('BOOKING_FORM_WRONG_PEOPLE_COUNT') ?>',
                    BOOKING_FORM_QUOTA_OVERLOAD: '<?= GetMessage('BOOKING_FORM_QUOTA_OVERLOAD') ?>',

                }

                var tag = div === true ? 'div' : 'span';

                code = code || '';

                message = message || (typeof messages[code] !== 'undefined' ? messages[code] : '');

                return top.BX.create(tag, {attrs: {className: 'error'}, text: ' ' + message});

            }
            
            function toggleShowRegAuthLinks (classNameForHidden) {
                var fh = classNameForHidden;
                var fs = classNameForHidden === 'register' ? 'auth' : 'register';
                top.BX.findChildrenByClassName(form, fs).forEach(function (el) {
                    top.BX.removeClass(el, 'hidden');
                });
                top.BX.findChildrenByClassName(form, fh).forEach(function (el) {
                    top.BX.addClass(el, 'hidden');
                });
            }
            
            // FIELDS SWITCHES
            top.BX.bindDelegate(form, 'click', {className: 'switch'}, function (e) {

                var firstTime = top.BX.findChild(form, {tag: 'input', attribute: {name: 'FIRST_TIME'}}, true);

                    if (top.BX.hasClass(this, 'auth')) {

                        top.BX.findChildrenByClassName(form, 'first-time-block').forEach(function (el) {
                            top.BX.addClass(el, 'hidden');
                        });
                        toggleShowRegAuthLinks('auth');
                        firstTime.value = 'N';
                    } else {

                        top.BX.findChildrenByClassName(form, 'first-time-block').forEach(function (el) {
                            top.BX.removeClass(el, 'hidden');
                        });
                        toggleShowRegAuthLinks('register');
                        firstTime.value = 'Y';
                    }

                return BX.PreventDefault(e);
            });

            // TOTAL PRICE CALCULATION
            top.BX.bindDelegate(form, 'change', {className: 'people-cnt'}, function () {

                var total = 0;
                top.BX.findChildrenByClassName(form, 'people-cnt', true).forEach(function (el) {

                    total = total + (el.dataset.price * el.value);
                });

                top.BX('total-<?= $arResult['HASH'] ?>').innerHTML = _formatPrice(total);
            });

            // AJAX SUBMIT FORM
            if (<? if ($arParams['USE_AJAX_MODE'] == 'Y'): ?>true<? else: ?>false<? endif ?>) {

                            // FORM VALIDATION
                            top.BX.bind(form, 'submit', function () {

                                var adults = top.BX.findChild(this, {tagName: 'input', attribute: {name: 'ADULTS'}}, true);
                                var firstTime = top.BX.findChild(this, {tag: 'input', attribute: {name: 'FIRST_TIME'}}, true);
                                var email = top.BX.findChild(this, {tagName: 'input', attribute: {name: 'USER_EMAIL'}}, true);
                                var password = top.BX.findChild(this, {tagName: 'input', attribute: {name: 'PASSWORD'}}, true);
                                var name = top.BX.findChild(this, {tagName: 'input', attribute: {name: 'USER_NAME'}}, true);
                                var lastName = top.BX.findChild(this, {tagName: 'input', attribute: {name: 'USER_LAST_NAME'}}, true);
                                var phone = top.BX.findChild(this, {tagName: 'input', attribute: {name: 'USER_PHONE'}}, true);
                                var confirmPassword = top.BX.findChild(this, {tagName: 'input', attribute: {name: 'CONFIRM_PASSWORD'}}, true);
                                var authUser = <? if ($arResult['IS_AUTH_USER']): ?>true<? else: ?>false<? endif ?>;
                                                    var successTmpStr = '<?= GetMessage('BOOKING_FORM_SUCCESS', array('#ORDER_DETAIL_PAGE#' => $arParams['ORDER_DETAIL_PAGE'])) ?>';
                                                    top.BX.ajax.post(this.action,
                                                            (top.BX.ajax.prepareForm(this, {
                                                                BOOKING_NOW: 'BOOKING_NOW',
                                                                USER_EMAIL: !authUser ? email.value : '',
                                                                USER_PHONE: phone.value

                                                            })).data, function (resp) {

                                                        resp = JSON.parse(resp);

                                                        if (typeof resp.success !== 'undefined' && resp.success === true) {
                                                            // TEMPORARY SOLUTION
                                                            top.BX.insertAfter(top.BX.create('div', {attrs: {className: 'success'}, html: successTmpStr.replace("#ORDER_ID#", resp.order_id)}), top.BX('booking-form-title-<?= $arResult['HASH'] ?>'));
                                                            top.BX.scrollToNode(form);
                                                            top.BX.remove(top.BX('booking-btn-<?= $arResult['HASH'] ?>'));
                                                            top.BX.remove(top.BX.findChildByClassName(form, 'cost-block', true));
                                                            top.BX.remove(top.BX.findChildByClassName(form, 'contact-info-block', true));
                                                            if (typeof resp.in_sale !== 'undefined') {
                                                                top.BX.findChild(top.BX('quota-in-sale-<?= $arResult['HASH'] ?>', {tag: 'b'}, true)).innerHTML = resp.in_sale;
                                                            }
                                                            top.window.location = '<?= $arParams['ORDER_DETAIL_PAGE'] ?>'.replace("#ORDER_ID#", resp.order_id);
                                                        } else if (typeof resp.errors !== 'undefined' && top.BX.type.isArray(resp.errors)) {

                                                            _cleanErrors();

                                                            for (var i = 0; i < resp.errors.length; i++) {

                                                                switch (resp.errors[i]) {

                                                                    case 'ORDER_CREATION_FAIL':

                                                                        BX.prepend(_er('BOOKING_FORM_ORDER_CREATION_FAIL', true), top.BX.findChildByClassName(form, 'desc-block', true));

                                                                        break;

                                                                    case 'WRONG_USER_EMAIL':
                                                                    case 'REGISTER_FAIL':

                                                                        top.BX.addClass(top.BX.findParent(email, {className: 'form-group'}), 'has-error');
                                                                        top.BX.insertAfter(_er('BOOKING_FORM_' + resp.errors[i]), top.BX.findPreviousSibling(email, {tag: 'LABEL', attribute: {'for': 'USER_EMAIL'}}));

                                                                        break;

                                                                    case 'USER_NOT_FOUND':

                                                                        top.BX.addClass(top.BX.findParent(email, {className: 'form-group'}), 'has-error');
                                                                        top.BX.insertAfter(_er('BOOKING_FORM_USER_NOT_FOUND'), top.BX.findPreviousSibling(email, {tag: 'LABEL', attribute: {'for': 'USER_EMAIL'}}));

                                                                        break;

                                                                    case 'WRONG_PASSWORD':

                                                                        top.BX.addClass(top.BX.findParent(password, {className: 'form-group'}), 'has-error');
                                                                        top.BX.insertAfter(_er('BOOKING_FORM_WRONG_PASSWORD', false, typeof resp.system_errors_messages.WRONG_PASSWORD !== 'undefined' ? resp.system_errors_messages.WRONG_PASSWORD : null), top.BX.findPreviousSibling(password, {tag: 'LABEL', attribute: {'for': 'PASSWORD'}}));

                                                                        break;

                                                                    case 'WRONG_ENTERED_USER_PASSWORD':

                                                                        top.BX.addClass(top.BX.findParent(password, {className: 'form-group'}), 'has-error');
                                                                        top.BX.insertAfter(_er('BOOKING_FORM_WRONG_ENTERED_USER_PASSWORD'), top.BX.findPreviousSibling(password, {tag: 'LABEL', attribute: {'for': 'PASSWORD'}}));


                                                                        break;

                                                                    case 'WRONG_PASSWORD_CONFORMATION':

                                                                        top.BX.addClass(top.BX.findParent(confirmPassword, {className: 'form-group'}), 'has-error');
                                                                        top.BX.insertAfter(_er('BOOKING_FORM_WRONG_PASSWORD_CONFORMATION'), top.BX.findPreviousSibling(confirmPassword, {tag: 'LABEL', attribute: {'for': 'CONFIRM_PASSWORD'}}));

                                                                        break;

                                                                    case 'WRONG_USER_LAST_NAME':

                                                                        top.BX.addClass(top.BX.findParent(lastName, {className: 'form-group'}), 'has-error');
                                                                        top.BX.insertAfter(_er('BOOKING_FORM_WRONG_USER_LAST_NAME'), top.BX.findPreviousSibling(lastName, {tag: 'LABEL', attribute: {'for': 'USER_LAST_NAME'}}));
                                                                        break;

                                                                    case 'WRONG_USER_NAME':

                                                                        top.BX.addClass(top.BX.findParent(name, {className: 'form-group'}), 'has-error');
                                                                        top.BX.insertAfter(_er('BOOKING_FORM_WRONG_USER_NAME'), top.BX.findPreviousSibling(name, {tag: 'LABEL', attribute: {'for': 'USER_NAME'}}));
                                                                        break;

                                                                    case 'WRONG_USER_PHONE':

                                                                        top.BX.addClass(top.BX.findParent(phone, {className: 'form-group'}), 'has-error');
                                                                        top.BX.insertAfter(_er('BOOKING_FORM_WRONG_USER_PHONE'), top.BX.findPreviousSibling(phone, {tag: 'LABEL', attribute: {'for': 'USER_PHONE'}}));
                                                                        break;

                                                                    case 'WRONG_PEOPLE_COUNT':
                                                                    case 'WRONG_QUOTA_OVERLOAD':

                                                                        top.BX.addClass(top.BX.findParent(adults, {className: 'form-group'}), 'has-error');
                                                                        BX.prepend(_er('BOOKING_FORM_WRONG_PEOPLE_COUNT', true), top.BX.findChildByClassName(form, 'cost-block', true));

                                                                        break;
                                                                }
                                                            }

                                                            _scrollToError();
                                                        }

                                                    });

                                                    return top.BX.PreventDefault();

                                                });

                                            }



                                        } catch (e) {

                                            console.error(e);
                                            alert('Application error');
                                            top.BX.PreventDefault();

                                        }

                                    });


</script>