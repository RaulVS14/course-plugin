jQuery(document).ready(function ($) {
    // do something after jQuery has loaded
    cp_debug('public js script loaded!');

    function cp_debug(msg, data) {
        try {
            console.log(msg);
            if (typeof data !== "undefined") {
                console.log(data);
            }
        } catch (e) {

        }

    }

    // setup our wp ajax url
    let wpajax_url = document.location.protocol + '//' + document.location.host + '/personal/wordpress/wp-admin/admin-ajax.php';

    // bind custom function to survey form submit event
    $(document).on('submit', '.cp-survey-form', function (e) {
        // prevent form form submitting normally
        e.preventDefault();
        let $form = $(this);
        let $survey = $form.closest('.cp-survey');

        // get selected radio button
        let $selected = $('input[name^="cp_question_"]:checked', $form);
        // split field name into array
        let name_arr = $selected.attr('name').split('_');

        // get survey id from the last item in name array
        let survey_id = name_arr[2];

        // get the response id from the value of the selected item
        let response_id = $selected.val();

        let data = {
            _wpnonce: $('[name="_wpnonce"]',$form).val(),
            _wp_http_referer: $('[name="_wp_http_referer"]',$form).val(),
            survey_id,
            response_id
        };

        cp_debug('data', data);

        // get the closest dl.ssp-question element
        let $dl = $selected.closest('dl.cp_question');

        $.ajax({
            cache: false,
            method: 'post',
            url: wpajax_url + '?action=cp_ajax_save_response',
            dataType: 'json',
            data,
            success: (response) => {
                cp_debug(response);
                if(response.status){
                    // update html of the current li
                    console.log($dl);
                    $dl.replaceWith(response.html);

                    // hide survey content message
                    $('.cp-survey-footer', $survey).hide();
                } else {
                    // notify user
                    alert(response.message);
                }
            },
            error: (jqXHR, textStatus, errorThrown) => {
                cp_debug('error', jqXHR);
                cp_debug('textStatus', textStatus);
                cp_debug('errorThrown', errorThrown);
            }


        })
    })
});