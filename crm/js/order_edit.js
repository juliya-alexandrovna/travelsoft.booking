
$(document).ready(function () {

    'use strict';

    var $clientDomNode = $('.client-select');
    
    // select2 для клиентов
    function initClientSelect2() {

        initSelect2($clientDomNode, "/local/modules/travelsoft.booking/crm/ajax/users.php");
    }

    // select2 по туристам
    function initTouristSelect2($domNode) {

        window.initSelect2($domNode, "/local/modules/travelsoft.booking/crm/ajax/tourists.php");
    }
    
    // select2 по турам
    window.initSelect2($('.tour-select'), "/local/modules/travelsoft.booking/crm/ajax/tours.php", 'tours').on('select2:select', function (e) {

        var value = $(this).val();

        var item = window.selectResult.tours.filter(function (element) {

            return element.id == value;
        });

        if (typeof item[0] === 'object') {

            $('input[name=UF_SERVICE_NAME]').val(item[0].text);
            $('input[name=UF_COUNTRY]').val(item[0].country);
            $('input[name=UF_DEP_CITY]').val(item[0].point_departure);
            $('input[name=UF_ARR_CITY]').val(item[0].point_arrival);
            $('input[name=UF_FOOD]').val(item[0].food);
            $('input[name=UF_HOTEL]').val(item[0].hotel);
            $('input[name=UF_SERVICE_TYPE]').val(item[0].type);

            $("#calculation-btn-area").show();
        }
    });
    
    // select2 по менеджерам
    window.initSelect2($('.manager-select'), "/local/modules/travelsoft.booking/crm/ajax/users.php");

    initClientSelect2();

    $('.tourist-select').each(function () {

        initTouristSelect2($(this));
    });

    window.CRMUtils = {
        
        // добавление нового клиента
        addClient: function () {

            window.clientChildWindowData = {

                clientSelect: $clientDomNode,

                initClientSelect2: initClientSelect2
            }

            window.jsUtils.OpenWindow('/local/modules/travelsoft.booking/crm/client_edit_window.php', 600, 600);
        },
        
        // добавление нового туриста
        addTourist: function (domNodeLink) {

            window.touristChildWindowData = {

                touristSelect: $("#" + domNodeLink),

                initTouristSelect2: function () {

                    initTouristSelect2($("#" + domNodeLink));
                }
            }

            window.jsUtils.OpenWindow('/local/modules/travelsoft.booking/crm/tourist_edit_window.php', 700, 600);
        },
        
        // добаление полей добавления туриста
        addTouristField: function () {

            var timestamp = new Date().getTime();
            $("#tourists-add-table").find('tbody').append('<tr><td><select style="width:180px;" id="tourist-select-' + timestamp + '" class="tourist-select" name="UF_TOURISTS_ID[]"><option value="" selected="">Выбрать из списка</option></select> или <a href="javascript: CRMUtils.addTourist(\'tourist-select-' + timestamp + '\')">Добавить нового</a></td></tr>');
            initTouristSelect2($('#tourist-select-' + timestamp));
        },
        
        // рассчёт стоимости по выбранному туру
        calculate: function () {

            var ajaxData = {

                id: $('.tour-select').val(),
                dateFrom: $('input[name=UF_DATE_FROM]').val(),
                adults: $('input[name=UF_ADULTS]').val(),
                children: $('input[name=UF_CHILDREN]').val(),
                currency: $('select[name=UF_CURRENCY]').val(),

            },
                    errorsMessages = {

                        dateFrom: 'Укажите дату начала',
                        id: 'Укажите тур',
                        adults: 'Укажите количество взрослых',
                        currency: 'Укажите валюту'
                    },
                    errors = [], property;

            for (property in ajaxData) {

                if (ajaxData.hasOwnProperty(property) && !ajaxData[property] && typeof errorsMessages[property] === 'string') {

                    errors.push(errorsMessages[property]);
                }
            }

            if (errors.length > 0) {

                alert(errors.join('\n'));
                return false;
            }

            $.get('/local/modules/travelsoft.booking/crm/ajax/calculation.php', ajaxData, function (data) {

                if (!showAlert(data)) {

                if (typeof data.result === 'object') {

                    $('input[name=UF_COST]').val(data.result.UF_COST);
                    $('input[name=UF_DURATION]').val(data.result.UF_DURATION);
                    $('input[name=UF_DATE_TO]').val(data.result.UF_DATE_TO);

                }
            }

            });

            return null;
        },
        
        convertingCurrency (that) {
            
            var $this = $(that),
                    price = $('input[name=UF_COST]').val(),
                    currencyIn = $this.data("currency-in");
            
            if ($this.val() && price > 0 && currencyIn) {
                
                $.get("/local/modules/travelsoft.booking/crm/ajax/currency_converting.php", {
                    
                    price: price,
                    currency_out: $this.val(),
                    currency_in: currencyIn
                    
                }, function (data) {
                    
                    if (!showAlert(data)) {

                    
                    if (typeof data.result === 'string') {

                        $('input[name=UF_COST]').val(data.result);
                    }
                }
                });
            }
            
            $this.data("currency-in", $this.val());
        }
    };

});