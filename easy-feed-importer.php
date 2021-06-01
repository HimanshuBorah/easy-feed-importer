<?php
/**
* Plugin Name: Easy Feed Importer
* Plugin URI: NA
* Description: This plugin imports data from XML feed into Properties
* Version: 1.0
* Author: BoraH
* Author URI: NA
**/

/**
 * 
 *  IMPORTANT : This plugin uses the following plugin 
 * 
 *   https://wordpress.org/plugins/featured-image-from-url/
 * 
 *   Please Install this plugin 
 */

// Import Handler
require_once( "import-handler.php" );


/**
 * Register Import Page.
 */
add_action( 'admin_menu', function (){
    add_menu_page( 
        __( 'Easy Import', 'textdomain' ),
        'Easy Import',
        'manage_options',
        'easyimportfeed',
        'hbel_easy_import_fn',
        'dashicons-xing',
        6
    ); 
} );
 
/**
 * Display a custom menu page
 */
function hbel_easy_import_fn(){
    echo '<h2> Easy Import Feed </h2>';
	echo "<div class='updated'>";
    echo "<p>";
    echo "Insert new Products from your XML Feed, click the button to the right.";
    echo "<a class='button button-primary' style='margin:0.25em 1em' href='{$_SERVER["REQUEST_URI"]}&insert_new_prods'> Run Importer </a>";
    echo "</p>";
    echo "</div>";
	
	
	//  the post creation _only_ happens when you want it to.
	if ( isset( $_GET["insert_new_prods"] ) ) {
		hbdev_run_import();
	} else {
		return;
	}

}

/**
 * 
 *  Set Scheduled
 * 
 */
function hbdev_deactivate() {
    wp_clear_scheduled_hook( 'hbdev_cron' );
}
 
add_action('init', function() {
    add_action( 'hbdev_cron', 'hbdev_run_cron' );
    register_deactivation_hook( __FILE__, 'hbdev_deactivate' );
 
    if (! wp_next_scheduled ( 'hbdev_cron' )) {
        wp_schedule_event( time(), 'hourly', 'hbdev_cron' );
    }
});
 
function hbdev_run_cron() {
    hbdev_run_import();
}