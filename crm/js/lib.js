
'use strict';

/**
 * Booking admin library
 */

window.selectResult = {};

/**
 * select2 ajax
 * @param {jQuery dom node} $domNode
 * @param {String} url
 * @param {String} scope
 * @returns {jQuery dom node}
 */
window.initSelect2 = function ($domNode, url, scope) {

    $domNode.select2({
        ajax: {
            url: url,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term
                };
            },
            processResults: function (data) {

                if (typeof data.error === 'string') {

                    alert(data.error);
                } else if (Array.isArray(data.items)) {

                    window.selectResult[scope] = data.items;
                    return {
                        results: data.items
                    };
                }

                return {};
            },
            cache: true
        },
        minimumInputLength: 1
    });

    return $domNode;
};

/**
 * show alert
 * @param {Object} data
 * @returns {Boolean}
 */
window.showAlert = function (data) {

    if (typeof data.error === 'string') {

        alert(data.error);
        return true;
    }

    return false;
};

/**
 * Display notify
 * @param {Object} data
 * @returns {undefined}
 */
window.showNotify = function (data) {

    var options = {

        body: typeof data.body === 'string' ? data.body : '',
        icon: typeof data.icon === 'string' ? data.icon : '',
        silent: typeof data.silent === 'boolean' ? data.silent : true
    };

    var notifyWindow = new Notification(typeof data.title === 'string' ? data.title : '', options);
    notifyWindow.onclick = function (e) {

        console.log(e);
    };
    
    notifyWindow.onshow = function (e) {

        console.log('shown');
    };

};