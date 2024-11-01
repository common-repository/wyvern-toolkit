<?php
/**
 * Google Fonts module main class file.
 *
 * @package WyvernToolkit\Modules
 */

namespace WyvernToolkit\Modules;

use WyvernToolkit\Abstracts\ModuleConfigs;
use WyvernToolkit\Store;

use function WyvernToolkit\Modules\GoogleFonts\Functions\get_css_configs;
use function WyvernToolkit\Modules\GoogleFonts\Functions\get_downloaded_fonts;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main class for Google Fonts module.
 *
 * @since 1.0.0
 */
class Google_Fonts extends ModuleConfigs {

	/**
	 * @inheritDoc
	 */
	protected function set_info() {
		return array(
			'name'        => esc_html__( 'Google Fonts', 'wyvern-toolkit' ),
			'version'     => '1.0.0',
			'module'      => 'google-fonts',
			'description' => esc_html__( 'Enabling this module helps you to download and load Google Fonts locally from your host or server.', 'wyvern-toolkit' ),
		);
	}

	/**
	 * @inheritDoc
	 */
	public function on_activation() {
		Store::init()->create_folder( $this->module );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_includes() {
		return array(
			'inc/functions.php',
		);
	}

	/**
	 * @inheritDoc
	 */
	public function enqueue_scripts() {
		$css_configs = get_css_configs();

		if ( is_array( $css_configs ) && ! empty( $css_configs ) ) {
			?>
			<style>
			<?php
			foreach ( $css_configs as $css_config ) {
				if ( ! empty( $css_config['family'] ) && ! empty( $css_config['css-var'] ) ) {
					$downloaded_fonts = get_downloaded_fonts( $css_config['family'], true );
					?>
					/* <?php echo esc_attr( $css_config['css-var'] ); ?> */
					@font-face {
						font-family: "<?php echo esc_html( $css_config['family'] ); ?>";
						src: local("<?php echo esc_html( $css_config['family'] ); ?>"), url("<?php echo wp_kses_post( implode( '"); url("', $downloaded_fonts ) ); ?>" );
					}
					/* <?php echo esc_attr( $css_config['css-var'] ); ?> */

					<?php
				}
			}
			?>
			</style>
			<?php
		}
	}

}
