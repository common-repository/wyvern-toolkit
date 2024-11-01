<?php
/**
 * Functions for preloader.
 *
 * @package WyvernToolkit\Modules
 */

namespace WyvernToolkit\Modules\Preloader\Functions;

use WyvernToolkit\Helpers;

/**
 * Adds Preloader on body start.
 */
function add_preloader() {
    print( '<div id="wyvern__preloader" class="wyvern__preloader"><span class="icon-wrapper"><span class="loader"></span></span></div>' );
}

add_action( 'wp_body_open', __NAMESPACE__ . '\add_preloader', -1 );

/**
 * Adds Styles for preloader.
 */
function add_preloader_style() {
$loaders = wp_json_file_decode( plugin_dir_path( WYVERN_TOOLKIT_FILE ) . 'build/public/loader.json', [ 'associative' => true ] );
$setting = (object) Helpers::get_decoded_option( 'preloader_config' );

if ( ! isset( $setting->colors ) ) {
    return;
}

$selector =  ! empty( $setting->icon ) ? $setting->icon : 'circle_0';
$css = "#wyvern__preloader{--preloader-color1: {$setting->colors['color1']};--preloader-color2: {$setting->colors['color2']};}";
$css .= str_replace( ".{$selector}", ".loader", $loaders[$selector] );
?>
    <style id="wyvern__preloader-style">
        body.preloader {
            overflow: hidden;
        }
        #wyvern__preloader {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            background: <?php echo sanitize_hex_color( $setting->colors['backgroundColor'] ) ?>;
            z-index: 99999999;
        }
        #wyvern__preloader .loader {
            display: inline-block;
        }
        #wyvern__preloader .icon-wrapper {
            position: absolute;
            left: 50%;
            transform: translate(-50%, -50%);
            top: 50%;
        }
        <?php echo wp_kses_post( $css ); ?>
    </style>
    <script>
    ;(function() {
        window.addEventListener('load', (event) => {
            var loaderNode = document.getElementById('wyvern__preloader')
            if(!! loaderNode ) {
                document.body.classList.toggle('preloader', false)
                loaderNode.remove()
            }
        });
    })();
    </script>
<?php
}
add_action( 'wp_head', __NAMESPACE__ . '\add_preloader_style' );

function add_body_preloader_class( $classes ) {
    $classes[] = 'preloader';
    return $classes;
}
add_action( 'body_class', __NAMESPACE__ . '\add_body_preloader_class' );
