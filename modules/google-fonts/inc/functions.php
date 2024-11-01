<?php
/**
 * Google fonts helpers.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit\Modules\GoogleFonts\Functions;

use WyvernToolkit\Filesystem;
use WyvernToolkit\Helpers;
use WyvernToolkit\Store;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_font_category( $family ) {
	$query = query_fonts(
		$family,
		array(
			'compare' => 'exact'
		)
	);

	if ( empty( $query[0]['category'] ) ) {
		return;
	}

	return $query[0]['category'];

}

function get_font_fallback( $family ) {

	$category = get_font_category( $family );

	if ( ! $category ) {
		return;
	}

	$cat = strtolower( $category );

	switch ( $cat ) {
		case 'display':
		case 'handwriting':
			return 'cursive';

		default:
			return $cat;
	}
}

function get_saved_css_configs() {

	$configs = Helpers::get_decoded_option( 'google-fonts_config' );

	if ( ! is_array( $configs ) ) {
		$configs = array();
	}

	return $configs;
}

function update_css_configs( $configkey, $family ) {

	$configs = get_saved_css_configs();

	$configs[ $configkey ] = $family;

	Helpers::update_option( 'google-fonts_config', $configs, true );

	return $configs;

}

function get_css_configs() {

	$css_configs = apply_filters(
		'wyvern_toolkit_filter_google_fonts_css_configs',
		array(
			'primary-font'   => array(
				'label'   => 'Primary Font',
				'css-var' => '--wyvern-toolkit-primary-font',
			),
			'secondary-font' => array(
				'label'   => 'Secondary Font',
				'css-var' => '--wyvern-toolkit-secondary-font',
			),
		)
	);

	$configs = get_saved_css_configs();

	$_configs = array();

	if ( is_array( $css_configs ) && ! empty( $css_configs ) ) {
		foreach ( $css_configs as $configkey => $css_config ) {
			$_configs[ $configkey ] = $css_config;

			$_configs[ $configkey ]['family'] = ! empty( $configs[ $configkey ] ) ? $configs[ $configkey ] : '';
		}
	}

	return $_configs;

}

function get_downloaded_fonts( $family = null, $urlify = false ) {
	$downloaded_fonts = array();

	$files = Store::init()->list_files( 'google-fonts' );

	if ( is_array( $files ) && ! empty( $files ) ) {
		foreach ( $files as $file ) {
			$folder = basename( dirname( $file ) );

			if ( $urlify ) {
				$downloaded_fonts[ $folder ][] = Helpers::convert_path_to_url( $file );
			} else {
				$downloaded_fonts[ $folder ][] = $file;
			}
		}
	}

	if ( $family ) {
		$_folder = sanitize_title( $family );
		return isset( $downloaded_fonts[ $_folder ] ) ? $downloaded_fonts[ $_folder ] : array();
	}

	return $downloaded_fonts;

}

function get_fonts_list( $list = 'all' ) {

	$fonts = array();

	$fs = Filesystem::init();

	$is_ssl = is_ssl();

	$json_file = WYVERN_TOOLKIT_MODULES_GOOGLE_FONTS_PATH . 'fonts.json';

	$json = $fs->get_wp_fs()->get_contents( $json_file );

	$_fonts = json_decode( $json, true );

	if ( is_array( $_fonts ) && ! empty( $_fonts ) ) {
		foreach ( $_fonts as $index => $_font ) {

			$family     = $_font['family'];
			$folder     = sanitize_title( $family );
			$downloaded = ! empty( get_downloaded_fonts( $family ) );

			$_font_files = array();

			if ( is_array( $_font['files'] ) && ! empty( $_font['files'] ) ) {
				foreach ( $_font['files'] as $_font_style => $_font_fileurl ) {
					$_font_files[ $_font_style ] = $is_ssl ? str_replace( 'http://', 'https://', $_font_fileurl ) : str_replace( 'https://', 'http://', $_font_fileurl );
				}
			}

			$fonts[ $index ]               = $_font;
			$fonts[ $index ]['files']      = $_font_files;
			$fonts[ $index ]['folder']     = $folder;
			$fonts[ $index ]['downloaded'] = $downloaded;

			$_font_files = array();

		}
	}

	if ( 'all' !== $list ) {
		return array_values(
			array_filter(
				$fonts,
				function( $font ) use ( $list ) {

					switch ( $list ) {
						case 'downloaded':
							return ! empty( $font['downloaded'] );

						case 'downloadable':
							return empty( $font['downloaded'] );

						default:
							return false;
					}
				}
			)
		);
	}

	return array_values( $fonts );

}

function query_fonts( $keyword, $query = array() ) {

	$query = wp_parse_args(
		$query,
		array(
			'compare' => null, // Accepts: null, '%like%', 'exact'
			'list'    => 'all', // Accepts: 'all', 'downloadable', 'downloaded'.
		)
	);

	$list    = $query['list'];
	$compare = $query['compare'];

	$keyword = strtolower( trim( $keyword ) );

	if ( ! $keyword ) {
		return array();
	}

	$fonts_list = get_fonts_list( $list );

	$fonts = array_filter(
		$fonts_list,
		function( $font ) use ( $keyword, $compare ) {

			if ( empty( $font['family'] ) ) {
				return false;
			}

			switch ( $compare ) {
				case '%like%':
					$family = strtolower( trim( $font['family'] ) );
					return false !== stripos( $family, $keyword );

				case 'exact':
					return strtolower( trim( $font['family'] ) ) === $keyword;

				default:
					return false;
			}
		}
	);

	if ( ! $fonts ) {
		return array();
	}

	return array_values( $fonts );
}

function download_fonts( $family, $files = null ) {

	$fonts = query_fonts(
		$family,
		array(
			'compare' => 'exact',
		)
	);

	if ( ! $files ) {
		return $fonts;
	}

	$font = $fonts[0];

	$urls = array();

	if ( $files ) {
		$selected_files = explode( ',', $files );
		$font_files     = $font['files'];

		if ( is_array( $selected_files ) && ! empty( $selected_files ) ) {
			foreach ( $selected_files as $selected_file ) {
				$urls[ $selected_file ] = $font_files[ $selected_file ];
			}
		}
	}

	if ( is_array( $urls ) && ! empty( $urls ) ) {
		foreach ( $urls as $selected_file => $url ) {

			$filename = 'google-fonts/' . $font['folder'] . '/font-' . $selected_file . '.' . pathinfo( $url, PATHINFO_EXTENSION );

			$data = wp_remote_get(
				$url,
				array(
					'sslverify' => false,
				)
			);

			$content = $data['body'];

			Store::init()->create_file( $filename, $content );

		}
	}

	Store::init()->delete_cache( 'google-fonts' );

	$fonts = query_fonts(
		$family,
		array(
			'compare' => 'exact',
		)
	);

	return $fonts;

}

function api_query( $data, $args ) {

	if ( ! $args ) {
		return $data;
	}

	$args = Helpers::sanitize_array( $args );

	$query    = ! empty( $args['query'] ) ? $args['query'] : '';
	$keyword  = ! empty( $args['keyword'] ) ? $args['keyword'] : '';
	$list     = ! empty( $args['list'] ) ? trim( $args['list'], '/' ) : '';
	$download = ! empty( $args['download'] ) ? $args['download'] : '';
	$files    = ! empty( $args['files'] ) ? $args['files'] : '';

	if ( 'css-configs' === $query ) {
		$type = ! empty( $args['type'] ) ? $args['type'] : 'get';

		if ( 'post' === $type ) {
			$family    = ! empty( $args['family'] ) ? $args['family'] : '';
			$configkey = ! empty( $args['configkey'] ) ? $args['configkey'] : '';

			update_css_configs( $configkey, $family );
		}

		return get_css_configs();
	}

	if ( $list ) {
		return get_fonts_list( $list );
	}

	if ( $download ) {
		return download_fonts( $keyword, $files );
	}

	return query_fonts(
		$keyword,
		array(
			'compare' => '%like%',
		)
	);

}
add_filter( 'wyvern_toolkit_filter_google-fonts_api_query', __NAMESPACE__ . '\api_query', 12, 2 );

function load_css_variables() {
	$css_configs = get_css_configs();

	?>
	<style>
	:root {
	<?php
	if ( is_array( $css_configs ) && ! empty( $css_configs ) ) {
		foreach ( $css_configs as $css_config ) {
			if ( ! empty( $css_config['family'] ) && ! empty( $css_config['css-var'] ) ) {

				$fallback = get_font_fallback( $css_config['family'] );

				$css_vars = '';

				if ( $fallback ) {
					$css_vars = $css_config['css-var'] . ': "' . "{$css_config['family']}\", '{$fallback}';";
				} else {
					$css_vars = $css_config['css-var'] . ': "' . "{$css_config['family']};";
				}

				echo "$css_vars\n"; // @phpcs::ignore

			}
		}
	}

	?>
	}
	</style>
	<?php

}
add_action( 'wp_head', __NAMESPACE__ . '\load_css_variables', 80 );
