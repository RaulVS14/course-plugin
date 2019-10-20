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
 *
 * 2. SHORTCODES
 *
 * 3. FILTERS
 *      3.1 - cp_admin_menus()
 *
 * 4. EXTERNAL SCRIPTS
 *
 * 5. ACTIONS
 *      5.1 - cp_create_plugin_tables()
 *      5.2 - cp_activate_plugin()
 *
 * 6. HELPERS
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
 *
 */

/* !1. HOOKS */

// 1.1
// hint: register custom admin menus and pages
add_action('admin_menu', 'cp_admin_menus');

// 1.2
// hint: plugin activation
register_activation_hook(__FILE__,'cp_activate_plugin');


/* !2. SHORTCODES */


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
        require_once(ABSPATH.'wp-admin/includes/upgrade.php');

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


