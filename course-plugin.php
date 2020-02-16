<?php

/**
 * Plugin Name:       Course Plugin
 * Plugin URI:        http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description:       Handle the basics with this plugin.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            RawlM
 * Author URI:        https://author.example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       course-plugin
 * Domain Path:       /languages
 */

/* !0. TABLE OF CONTENTS */

/*
 *
 * 1. HOOKS
 *      1.1 - admin menus and pages
 *      1.2 - plugin activation
 *      1.3 - shortcodes
 *      1.4 - load external scripts
 *      1.5 - register ajax functions
 *
 * 2. SHORTCODES
 *      2.1 - cp_register_shortcodes()
 *      2.2 - cp_survey_shortcode()
 *
 * 3. FILTERS
 *      3.1 - cp_admin_menus()
 *
 * 4. EXTERNAL SCRIPTS
 *      4.1 - cp_admin_scripts()
 *      4.2 - cp_public_scripts()
 *
 * 5. ACTIONS
 *      5.1 - cp_create_plugin_tables()
 *      5.2 - cp_activate_plugin()
 *      5.3 - cp_ajax_save_response()
 *      5.4 - cp_save_response()
 *
 * 6. HELPERS
 *      6.1 - cp_get_question_html()
 *      6.2 - cp_question_is_answered()
 *      6.3 - cp_return_json()
 *      6.4 - cp_get_client_ip()
 *      6.5 - cp_get_response_stats()
 *      6.6 - cp_get_item_responses()
 *      6.7 - cp_get_survey_responses()
 *
 * 7. CUSTOM POST TYPES
 *      7.1 - cp_survey
 *
 * 8. ADMIN PAGES
 *      8.1 - cp_welcome_page()
 *      8.2 - cp_stats_page()
 *
 * 9. SETTINGS
 *
 * 10. MISCELLANEOUS
 *      10.1 - cp_debug()
 *
 */

/* !1. HOOKS */

// 1.1
// hint: register custom admin menus and pages
add_action('admin_menu', 'cp_admin_menus');

// 1.2
// hint: plugin activation
register_activation_hook(__FILE__, 'cp_activate_plugin');

// 1.3
// hint: register shortcodes
add_action('init', 'cp_register_shortcodes');

// 1.4
// hint: load external scripts
add_action('admin_enqueue_scripts', 'cp_admin_scripts');
add_action('wp_enqueue_scripts', 'cp_public_scripts');

// 1.5
// hint: register ajax functions
add_action('wp_ajax_cp_ajax_save_response', 'cp_ajax_save_response'); // admin user
add_action('wp_ajax_nopriv_cp_ajax_save_response', 'cp_ajax_save_response'); // website user

/* !2. SHORTCODES */

// 2.1
// hint: registers custom shortcodes for this plugin
function cp_register_shortcodes()
{
    // hint: [cp_survey id="123"]
    add_shortcode('cp_survey', 'cp_survey_shortcode');
}

// 2.2
// hint: display a survey
function cp_survey_shortcode($args, $content = "")
{
    // setup our return variable
    $output = "";
    try {
        // begin building our output html
        $output = '<div class="cp cp-survey">';

        // get the survey id
        $survey_id = (isset($args['id'])) ? (int)$args['id'] : 0;

        // get the survey object
        $survey = get_post($survey_id);

        // IF the survey is not valid cp_survey post, return a message
        if (!$survey_id || $survey->post_type !== 'cp_survey'):
            $output .= '<p>The requested survey does not exist.</p>';
        else:

            // build form html
            $form = '';
            if (strlen($content)) {
                $form = '<div class="cp-survey-content">'
                    . wpautop($content)
                    . '</div>';
            }
            $submit_button = '';

            $responses = cp_get_survey_responses($survey_id);

            // cp_survey_is_complete
            if (!cp_question_is_answered($survey_id)) {

                $submit_button = '<div class="cp-survey-footer">
                                    <p><em>Submit your response to see the results of all ' . $responses . ' participants surveyed.</em></p>
                                    <p class="cp-input-container cp-submit">
                                        <input type="submit" name="cp_submit" value="Submit Your Response"/>                                    
                                    </p>
                                  </div>';
            }

            $nonce = wp_nonce_field('cp-save-survey-submission_' . $survey_id, '_wpnonce', true, false);

            $form .= '<form id="survey_' . $survey_id . '" class="cp-survey-form">'
                . $nonce . ''
                . cp_get_question_html($survey_id) . $submit_button
                . '</form>';
            $output .= $form;
        endif;
        $output .= '</div>';

    } catch (Exception $e) {
        // php error
    }
    return $output;
}

