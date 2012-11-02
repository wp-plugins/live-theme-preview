<?php
/**
 * Live Theme Preview
 *
 * @package live-theme-preview
 * @since 0.1
 */

// This is all the data we need in our view
global $active_theme, $return;

$available_themes  = wp_get_themes( array('allowed' => true) );

$tmp = get_theme( get_current_theme() );
$active_theme = $tmp['Stylesheet'];
unset ( $tmp );

$theme = $available_themes[$active_theme];
unset ( $available_themes[$active_theme] );

// Now pretend we're a native WP admin page
require_once( './admin.php' );
if ( ! current_user_can( 'edit_theme_options' ) )
	wp_die( __( 'Cheatin&#8217; uh?' ) );

wp_reset_vars( array( 'url', 'return' ) );
$url = urldecode( $url );
$url = wp_validate_redirect( $url, home_url( '/' ) );
if ( $return )
	$return = wp_validate_redirect( urldecode( $return ) );
if ( ! $return )
	$return = $url;

global $wp_scripts;

$registered = $wp_scripts->registered;
$wp_scripts = new WP_Scripts;
$wp_scripts->registered = $registered;

add_action( 'wp_ltp_print_scripts',        'print_head_scripts', 20 );
add_action( 'wp_ltp_print_footer_scripts', '_wp_footer_scripts'     );
add_action( 'wp_ltp_print_styles',         'print_admin_styles', 20 );

do_action( 'wp_ltp_init' );

wp_enqueue_script( 'customize-controls' );
wp_enqueue_style( 'customize-controls' );

do_action( 'wp_ltp_enqueue_scripts' );

// Let's roll.
@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

wp_user_settings();
_wp_admin_html_begin();

$body_class = '';

if ( wp_is_mobile() ) :
	$body_class .= ' mobile';

	?><meta name="viewport" id="viewport-meta" content="width=device-width, initial-scale=0.8, minimum-scale=0.5, maximum-scale=1.2"><?php
endif;

$is_ios = wp_is_mobile() && preg_match( '/iPad|iPod|iPhone/', $_SERVER['HTTP_USER_AGENT'] );

if ( $is_ios )
	$body_class .= ' ios';

$admin_title = sprintf( __( '%1$s &#8212; WordPress' ), __('Themes') );
?><title><?php echo $admin_title; ?></title><?php

do_action( 'wp_ltp_print_styles' );
do_action( 'wp_ltp_print_scripts' );
?>
</head>
<body class="<?php echo esc_attr( $body_class ); ?>">

    <div class="wp-full-overlay expanded">

        <div class="wp-full-overlay-sidebar">

            <div class="wp-full-overlay-header">

                <form method="link" action="<?php echo esc_url( $return ? $return : admin_url( 'themes.php' ) ); ?>">
                    <input class="back button" id="themes_cancel_button" type="submit" value="<?php _e( 'Close' );?>">
                </form>

            </div>

            <div class="wp-full-overlay-sidebar-content">

                <div class="themes">

                    <?php

                        $this->the_theme_button ( $theme, true );

                        foreach ($available_themes as $tname=>$theme)
                        {
                            $this->the_theme_button ( $theme );
                        }
                    ?>

                </div>

            </div>

            <div id="customize-footer-actions" class="wp-full-overlay-footer">
                <a href="#" class="collapse-sidebar button-secondary" title="<?php _e("Collapse Sidebar"); ?>">
                    <span class="collapse-sidebar-arrow"></span>
                    <span class="collapse-sidebar-label"><?php _e("Collapse"); ?></span>
                </a>
            </div>

        </div>

        <div class="wp-full-overlay-main">
            <iframe width="100%" height="100%" frameborder="0" scrolling="auto" src=""></iframe>
        </div>

        <?php do_action ( "wp_ltp_print_footer_scripts" ); ?>

    </div>
</body>
</html>