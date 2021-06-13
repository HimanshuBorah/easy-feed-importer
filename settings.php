<?php


add_action( 'admin_menu', function() {
    add_submenu_page(
        'easy-importfeed',
        __( 'Settings', 'my-textdomain' ),
        __( 'Settings', 'my-textdomain' ),
        'manage_options',
        'efi-settings',
        'hbel_efi_settings_content',
    );
} );

function hbel_efi_settings_content() {
    ?>
    <form method="POST" action="options.php">
    <?php
    settings_fields( 'efi-settings' );
    do_settings_sections( 'efi-settings' );
    submit_button();
    ?>
    </form>
    <?php
}


add_action( 'admin_init', 'my_settings_init' );

function my_settings_init() {

    add_settings_section(
        'efi_settings_section',
        __( 'Easy Feed Importer Settings', 'my-textdomain' ),
        'efi_settings_section_callback_function',
        'efi-settings'
    );

		add_settings_field(
		   'efi_price_increase_by',
		   __( 'Price Increase by', 'my-textdomain' ),
		   'efi_setting_markup',
		   'efi-settings',
		   'efi_settings_section'
		);

		register_setting( 'efi-settings', 'efi_price_increase_by' );
}


function efi_settings_section_callback_function() {
    echo '<p>Additional settings for Easy Feed Import</p>';
}


function efi_setting_markup() {
    ?>
    <label for="my-input"><?php _e( 'In Percentage' ); ?></label>
    <input type="text" id="efi_price_increase_by" name="efi_price_increase_by" value="<?php echo get_option( 'efi_price_increase_by' ); ?>">
    <?php
}