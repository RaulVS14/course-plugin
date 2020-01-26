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
        let $survey = $form.closest('.ssp-survey');

        // get selected radio button
        let $selected = $('input[name^="cp_question_"]:checked', $form);

        // split field name into array
        let name_arr = $selected.attr('name').split('_');

        // get survey id from the last item in name array
        let survey_id = name_arr[2];

        // get the response id from the value of the selected item
        let response_id = $selected.val();

        // get the closest dl.ssp-question element
        let $dl = $selected.closest('dl.cp-question');

        $.ajax({
            cache: false,
            method: 'post',
            url: wpajax_url + '?action=cp_ajax_save_response',
            dataType: 'json',
            data: {
                survey_id,
                response_id
            },
            success: (response) => {
                cp_debug(response);
                alert(response.message);
            },
            error: (jqXHR, textStatus, errorThrown) => {
                cp_debug('error', jqXHR);
                cp_debug('textStatus', textStatus);
                cp_debug('errorThrown', errorThrown);
            }


        })
    })
});