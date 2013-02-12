<?php
// Exit if accessed directly
if ( !defined ( 'ABSPATH' ) )
    exit;

require_once ( WP_LTP_DIR . 'lib/live-admin/live-admin.php' );

if ( ! class_exists ( 'WP_LiveThemePreview_Template' ) ) :

    /**
     * The template
     */
    class WP_LiveThemePreview_Template extends WP_LiveAdmin
    {
        protected $active_theme;

        public function __construct() {
            do_action ( 'wp_ltp_init' );

            $this->menu = true;
            $this->capability = 'switch_themes';

            $this->active_theme = wp_get_theme()->get_stylesheet();

            $theme = ( isset ( $_GET['theme'] ) && ! empty ( $_GET['theme'] ) ) ? $_GET['theme'] : $this->active_theme;
            $template = wp_get_theme( $theme )->template;

            $this->custom_js_vars = apply_filters ( 'wp_ltp_js_vars', array (
                "blog_url"                 => get_bloginfo('url'),
                "previewed_theme"          => $theme,
                "previewed_theme_template" => $template,
            ) );
            $this->override_iframe_loader = true;

            $this->add_button ( $this->close_button(), 10 );

            $this->enqueue_styles_and_scripts();

        }

            public function wp_livethemepreview_template() {
                $this->_construct();
            }

        /**
         * Enqueue scripts and styles
         *
         * @since 1.0
         */
        public function enqueue_styles_and_scripts() {
            wp_enqueue_style("live-theme-preview", WP_LTP_INC_URL . 'css/live-theme-preview.css', array ("customize-controls"), "0.1" );
            wp_enqueue_script("live-theme-preview", WP_LTP_INC_URL . 'js/live-theme-preview.js', array ("jquery"), "0.1" );
        }

        public function do_start() {
            global $title, $parent_file, $submenu_file, $handle;

            // Globals
            $title = __('Manage Themes');
            $parent_file = 'themes.php';
            $submenu_file = 'themes.php?live_themes=1';

            $handle = 'themes.php';

        }

        public function do_controls() {
            $available_themes  = wp_get_themes( array('allowed' => true) );

            $theme = $available_themes[$this->active_theme];
            unset ( $available_themes[$this->active_theme] );

            $this->the_theme_button ( $theme, true );

            foreach ($available_themes as $tname=>$theme)
            {
                $this->the_theme_button ( $theme );
            }
        }

        /**
         * Renders a theme button in the sidebar
         *
         * @param str $theme
         * @param bool $active
         * @since 0.1
         */
        protected function the_theme_button ( $theme, $active = false ) {
            $screenshot =  $theme['Screenshot'];
            $template = $theme['Template'];
            $stylesheet = $theme['Stylesheet'];

            $previewed_theme = trailingslashit ( get_theme_root_uri( $stylesheet ) ) . $stylesheet . '/' . $screenshot;

            $selected_theme = '';

            if( $active )
                $selected_theme = ' selected_theme';

            $activateurl = wp_nonce_url ( apply_filters ( 'wp_ltp_activateurl', admin_url ( "themes.php/?live_themes=1&action=activate&template=" . urlencode( $template ) . "&stylesheet=" . urlencode ( $stylesheet ) ) ), "live-theme-preview_$stylesheet" );

            $editurl = apply_filters ( 'wp_ltp_editurl', wp_customize_url( $stylesheet ) );

            ?>

            <div class="thumbnail<?php echo $selected_theme; ?>" id="<?php echo $stylesheet ?>" style="background-image: url('<?php echo $previewed_theme;?>');"></div>

            <input type="hidden" name="template" class="<?php echo $stylesheet; ?>" value="<?php echo $template; ?>"/>

            <div class="buttons">

                <input class="button-secondary" id="edit_theme_button" type="button" value="<?php _e('Edit','themeselector');?>" ONCLICK="window.location.href='<?php echo $editurl;?>'">

                <?php if ( ! $active ) : ?>
                    <input class="button-primary" id="use_theme_button" type="button" value="<?php _e('Activate','themeselector');?>" ONCLICK="window.location.href='<?php echo $activateurl;?>'">
                <?php endif; ?>

            </div>


            <p class="title">

                <?php echo $theme['Title']?>

                <?php if ( $active ) : ?>

                    <strong>current</strong>

                <?php endif; ?>

                <br>
                <span class="author">
                    <?php _e('by');?><a href="<?php $theme['Author'];?>"><?php echo $theme['Author Name'];?></a>
                </span>

            </h3>
            <?php
        }

        protected function close_button() {
            global $url, $return;

            //wp_reset_vars( array( 'url', 'return' ) );
            $url = urldecode( $url );
            $url = wp_validate_redirect( $url, home_url( '/' ) );
            if ( $return )
                $return = wp_validate_redirect( urldecode( $return ) );
            if ( ! $return )
                $return = $url;

            return '<a href="'. esc_url( $return ? $return : admin_url( 'themes.php' ) ) . '" class="back button">' . __( 'Close' ) . '</a>';
        }

    }
    live_admin_register_extension ( 'WP_LiveThemePreview_Template' );
endif;