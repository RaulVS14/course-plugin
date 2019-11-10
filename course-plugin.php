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
 *
 * 6. HELPERS
 *      6.1 - cp_get_question_html()
 *      6.2 - cp_question_is_answered()
 *
 * 7. CUSTOM POST TYPES
 *      7.1 - cp_survey
 *
 * 8. ADMIN PAGES
 *      8.1 - cp_welcome_page()
 *      8.2 - cp_stats_page()
 *
 * 9. SETTINGS
 *admin
 * 10. MISCELLANEOUS
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
            if (strlen($content)):
                $form = '<div class="cp-survey-content">'
                    . wpautop($content)
                    . '</div>';
            endif;
            $submit_button = '';
            if (!cp_question_is_answered($survey_id)):
                $submit_button = '<div class="cp-survey-footer">
                                    <p class="cp-input-container cp-submit">
                                        <input type="submit" name="cp_submit" value="Submit Your Response"/>                                    
                                    </p>
                                  </div>';
            endif;
            $form .= '<form id="survey_' . $survey_id . '" class="cp-survey-form">'
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
    wp_register_script('cp-js-private', plugins_url('/js/private/cp.js',__FILE__), array('jquery'), '', true);

    // add to queue of scripts that get loaded into every admin page
    wp_enqueue_script('cp-js-private');
}

// 4.2
// hint: loads external files into PUBLIC WEBSITE
function cp_public_scripts()
{
    // register scripts with WordPress's internal library
    wp_register_script('cp-js-public', plugins_url('/js/public/cp.js',__FILE__), array('jquery'), '', true);
    wp_register_style('cp-css-public', plugins_url('/css/public/cp.css',__FILE__));

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

/* !6. HELPERS */

// 6.1
// hint: returns html for survey question
function cp_get_question_html($survey_id)
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
        $answered = cp_question_is_answered($survey_id);

        // default complete class is blank
        $complete_class = '';
        if (!$answered):
            // setup out inputs html
            $inputs = '<ul class="cp-question-options">';

            foreach ($question_opts as $key => $value) :
                $inputs .= '<li><label><input type="radio" name="cp_question_' . $survey_id . '" value="' . $value . '"/>' . $key . '</label></li>';
            endforeach;
            $inputs .= '</ul>';
        else:
            // survey is complete, add a real complete class
            $complete_class = ' cp_question_complete';
            $inputs = ' Thank you for completing our survey.';
        endif;

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
    // setup default return value
    $return_value = false;

    // return result
    return $return_value;
}

/* !7. CUSTOM POST TYPES */
// 7.1
// ssp_survey
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

/* !10. MISCELLANEOUS */