/* !3. FILTERS */

// 3.1
// hind: register custom plugin admin menus
function cp_admin_menus()
{
    /* main menu */

    $top_menu_item = 'cp_welcome_page';

    add_menu_page(
        '',
        'Course plugin',
        'manage_options',
        $top_menu_item,
        $top_menu_item,
        'dashicons-chart-bar'
    );

    /* submenu items */

    // welcome
    add_submenu_page(
        $top_menu_item,
        '',
        'Welcome',
        'manage_options',
        $top_menu_item,
        $top_menu_item
    );

    // surveys
    add_submenu_page(
        $top_menu_item,
        '',
        'Surveys',
        'manage_options',
        'edit.php?post_type=cp_survey'
    );

    // stats
    add_submenu_page(
        $top_menu_item,
        '',
        'Stats',
        'manage_options',
        'cp_stats_page',
        'cp_stats_page'
    );
}


/* !4. EXTERNAL SCRIPTS */

// 4.1
// hint: loads external files into wordpress ADMIN
function cp_admin_scripts()
{
    // register scripts with WordPress's internal library
    wp_register_script('cp-js-private', plugins_url('/js/private/cp.js', __FILE__), array('jquery'), '', true);

    // add to queue of scripts that get loaded into every admin page
    wp_enqueue_script('cp-js-private');
}

// 4.2
// hint: loads external files into PUBLIC WEBSITE
function cp_public_scripts()
{
    // register scripts with WordPress's internal library
    wp_register_script('cp-js-public', plugins_url('/js/public/cp.js', __FILE__), array('jquery'), '', true);
    wp_register_style('cp-css-public', plugins_url('/css/public/cp.css', __FILE__));

    // add to queue of scripts that get loaded into every public page
    wp_enqueue_script('cp-js-public');
    wp_enqueue_style('cp-css-public');
}

/* !5. ACTIONS */

// 5.1
// hint: installs custom plugin database tables
function cp_create_plugin_tables()
{
    global $wpdb;

    // setup return value
    $return_value = false;

    try {
        // get the appropriate charset for your database
        $charset_collate = $wpdb->get_charset_collate();

        // $wpdb->prefix returns the custom database prefix
        // originally setup in your wp-config.php

        // sql for our custom table creation
        $sql = "CREATE TABLE {$wpdb->prefix}cp_survey_responses (
                id mediumint(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                ip_address varchar(32) NOT NULL,
                survey_id mediumint(11) UNSIGNED NOT NULL,
                response_id mediumint(11) UNSIGNED NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE INDEX ix (ip_address,survey_id)) $charset_collate;";

        // make sure we include wordpress functions for dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // dbDelta will create a new table if none exists or update an existing one
        dbDelta($sql);
        // return true
        $return_value = true;
    } catch (Exception $e) {
        // php error
        echo $e;
    }
    return $return_value;

}

// 5.2
// hint: runs functions for plugin activation
function cp_activate_plugin()
{
    // create/update custom plugin tables
    cp_create_plugin_tables();
}

// 5.3
// hint: ajax form handler for saving question responses
// expects: $_POST['survey_id'] and $_POST['response_id']
function cp_ajax_save_response()
{
    $result = array(
        'status'          => 0,
        'message'         => 'Could not save response',
        'survey_complete' => false
    );
    try {
        $survey_id = (isset($_POST['survey_id'])) ? (int)$_POST['survey_id'] : 0;
        $response_id = (isset($_POST['response_id'])) ? (int)$_POST['response_id'] : 0;

        // verify nonce
        if (!check_ajax_referer('cp-save-survey-submission_' . $survey_id, false, false)) {
            $result['message'] .= ' Nonce invalid';
        } else {
            $saved = cp_save_response($survey_id, $response_id);
            if ($saved) {

                $survey = get_post($survey_id);
                if (isset($survey->post_type) && $survey->post_type == 'cp_survey') {

                    $complete = true;
                    $html = cp_get_question_html($survey_id);

                    $result = array(
                        'status'          => 1,
                        'message'         => 'Response saved!',
                        'survey_complete' => $complete,
                        'html'            => $html
                    );
                } else{
                    $result['message'] .= ' Invalid survey.';
                }
            }
        }
    } catch (Exception $exception) {
        // php error
    }
    cp_return_json($result);
}

