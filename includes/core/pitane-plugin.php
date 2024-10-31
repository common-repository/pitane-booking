<?php
if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly.
    
}

/**
 * Free Resources Shortcode.
 *
 */
class PitanePlugin
{
    function __construct()
    {

        // action for shortcode
        add_shortcode('pitane_plugin', array(
            $this,
            'pitane_booking_function'
        ));

    }

    /**
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access public
     */
    function pitane_booking_function()
    {

        // Turn on output buffering
        ob_start();
        
        if (!session_id())
        {
            session_start();
        }
        
        include WP_PITANE_PLUGIN_DIR . 'includes/core/html/form.php';

        // return buffering output
        return ob_get_clean();
    }
}
new PitanePlugin();

