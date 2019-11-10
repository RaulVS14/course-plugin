jQuery(document).ready(function ($) {
    // do something after jQuery has loaded
    cp_debug('private js script loaded!');

    function  cp_debug(msg, data) {
        try {
            console.log(msg);
            if (typeof data !== "undefined") {
                console.log(data);
            }
        } catch (e) {

        }

    }
});