// 5.4
// hint: saves single question response
function cp_save_response($survey_id, $response_id)
{
    global $wpdb;
    $return_value = false;
    try {
        $ip_address = cp_get_client_ip();
        $survey = get_post($survey_id);
        if ($survey->post_type == 'cp_survey') {
            // get current timestamp
            $now = new DateTime();
            $ts = $now->format('Y-m-d H:i:s');

            // query sql
            $sql = "
                INSERT INTO {$wpdb->prefix}cp_survey_responses(ip_address, survey_id, response_id, created_at)
                VALUES(%s,%d,%d,%s)
                ON DUPLICATE KEY UPDATE survey_id = %d
            ";

            // prepare query
            $sql = $wpdb->prepare($sql, $ip_address, $survey_id, $response_id, $ts, $survey_id);

            // run query
            $entry_id = $wpdb->query($sql);

            // IF response save successfully ...
            if ($entry_id) {
                $return_value = true;
            }
        }
    } catch (Exception $e) {
        cp_debug('cp_save_response php errpr', $e->getMessage());
    }
    return $return_value;
}


/* !6. HELPERS */

// 6.1
// hint: returns html for survey question
function cp_get_question_html($survey_id, $force_results = false)
{
    $html = '';

    // get the survey post object
    $survey = get_post($survey_id);

    // IF $survey is a valid cp_survey post type ...
    if ($survey->post_type == 'cp_survey'):
        $question_text = $survey->post_content;
        $question_opts = array(
            'Strongly Agree'    => 5,
            'Somewhat Agree'    => 4,
            'Neutral'           => 3,
            'Somewhat Disagree' => 2,
            'Strongly Disagree' => 1
        );

        // check if the current user has already answered this survey question
        // or is force_Results is true, treat as answered
        $answered = ($force_results) ? true : cp_question_is_answered($survey_id);

        // default complete class is blank
        $complete_class = '';
        // setup out inputs html
        $inputs = '<ul class="cp-question-options">';
        if (!$answered):

            foreach ($question_opts as $key => $value) :
                $inputs .= '<li><label><input type="radio" name="cp_question_' . $survey_id . '" value="' . $value . '"/>' . $key . '</label></li>';
            endforeach;
        else:
            // survey is complete, add a real complete class
            $complete_class = ' cp_question_complete';
            $inputs = ' Thank you for completing our survey.';
            foreach ($question_opts as $key => $value) {
                $stats = cp_get_response_stats($survey_id, $value);

                // append input html for each option
                $inputs .= '<li><label>' . $key . ' - ' . $stats['percentage'] . '</label></li>';
            }

        endif;
        $inputs .= '</ul>';
        $html .= '
            <dl id="cp_' . $survey_id . '_question" class="cp_question ' . $complete_class . '">
                <dt>' . $question_text . '</dt>
                <dd>' . $inputs . '</dd>
            </dl>';

    endif;

    return $html;
}

// 6.2
// hint: returns true or false depending on
// whether or not the current user has answered the survey
function cp_question_is_answered($survey_id)
{
    global $wpdb;

    // setup default return value
    $return_value = false;

    try {

        // get user ip address
        $ip_address = cp_get_client_ip();

        // cp_debug('ip_address', $ip_address);

        // sql to check if this user has completed the survey

        $sql = "
            SELECT response_id FROM {$wpdb->prefix}cp_survey_responses
            WHERE survey_id = %d AND ip_address = %s
        ";

        // prepare query
        $sql = $wpdb->prepare($sql, $survey_id, $ip_address);

        // run query, returns entry id if successful
        $entry_id = $wpdb->get_var($sql);

        // IF query worked and entry_id is not null ...
        if ($entry_id !== NULL) {
            // set our return value to the entry_id
            $return_value = $entry_id;
        }
    } catch (Exception $e) {
        // php error
    }

    // return result
    return $return_value;
}

