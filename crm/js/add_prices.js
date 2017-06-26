
$(document).ready(function () {

    'use strict'

    /**
     * @type Boolean
     */
    var needInitialize = true

    /**
     * @type $
     */
    var settings = $('#settings').data('settings')

    /**
     * @type $
     */
    var toursSelect = $('#tours-select')

    /**
     * @type $
     */
    var pricetypesSelect = $('#pricetypes-select')

    /**
     * @type $
     */
    var calendar = $('#datepicker-area')

    /**
     * @type $
     */
    var calendarInputsArea = $("#dates-inputs-area")

    /**
     * @type $
     */
    var settingsForm = $('#settings-form')

    /**
     * @type $
     */
    var subSettings = $('.sub-part')

    /**
     * @type $
     */
    var sendFormBtn = $('button[name="settings[show]"]')

    /**
     * @type $
     */
    var tableFormHidder = $('#table-hidder')

    /**
     * @type Obejct
     */
    var tableFormArea = $('#table-form-area')

    /**
     * Возвращает html-строку для ajax-loader
     * @returns {String}
     */
    var getSpinLoaderHtml = function () {

        return "<div class='uil-spin-css' style='-webkit-transform:scale(0.15)'><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div><div><div></div></div></div>"
    }

    /**
     * Отображает ajax-loader
     * @returns {undefined}
     */
    var showSpinLoader = function () {

        sendFormBtn.html(getSpinLoaderHtml())
        sendFormBtn.prop('disabled', true)
    }

    /**
     * Скрывает ajax-loader
     * @returns {undefined}
     */
    var hideSpinLoader = function () {

        sendFormBtn.prop('disabled', false)
        sendFormBtn.html('Показать')
    }

    /**
     * Показывает dom-элемент
     * @param {Object} $domElement
     * @returns {undefined}
     */
    var visibleDOMElement = function ($domElement) {

        $domElement.removeClass('hidden')
    }

    /**
     * Скрывает dom-элемент
     * @param {Object} $domElement
     * @returns {undefined}
     */
    var unvisibleDOMElement = function ($domElement) {

        $domElement.addClass('hidden')
    }

    /**
     * Возвращает input выбранной даты
     * @param {string} id
     * @param {string} value
     * @returns {String}
     */
    var getInputDateHtml = function (id, value) {

        return '<input id="date-input-' + id + '" name="settings[dates][]" value="' + value + '" type="hidden">'
    }

    /**
     * Возвращает id input'а выбранной даты
     * @param {String} inp
     * @returns {String}
     */
    var getInputDateId = function (inp) {

        var result = inp

        while (result.indexOf('.') !== -1) {
            result = result.replace(".", "")
        }

        return result
    }

    /**
     * Инициализация типов цен
     * @param {Array} values
     * @returns {undefined}
     */
    var _initPricetypes = function () {

        var ptGroup

        var values = pricetypesSelect.val() || []

        pricetypesSelect.find('option').prop('disabled', false)

        for (var i = 0; i < values.length; i++) {

            ptGroup = pricetypesSelect.find('option[value="' + values[i] + '"]').data('group')

            if (ptGroup) {

                pricetypesSelect.find('option[data-group="' + ptGroup + '"]').each(function () {

                    var $this = $(this)

                    if (values[i] != $this.val()) {

                        $this.prop('disabled', true)
                    }
                })
            }

        }

    }

    /**
     * Инициализация работы jquery плагинов
     * @returns {undefined}
     */
    var init = function () {

        var today = new Date()
        var dates = []
        var date = ''

        var calendarOptions = {

            minDate: today,
            dateFormat: 'dd.mm.yy',
            onSelect: function (dateText) {

                var id = getInputDateId(dateText)
                var input

                input = $("#date-input-" + id)
                if (input.length) {

                    input.remove()
                } else {

                    calendarInputsArea.append(getInputDateHtml(id, dateText))
                }
            },
            numberOfMonths: [1, 4],
            defaultDate: today
        }

        if (needInitialize) {

            pricetypesSelect.on('change', function () {

                _initPricetypes()
            })

            if (typeof settings[toursSelect.val()] === 'object') {

                for (var i = 0; i < settings[toursSelect.val()].dates.length; i++) {

                    date = settings[toursSelect.val()].dates[i]

                    dates.push(date)

                    calendarInputsArea.append(getInputDateHtml(getInputDateId(date), date))
                }

                if (dates.length) {

                    calendarOptions.addDates = dates
                }
            }

            calendar.multiDatesPicker(calendarOptions)

            needInitialize = false
        }
    }

    /**
     * Производит установку значений jquery плагинов
     * @param {Number} tourid
     * @returns {undefined}
     */
    var setValues = function (tourid) {

        var date, ptGroup

        if (typeof settings[tourid] !== 'undefined') {

            pricetypesSelect.val(settings[tourid].pricetypes)
            _initPricetypes()

            for (var i = 0; i < settings[tourid].dates.length; i++) {

                date = settings[tourid].dates[i]

                calendar.multiDatesPicker('addDates', date)
                calendarInputsArea.append(getInputDateHtml(getInputDateId(date), date))
            }

        }
    }

    /**
     * Производит деинсталяцию значений jquery плагинов
     * @param {Number} tourid
     * @returns {undefined}
     */
    var unsetValues = function (tourid) {

        tourid = tourid || toursSelect.val()

        pricetypesSelect.val(null)
        _initPricetypes()
        if (typeof settings[tourid] !== 'undefined') {
            for (var i = 0; i < settings[tourid].dates.length; i++) {

                calendar.multiDatesPicker('removeDates', settings[tourid].dates[i])
            }
            calendarInputsArea.html('')
        }
    }

    /**
     * Обрабатывает ответ сервера на запрос формы настроек пользователя
     * @param {Object} data
     * @returns {undefined}
     */
    var processServerResponseBySettingsRequest = function (data) {

        try {

            if (data.errors.length) {

                throw new Error(data.errors.join('\n'))
            }

            if (typeof data.settings === 'object') {

                settings = data.settings
            }

            if (typeof data.responseBody === 'string' && data.responseBody.length) {

                tableFormArea.html(data.responseBody)
            }

        } catch (error) {

            alert(error.message)
        }
    }

    /**
     * Инициирует отправку ajax формы настроек пользователя
     * @param {Object} form
     * @returns {undefined}
     */
    var settingsFormAjaxSubmit = function (form) {

        formAjaxSubmit(form, showSpinLoader, hideSpinLoader, processServerResponseBySettingsRequest)
    }

    /**
     * Инициирует отправку формы
     * @param {Object} btn
     * @returns {undefined}
     */
    var triggerFormAjaxSubmit = function (btn) {

        $(btn).closest('form').trigger('submit')
    }

    /**
     * Отпрака формы ajax'ом
     * @param {$} form
     * @param {Function} beforeSend
     * @param {Function} complete
     * @param {Function} success
     * @returns {undefined}
     */
    var formAjaxSubmit = function (form, beforeSend, complete, success) {

        var options = {

            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            processData: false
        }


        if (typeof beforeSend === 'function') {

            options.beforeSend = beforeSend
        }

        if (typeof complete === 'function') {

            options.complete = complete
        }

        if (typeof success === 'function') {

            options.success = success
        }

        $.ajax(options)
    }


    var processPriceAndQuotasResponse = function (data) {

        function _setInputValue(s) {

            $('input[name="prices_and_quotas' + s + '"]')
        }

        try {

            if (typeof data.quotas === 'object') {

                for (var uxd in data.quotas) {

                    $('input[name="prices_and_quotas[quotas][' + uxd + ']"]').val(data.quotas[uxd].quota_value > 0 ? data.quotas[uxd].quota_value : 0)
                    $('#sold-' + uxd).text(data.quotas[uxd].sold_value || 0)
                    $('#on-sale-' + uxd).text(data.quotas[uxd].onsale_value || 0)
                }
            }

            if (typeof data.stop_sale === 'object') {

                for (var uxd in data.stop_sale) {

                    $('select[name="prices_and_quotas[stop_sale][' + uxd + ']"]').val(data.stop_sale[uxd] ? 1 : 0)
                }
            }

            if (typeof data.prices === 'object') {

                for (var ptid in data.prices) {

                    for (uxd in data.prices[ptid]) {

                        $('input[name="prices_and_quotas[prices][' + ptid + '][' + uxd + ']"]').val(data.prices[ptid][uxd] > 0 ? data.prices[ptid][uxd] : 0)
                    }
                }
            }
        } catch (error) {

            alert(error.message)
        }
    }

    /**
     * Объект функций для доступа из контекста страницы
     */
    window.CRMUtils = {

        /**
         * @type Object
         */
        _popupFroms: {},

        /**
         * Инициализирует показ попап формы массового редактирования цен и квот
         * @param {type} settings
         * @returns {undefined}
         */
        initPopupForm: function (popup) {

            if (typeof CRMUtils._popupFroms[popup.id] === 'undefined') {

                CRMUtils._popupFroms[popup.id] = new BX.CDialog(popup)
            }

            CRMUtils._popupFroms[popup.id].Show()
        },

        /**
         * Инициирует отправку формы заполнения цен и квот
         * @param {Object} btn
         * @returns {undefined}
         */
        triggerPriceAndQuotasFormAjaxSubmit: function (btn) {

            triggerFormAjaxSubmit(btn)
        },

        /**
         * Отправка формы заполнения цен и квот
         * @param {Object} form
         * @returns {Boolean}
         */
        priceAndQuotasFormAjaxSubmit: function (form) {

            formAjaxSubmit($(form), function () {
                tableFormHidder.show()
            }, function () {
                tableFormHidder.hide()
            }, processPriceAndQuotasResponse)

            return false
        },

        /**
         * Отправка формы массового редактирования цен и квот
         * @param {String} formid
         * @returns {Boolean}
         */
        massEditFormAjaxSubmit: function (formid) {

            formAjaxSubmit($('#' + formid), null, function () {
                BX.WindowManager.Get().Close()
            }, processPriceAndQuotasResponse)
            return false
        },
    }

    toursSelect.select2({

        allowClear: true,
        placeholder: 'Выберите тур'
    }).on('select2:selecting', function () {

        visibleDOMElement(subSettings)
        init()
        unsetValues()
        tableFormArea.html('')
    })
            .on('select2:select', function () {

                var tourid = toursSelect.val()
                setValues(tourid)
                if (typeof settings[tourid] === 'object') {
                    settingsFormAjaxSubmit(settingsForm)
                }
            }).on('select2:unselecting', function () {

        tableFormArea.html('')
        unsetValues(toursSelect.val())
        unvisibleDOMElement(subSettings)
    })

    if (toursSelect.val() > 0) {

        visibleDOMElement(subSettings)
        init()
        settingsFormAjaxSubmit(settingsForm)
    }

    // ajax отправка формы настроек
    settingsForm.on('submit', function (ev) {

        ev.preventDefault()

        settingsFormAjaxSubmit($(this))
    })

})


