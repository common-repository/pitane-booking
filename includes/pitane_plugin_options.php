<?php
// create custom plugin settings menu
add_action('admin_menu', 'pitane_plugin_create_menu');

function pitane_plugin_create_menu()
{
    //create new top-level menu
    add_menu_page(__('Pitane Settings','pitanebooking'), __('Pitane Settings','pitanebooking'), 'administrator', 'pitane-plugin-settings', 'pitane_plugin_settings_page', 'dashicons-admin-settings');

    add_submenu_page('pitane-plugin-settings', __('Color Settings','pitanebooking'), __('Color Settings','pitanebooking'), 'manage_options', 'pitane-color-settings', 'pitane_color_settings');

    //call register settings function
    add_action('admin_init', 'register_pitane_plugin_settings');
}

function register_pitane_plugin_settings()
{
    //register our settings
    register_setting('pitane-plugin-settings-group', 'google_api_key');
    register_setting('pitane-plugin-settings-group', 'pitane_api_url');
    register_setting('pitane-plugin-settings-group', 'pitane_api_port');
    register_setting('pitane-plugin-settings-group', 'pitane_api_key');
    register_setting('pitane-plugin-settings-group', 'tariffT');
    register_setting('pitane-plugin-settings-group', 'tariffB');
    register_setting('pitane-plugin-settings-group', 'tariffR');
    register_setting('pitane-plugin-settings-group', 'rei_vor_id');
    register_setting('pitane-plugin-settings-group', 'rei_id');
    register_setting('pitane-plugin-settings-group', 'filter');
    register_setting('pitane-plugin-settings-group', 'gate12_guid');
    register_setting('pitane-plugin-settings-group', 'companyname');
    register_setting('pitane-plugin-settings-group', 'google_countries', 'pitane_plugin_settings_google_validation');    
    register_setting('pitane-color-settings-group', 'pitane_main_color');
    register_setting('pitane-color-settings-group', 'pitane_button_color');
    register_setting('pitane-color-settings-group', 'pitane_text_main_color');
    register_setting('pitane-color-settings-group', 'pitane_background_color');
    register_setting('pitane-color-settings-group', 'pitane_widget_text_color');
    register_setting('pitane-color-settings-group', 'pitane_success_text_color');
    register_setting('pitane-color-settings-group', 'pitane_error_text_color');
}

function pitane_plugin_settings_google_validation($value) 
{
    $isValid = true;

    try 
    {
        if (!empty($value)) 
        {
            $values =  explode(",", $value);
            for($i = 0; $i < count($values); $i++)
            {
                $currentValue = trim($values[$i]);
                if (strlen($currentValue) != 2)
                {
                    pitanebooking_logToDatabase("Invalid Google Countries values given => '$value', resetting to defaults", "ERROR");
                    $isValid = false;
                    break;
                }
            }
        }
    } 
    catch (Exception $e) 
    {
        pitanebooking_logToDatabase("Invalid Google Countries values given => '$value', resetting to defaults", "ERROR");
        $isValid = false;
    }

    if (empty($value) || !$isValid)
    {
        $value = "NL, BE, DE";
    }   

    return $value;
}

