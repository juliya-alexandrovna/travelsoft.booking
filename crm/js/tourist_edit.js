
$(document).ready(function () {
    
    'use strict';
    
    window.initSelect2($('#tpl-user'), "/local/modules/travelsoft.booking/crm/ajax/users.php", 'tplUser').on('select2:select', function (e) {
        
        var value = $(this).val();
        
        var item = window.selectResult.tplUser.filter(function (element) {
            
            return element.id == value;
        });
        
        if (typeof item[0] === 'object') {
            
            $('input[name=UF_NAME]').val(item[0].name);
            $('input[name=UF_LAST_NAME]').val(item[0].last_name);
            $('input[name=UF_SECOND_NAME]').val(item[0].second_name);
            $('input[name=UF_PASS_ISSUED_BY]').val(item[0].issued_by);
            $('input[name=UF_PASS_DATE_ISSUE]').val(item[0].date_issue);
            $('input[name=UF_PASS_PERNUM]').val(item[0].pernum);
            $('input[name=UF_PASS_NUMBER]').val(item[0].number);
            $('input[name=UF_PASS_SERIES]').val(item[0].series);
            $('input[name=UF_PASS_ACTEND]').val(item[0].actend);
        }
    });
});
