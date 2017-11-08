$(document).ready(function () {

    function newOrderChecker(withNotify) {

        setInterval(function () {
            
            $.post("/local/modules/travelsoft.booking/crm/ajax/new_order_checker.php", {
                
                sessid: CRM.config.sessid,
                last_id: CRM.config.last_id
                
            }, function (data) {

                if (!CRM.utils.showAlert(data)) {

                    if (typeof data.result === 'object' && data.result) {

                        if (typeof data.result.content === 'string' && typeof data.result.content != '') {

                            // add table row new order
                            $("#" + CRM.config.table_id).prepend(data.result.content);
                            CRM.config.last_id = data.result.last_id;
                        }

                        if (data.result.last_id && withNotify) {

                            // show notify
                            CRM.utils.showNotify({

                                title: CRM.config.notifyTitle,
                                icon: CRM.config.notifyIcon,
                                sound: CRM.config.notifySound
                            });
                        }

                    }

                }

            });

        }, CRM.config.time_interval);

    }

    if (typeof CRM.config.table_id === 'string') {

        if (!("Notification" in window) || Notification.permission === 'denied') {

            newOrderChecker(false);

        } else if (Notification.permission === 'granted') {

            newOrderChecker(true);

        } else {

            Notification.requestPermission(function (permission) {

                if (permission === "granted") {

                    newOrderChecker(true);
                } else {

                    newOrderChecker(false);
                }
            });

        }
    }
});