function pitane_plugin_settings_page()
{
?>
<div class="wrap">
<h1><?php esc_attr_e( 'Pitane Settings', 'pitanebooking' ); ?></h1>

<form method="post" action="options.php">
    <?php settings_fields('pitane-plugin-settings-group'); ?>
    <?php do_settings_sections('pitane-plugin-settings-group'); ?>
    <table class="form-table">     
        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Google API key', 'pitanebooking' ); ?></th>
            <td><input type="text" name="google_api_key" value="<?php echo esc_attr(get_option('google_api_key')); ?>" placeholder="YOUR_API_KEY" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Google countries', 'pitanebooking' ); ?> (NL, BE, DE)</th>
            <td><input type="text" name="google_countries" value="<?php echo esc_attr(get_option('google_countries', "NL, BE, DE")); ?>" placeholder="GOOGLE_PLACES_COUNTRIES" class="regular-text" /></td>
        </tr>
        
        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Pitane API url', 'pitanebooking' ); ?></th>
            <td><input type="text" name="pitane_api_url" value="<?php echo esc_attr(get_option('pitane_api_url')); ?>" placeholder="YOUR_API_URL" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Pitane API port', 'pitanebooking' ); ?></th>
            <td><input type="text" name="pitane_api_port" value="<?php echo esc_attr(get_option('pitane_api_port')); ?>" placeholder="YOUR_API_PORT" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Pitane API key', 'pitanebooking' ); ?></th>
            <td><input type="text" name="pitane_api_key" value="<?php echo esc_attr(get_option('pitane_api_key')); ?>" placeholder="YOUR_API_KEY" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Company name', 'pitanebooking' ); ?></th>
            <td><input type="text" name="companyname" value="<?php echo esc_attr(get_option('companyname')); ?>" placeholder="YOUR_COMPANY_NAME" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Tariff Id Taxi', 'pitanebooking' ); ?></th>
            <td><input type="text" name="tariffT" value="<?php echo esc_attr(get_option('tariffT')); ?>" placeholder="YOUR_TARIFF_ID" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Tariff Id Bus', 'pitanebooking' ); ?></th>
            <td><input type="text" name="tariffB" value="<?php echo esc_attr(get_option('tariffB')); ?>" placeholder="YOUR_TARIFF_ID" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Tariff Id Wheelchair Bus', 'pitanebooking' ); ?></th>
            <td><input type="text" name="tariffR" value="<?php echo esc_attr(get_option('tariffR')); ?>" placeholder="YOUR_TARIFF_ID" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Transport id (rei_vor_id)', 'pitanebooking' ); ?></th>
            <td><input type="text" name="rei_vor_id" value="<?php echo esc_attr(get_option('rei_vor_id')); ?>" placeholder="YOUR_TRANSPORT_ID" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Default passenger id (rei_id)', 'pitanebooking' ); ?></th>
            <td><input type="text" name="rei_id" value="<?php echo esc_attr(get_option('rei_id')); ?>" placeholder="YOUR_PASSENGER_ID" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Filter letter (pla_filter)', 'pitanebooking' ); ?></th>
            <td><input type="text" name="filter" value="<?php echo esc_attr(get_option('filter')); ?>" placeholder="YOUR_FILTER" class="regular-text" /></td>
        </tr>

        <tr valign="top">
            <th scope="row"><?php esc_attr_e( 'Gate12 GUID', 'pitanebooking' ); ?></th>
            <td><input type="text" name="gate12_guid" value="<?php echo esc_attr(get_option('gate12_guid')); ?>" placeholder="YOUR_GATE12_GUID" class="regular-text" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php
}

function pitane_color_settings()
{
    // Enqueue Script
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('pitane-admin-shortcode-js');
?>
        <div class="wrap">
            <h1><?php esc_attr_e( 'Color Settings', 'pitanebooking' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields('pitane-color-settings-group'); ?>
                <?php do_settings_sections('pitane-color-settings-group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_attr_e( 'Main color', 'pitanebooking' ); ?></th>
                        <td><input type="text" name="pitane_main_color" value="<?php echo esc_attr(get_option('pitane_main_color')); ?>" class="pitane-color" data-default-color="#002E6E" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_attr_e( 'Text Main color', 'pitanebooking' ); ?></th>
                        <td><input type="text" name="pitane_text_main_color" value="<?php echo esc_attr(get_option('pitane_text_main_color')); ?>" class="pitane-color" data-default-color="#FFFFFF" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_attr_e( 'Background color', 'pitanebooking' ); ?></th>
                        <td><input type="text" name="pitane_background_color" value="<?php echo esc_attr(get_option('pitane_background_color')); ?>" class="pitane-color" data-default-color="#E4E4E4" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_attr_e( 'Button background color', 'pitanebooking' ); ?></th>
                        <td><input type="text" name="pitane_button_color" value="<?php echo esc_attr(get_option('pitane_button_color')); ?>" class="pitane-color" data-default-color="#58C6F2" /></td>
                    </tr>                    
                    <tr valign="top">
                        <th scope="row"><?php esc_attr_e( 'Widget Text color', 'pitanebooking' ); ?></th>
                        <td><input type="text" name="pitane_widget_text_color" value="<?php echo esc_attr(get_option('pitane_widget_text_color')); ?>" class="pitane-color" data-default-color="#000000" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_attr_e( 'Success Text color', 'pitanebooking' ); ?></th>
                        <td><input type="text" name="pitane_success_text_color" value="<?php echo esc_attr(get_option('pitane_success_text_color')); ?>" class="pitane-color" data-default-color="#8ED093"/></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php esc_attr_e( 'Error Text color', 'pitanebooking' ); ?></th>
                        <td><input type="text" name="pitane_error_text_color" value="<?php echo esc_attr(get_option('pitane_error_text_color')); ?>" class="pitane-color" data-default-color="#FF5151" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
}

