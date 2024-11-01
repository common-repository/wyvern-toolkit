<?php
/**
 * Class for managing Store from Settings page.
 *
 * @package WyvernToolkit
 */

namespace WyvernToolkit;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for managing Store from Settings page.
 *
 * @since 1.0.4
 */
class StoreManager extends Store {

	protected function get_meta( $fullpath ) {

		$dirname       = dirname( $fullpath );
		$type          = is_file( $fullpath ) ? "file" : "folder";
		$size          = 'file' === $type ? wp_filesize( $fullpath ) : recurse_dirsize( $fullpath );
		$last_modified = filemtime( $fullpath );

		/**
		 * Generate meta informations by type.
		 */
		return array(
			'type'   => $type,
			'name'   => wp_basename( $fullpath ),
			'path'   => $this->convert_path_to_relative( $fullpath ),
			'parent' => array(
				'basename' => wp_basename( $dirname ),
				'path'     => $this->convert_path_to_relative( $dirname )
			),
			'last_modified' => array(
				'raw'       => $last_modified,

				/* translators: %s is the human_time_diff() result.  */
				'formatted' => sprintf( __( '%s ago', 'wyvern-toolkit' ), esc_html( human_time_diff( $last_modified ) ) ),
			),
			'size'      => array(
				'raw'       => $size,
				'formatted' => size_format( $size, 2 ),
			),
		);

	}

	protected function get_breadcrumbs( $path ) {

		$parents = explode( DIRECTORY_SEPARATOR, str_replace( self::ROOT, '', $path ) );

		$parents = array_filter( $parents );

		$breadcrumbs = array(
			array(
				'name' => '~ / store',
				'path' => '',
			)
		);

		if ( is_array( $parents ) && ! empty( $parents ) ) {
			foreach ( $parents as $parent ) {

				$breadcrumbs[] = array(
					'name' => $parent,
					'path' => $this->convert_path_to_relative( substr( $path, 0, strpos( $path, $parent ) ) . $parent ),
				);
			}
		}

		return $breadcrumbs;
	}

	public function list( $path = self::ROOT ) {

		if ( ! $path ) {
			$path = self::ROOT;
		}

		$path = self::normalize_path( $path );

		$files_folders = @scandir( $path ); // @phpcs:ignore

		$list = array(
			'folder' => array(),
			'file'   => array(),
		);

		if ( is_array( $files_folders ) && ! empty( $files_folders ) ) {
			foreach ( $files_folders as $file_folder ) {
				if ( '.' === $file_folder || '..' === $file_folder ) {
					continue;
				}

				$fullpath = self::normalize_path( "{$path}/{$file_folder}" );

				$meta = $this->get_meta( $fullpath );

				if ( ! $meta ) {
					continue;
				}

				/**
				 * Generate meta informations by type.
				 */
				$list[ $meta['type'] ][] = $meta;

			}
		}

		/**
		 * Make sure folders are always listed first.
		 */
		$contents =  array_merge( $list['folder'], $list['file'] );

		$filetype    = '';
		$current     = $this->convert_path_to_relative( $path );
		$pathtype    = is_file( $path ) ? "file" : "folder";
		$breadcrumbs = $this->get_breadcrumbs( $path );

		if ( 'file' === $pathtype ) {
			$filetype = wp_ext2type( pathinfo( $path, PATHINFO_EXTENSION ) );
		}

		return compact(
			'current',
			'pathtype',
			'filetype',
			'breadcrumbs',
			'contents'
		);
	}

	public function delete( $path ) {

		if ( $path ) {
			$path = self::normalize_path( $path );
		}

		$filemeta = $this->get_meta( $path );

		if ( empty( $filemeta['path'] ) ) {
			return $this->list();
		}

		$type = $filemeta['type'];

		$parent = $filemeta['parent']['path'];

		if ( 'file' === $type ) {
			$this->delete_file( $path );
		} else {
			$this->delete_folder( $path );
		}

		delete_transient( 'dirsize_cache' );

		return $this->list( $parent );
	}
}
