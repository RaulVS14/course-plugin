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
 *
 * 2. SHORTCODES
 *
 * 3. FILTERS
 *      3.1 - cp_admin_menus()
 *
 * 4. EXTERNAL SCRIPTS
 *
 * 5. ACTIONS
 *
 * 6. HELPERS
 *
 * 7. CUSTOM POST TYPES
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
add_action('admin_menu','cp_admin_menus');


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


/* !6. HELPERS */


/* !7. CUSTOM POST TYPES */


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


