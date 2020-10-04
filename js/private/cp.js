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

    // setup our WP AJAX URL
    var wpajax_url = document.location.protocol + '//' + document.location.host + '/wordpress/wp-admin/admin-ajax.php';

    // dynamically load in stats when survey select html form element is changed
    $(document).on('change', '.cp-stats-admin-page [name="cp_survey"]', function (e) {
        let survey_id = $('option:selected', this).val();
        cp_debug('selected survey', survey_id);
        $stats_div = $('.cp-survey-stats', '.cp-stats-admin-page');

        $.ajax({
            cache: false,
            method: 'post',
            url: wpajax_url + '?action=cp_ajax_get_stats_html',
            dataType: 'json',
            data: {
                survey_id
            },
            success: function (response) {
                // return response in console for debugging
                cp_debug(response);

                // IF submission was successful
                if (response.status) {
                    // update the stats_div html
                    $stats_div.replaceWith(response.html);
                } else{
                    // IF submission was unsuccessful
                    // notify user
                    alert(response.message);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // output error information for debugging
                cp_debug('error', jqXHR);
                cp_debug('textStatus', textStatus);
                cp_debug('errorThrown', errorThrown);
            }
        })
    });
});