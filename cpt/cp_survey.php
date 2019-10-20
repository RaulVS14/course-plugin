<?php
function cp_register_cp_survey()
{

    /**
     * Post Type: Surveys.
     */

    $labels = array(
        "name"          => __("Surveys", "twentynineteen"),
        "singular_name" => __("Survey", "twentynineteen"),
    );

    $args = array(
        "label"                 => __("Surveys", "twentynineteen"),
        "labels"                => $labels,
        "description"           => "",
        "public"                => false,
        "publicly_queryable"    => true,
        "show_ui"               => true,
        "delete_with_user"      => false,
        "show_in_rest"          => true,
        "rest_base"             => "",
        "rest_controller_class" => "WP_REST_Posts_Controller",
        "has_archive"           => false,
        "show_in_menu"          => false,
        "show_in_nav_menus"     => true,
        "exclude_from_search"   => true,
        "capability_type"       => "post",
        "map_meta_cap"          => true,
        "hierarchical"          => false,
        "rewrite"               => array("slug" => "cp_survey", "with_front" => false),
        "query_var"             => true,
        "supports"              => array("title", "editor"),
    );

    register_post_type("cp_survey", $args);
}

add_action('init', 'cp_register_cp_survey');