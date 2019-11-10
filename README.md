# Course plugin 
## Plugin file - [plugin-file].php
1. Create header file using [header requirements](https://developer.wordpress.org/plugins/the-basics/header-requirements/)
2. Create admin pages [administration screens](https://wordpress.org/support/article/administration-screens/)
3. Create admin menu using:
    * [add_menu_page](https://developer.wordpress.org/reference/functions/add_menu_page/) 
    * [Dashicons](http://calebserna.com/dashicons-cheatsheet/) 
    * [add_submenu_page](https://developer.wordpress.org/reference/functions/add_submenu_page/).
4. Create hook for the admin menu 
    [add_action](https://developer.wordpress.org/reference/functions/add_action/)
5. Activate plugin

## Custom Post Types - [type_slug].php
1. Install Custom Post Type UI
2. Create new post type:
    * Type slug
    * Plural Label
    * Singular Label
    * Public - False
    * Has Archive - False
    * Exclude From Search - True
    * With Front - False
3. Create cpt/[type_slug].php file
4. Export the created cpt from CPT UI under Tools -> Get Code
5. Add it to the file cpt/[type_slug].php
6. Include this file under [plugin-file].php using **include_once**
7. Remove custom post type from menu by setting **show_in_menu** to **false** in the [type_slug].php file
8. Rename the function [plugin_short]_register_[type_slug]

## Create custom database tables
1. Create method in your [plugin-file].php [plugin_short]_create_plugin_tables:
    * Use global $wpdb [WPDB](https://codex.wordpress.org/Class_Reference/wpdb)
    * Create query
    * Use [dbDelta](https://developer.wordpress.org/reference/functions/dbdelta/) to execute the error
2. Create a method for plugin activation
3. [Register activation hook](https://developer.wordpress.org/reference/functions/register_activation_hook/) for plugin activation method

## Shortcodes
[Shortcodes support page](https://en.support.wordpress.com/shortcodes/)

1. Create custom shortcode by registering shortcode function with [add_shortcode](https://codex.wordpress.org
 /add_shortcode)

2. Create function that will replace the shortcode

3. Register hooks for shortcode using [init](https://developer.wordpress.org/reference/hooks/init/)
## Add JS and CSS

Create functions that will:
 1. Register the scripts and styles

    [Register scripts](https://developer.wordpress.org/reference/functions/wp_register_script/)
    
    [Register styles](https://developer.wordpress.org/reference/functions/wp_register_style/)
2. Queue the scripts and styles

    [Enqueue scripts](https://developer.wordpress.org/reference/functions/wp_enqueue_script/)
    
    [Enqueue styles](https://developer.wordpress.org/reference/functions/wp_enqueue_style/)

3. Create hooks

    [Admin enqueue scripts hook](https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/)
    
    [Enqueue scripts hook](https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/)

## ISSUES
1. Forgot password -> create md5 password and copy it to replace it in database
2. Can't update -> check rights if the files are for correct user
3. Can't connect to database:
    * Grant privileges 
       ```
       mysql > grant all privileges on . to 'root'@'IP' identified by 'root_password'; mysql> flush privileges;
       ```
    * For IDE database connection: add UTC, when you get timezone error
    * Test remote connection:
      ```
      mysql -u <username> -h <ip_address_of_the_machine_having_mysql_server> -p <port_number> -p
      ```
4. Does not send mail -> logout and try to send mail, it will give mail() error
      * sudo apt-get install sendmail
      * sudo sendmailconfig - choose "Yes"
      * sudo service apache2 restart