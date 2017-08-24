/**
 * Object of utilites and page configuration
 * @type Object
 */
window.CRM = {

    /**
     * Utilites
     * @type Object
     */
    utils: {

        /**
         * Display notify
         * @param {Object} config
         * @returns {undefined}
         */
        showNotify: function (config) {

            var options = {

                body: typeof config.body === 'string' ? config.body : '',
                icon: typeof config.icon === 'string' ? config.icon : '',
            };

            var notifyWindow = new Notification(typeof config.title === 'string' ? config.title : '', options);
            notifyWindow.onclick = function (e) {

                console.log(e);
            };

            notifyWindow.onshow = function (e) {

                if (typeof config.sound === 'string') {

                    var audio = new Audio();
                    audio.src = config.sound;
                    audio.autoplay = true;

                }
            };

        },
        
        /**
         * Show alert message
         * @param {Object} data
         * @returns {Boolean}
         */
        showAlert: function (data) {

            if (typeof data.error === 'string') {

                alert(data.error);
                return true;
            }

            return false;
        }

    },

    /**
     * Page configuration
     * @type Object
     */
    config: {}

}