// 6.3
// hint: returns json string and exits php processes
function cp_return_json($php_array)
{

    // encode result json string
    $json_result = json_encode($php_array);

    // return result
    wp_die($json_result);
}

// 6.4
// hint: makes it's best attempt to get the ip address of the current user
function cp_get_client_ip()
{
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ipaddress = getenv('HTTP_FORWARDED');
    } elseif (getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

// 6.5
// hint: gets the statistics for a survey response
function cp_get_response_stats($survey_id, $response_id)
{
    // setup default return variable
    $stats = array(
        'percentage' => '0%',
        'votes'      => 0
    );

    try {
        // get responses for this item
        $item_responses = cp_get_item_responses($survey_id, $response_id);

        // get total responses for this survey
        $survey_responses = cp_get_survey_responses($survey_id);

        if ($survey_responses && $item_responses) {
            $stats = array(
                'percentage' => ceil(($item_responses / $survey_responses) * 100) . '%',
                'votes'      => $item_responses
            );
        }
    } catch (Exception $e) {

        // php error
        cp_debug('cp_get_response_stats exception', $e);
    }

    // return stats
    return $stats;
}

// 6.6
function cp_get_item_responses($survey_id, $response_id)
{
    global $wpdb;
    $item_responses = 0;
    try {
        // sql to check if this user has completed the survey
        $sql = "
            SELECT count(id) AS total FROM {$wpdb->prefix}cp_survey_responses
            WHERE survey_id = %d AND response_id = %d
        ";

        // prepare query
        $sql = $wpdb->prepare($sql, $survey_id, $response_id);

        // run query, returns total item responses
        $item_responses = (int)$wpdb->get_var($sql);
    } catch (Exception $e) {
        cp_debug('cp_get_item_responses php error', $e->getMessage());
    }
    return $item_responses;
}

// 6.7
function cp_get_survey_responses($survey_id)
{
    global $wpdb;

    $survey_responses = 0;

    try {
        // sql to check if this user has completed the survey
        $sql = "
            SELECT count(id) AS total FROM {$wpdb->prefix}cp_survey_responses
            WHERE survey_id = %d
        ";

        // prepare query
        $sql = $wpdb->prepare($sql, $survey_id);

        // run query, returns total survey responses
        $survey_responses = (int)$wpdb->get_var($sql);
    } catch (Exception $e) {
        // php error
        cp_debug('cp_get_survey_responses php error', $e->getMessage());
    }

    return $survey_responses;
}

/* !7. CUSTOM POST TYPES */
// 7.1
// cp_survey
include_once(plugin_dir_path(__FILE__) . 'cpt/cp_survey.php');

/* !8. ADMIN PAGES */

// 8.1
// hint: this page explains what the plugin is about
function cp_welcome_page()
{
    $output = '
        <div class="wrap cp-welcome-admin-page">
            <h2>Course plugin</h2>
            <p>Just to learn a new skill</p>
            <p> 2 peeps involved</p>
        </div>
    ';

    echo $output;
}

function cp_stats_page()
{
    $output = '
        <div class="wrap cp-stats-admin-page">
            <h2>Plugin Statistics</h2>
            <p>
                <label for="survey-select">Select a survey</label>
                <select name="survey" id="survey-select">
                    <option value=""> - Select One - </option>
                </select>
            </p>
        </div>
    ';

    echo $output;
}

/* !9. SETTINGS */
/* !10. MISCELLANEOUS */

// 10.1
// hint: writes an output to the browser and runs kills php process
function cp_debug($msg = '', $data = false, $die = true)
{
    echo '<pre>';

    if (strlen($msg)) {
        echo $msg . '<br/>';
    }

    if ($data !== false) {
        var_dump($data);
    }

    echo '<pre/>';

    if ($die) wp_die();
}


