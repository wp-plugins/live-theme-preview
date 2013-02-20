<?php
/**************************************************************************
Plugin Name: Live Theme Preview
Plugin URI: https://github.com/mgmartel/WP-Live-Theme-Preview
Description: Live Theme Preview allows users to preview themes on their website before customizing or activating them.
Version: 1.0.2
Author: Mike_Cowobo
Author URI: http://trenvo.com

**************************************************************************/

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit(-1);

/**
 * Version number
 *
 * @since 0.1
 */
define ( 'WP_LTP_VERSION', '1.0.2' );

/**
 * PATHs and URLs
 *
 * @since 0.1
 */
define( 'WP_LTP_DIR', plugin_dir_path(__FILE__) );
define( 'WP_LTP_URL', plugin_dir_url(__FILE__) );
define( 'WP_LTP_INC_URL', WP_LTP_URL . '_inc/' );

if (!class_exists('WP_LiveThemePreview')) :

    class WP_LiveThemePreview    {

        public $settings;

        /**
         * Creates an instance of the WP_LiveThemePreview class
         *
         * @return WP_LiveThemePreview object
         * @since 0.1
         * @static
         */
        public static function &init() {
            static $instance = false;

            if (!$instance) {
                load_plugin_textdomain('live-theme-preview', false, basename ( WP_LTP_DIR ) . '/languages/');
                $instance = new WP_LiveThemePreview;
            }

            return $instance;
        }

        /**
         * Constructor
         *
         * @since 0.1
         */
        public function __construct() {
            /**
             * Requires and includes
             */
            require_once ( WP_LTP_DIR . 'lib/live-admin/live-admin.php' );
            $this->settings = new WP_LiveAdmin_Settings(
                    'themes',
                    __('Live Theme Preview', 'live-theme-preview'),
                    __('Use the Live Theme Preview as the default theme chooser (adds an extra menu item for the default theme chooser)','live-theme-preview'),
                    'true'
                    );

            $this->actions_and_filters();

            if ( $this->settings->is_activated() )
                add_action ('admin_init', array ( &$this, 'live' ) );

        }

            /**
             * PHP4
             *
             * @since 0.1
             */
            public function WP_LiveThemePreview() {
                $this->__construct();
            }

        /**
         * Show the live theme preview!
         *
         * @since 0.1
         */
        public function live() {
            $this->maybe_activate();
            $this->display();
            exit;
        }

        /**
         * Are we activating a theme?
         *
         * @since 0.1
         */
        protected function maybe_activate() {
            if( isset ( $_GET['action'] ) && $_GET['action'] == 'activate' && check_admin_referer( 'live-theme-preview_' . $_GET['stylesheet'] ) ) {
                if ( version_compare( $GLOBALS['wp_version'], 3.5, '>=' ) )
                    switch_theme( $_GET['stylesheet'] );
                else switch_theme( $_GET['template'], $_GET['stylesheet'] );
            }
        }

        /**
         * Load the various actions and filters
         *
         * @since 0.1
         * @todo Make the admin menu modification optional
         */
        private function actions_and_filters() {
            // Make sure theme options of the previewed theme are loaded when available
            if ( isset ( $_REQUEST['preview'] ) && true == $_REQUEST['preview'] )
                add_filter( 'pre_option_theme_mods_' . get_option( 'stylesheet' ), array ( &$this, 'return_theme_options' ) );

            if ( $this->settings->is_default() ) {
                // Set Live Preview as the default theme selector in the WP admin menus
                add_action('admin_menu', array ( &$this, 'set_as_theme_chooser' ) );

                // and as the default return for the Theme Customizer if the theme is not active
                add_action('customize_controls_init', array ( &$this, 'modify_redirect' ) );
            } else {
                // Set Live Preview as the default theme selector in the WP admin menus
                add_action('admin_menu', array ( &$this, 'add_menu_item' ) );
            }
        }

        /**
         * Load the template
         *
         * @since 0.1
         */
        protected function display() {
            require( WP_LTP_DIR . '/live-theme-preview-template.php' );
        }

        /**
         * Sets theme options not to be from the options table, but from the requested stylesheet
         *
         * @return array
         */
        public function return_theme_options( $i ) {
            if ( $_GET['stylesheet'] != get_option( 'stylesheet' ) )
                return get_option ( 'theme_mods_' . $_GET['stylesheet'] );
            else return false;
        }

        /**
         * Sets LTP as the default option for themes in wp-admin
         *
         * @global array $submenu
         */
        public function set_as_theme_chooser() {
            global $submenu;

            $submenu['themes.php'][5][2] .= "?live_themes=1";
            add_submenu_page('themes.php','', 'Manage Themes', 'switch_themes', 'themes.php');
        }

        /**
         * Add LTP menu item
         *
         * @global array $submenu
         */
        public function add_menu_item() {
            add_submenu_page('themes.php','', 'Live Theme Preview', 'switch_themes', 'themes.php?live_themes=1');
        }

        /**
         * Return to LTP after visiting the customizer when the theme is not activated (and make sure we go back to that theme). Leave it alone when the theme is active.
         *
         * @global str $return
         * @global WP_Customize_Manager $wp_customize
         */
        public function modify_redirect() {
            global $return, $wp_customize;
            if ( ! $wp_customize->is_theme_active() )
                $return = admin_url("themes.php?live_themes=1&theme={$wp_customize->get_stylesheet()}");
        }
    }
    //WP_LiveThemePreview::init();
    add_action ( 'init', array ( 'WP_LiveThemePreview', 'init' ) );
endif;