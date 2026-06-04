<?php

use WP_CLI\Utils;

/**
 * Generates opinionated starter code for a WordPress plugin.
 *
 * Produces a PSR-4 plugin skeleton based on the camaleaun convention:
 * src/ layout, DI container, unit-only PHPUnit bootstrap (no live WP
 * install required), PHPStan level 6, PHPCS/WPCS and Playwright E2E.
 *
 * ## EXAMPLES
 *
 *     $ wp scaffold camaleaun-plugin my-plugin
 *     Success: Created plugin files.
 *     Success: Created test files.
 *
 * @package camaleaun/wpcli-scaffold-plugin-command
 */
class Camaleaun_Scaffold_Plugin_Command extends WP_CLI_Command {

	/**
	 * Generates starter code for a plugin.
	 *
	 * The following files are always generated:
	 *
	 * * `<slug>.php` is the main PHP plugin file with PSR-4 bootstrap.
	 * * `src/Autoloader.php` bootstraps Composer's PSR-4 autoloader.
	 * * `src/Packages.php` boots all first-party packages.
	 * * `src/Constants.php` centralises plugin paths and URLs.
	 * * `readme.txt` is the readme file for the plugin.
	 * * `composer.json` with phpunit, phpstan, wpcs and brain/monkey.
	 * * `.editorconfig` is the configuration file for editors.
	 * * `.gitignore` tells which files git should ignore.
	 * * `.distignore` tells which files to exclude from distribution.
	 *
	 * The following files are also included unless `--skip-tests` is used:
	 *
	 * * `phpunit.xml` is the configuration file for PHPUnit.
	 * * `phpstan.neon` is the configuration file for PHPStan (level 6).
	 * * `.phpcs.xml.dist` is a collection of PHP_CodeSniffer rules.
	 * * `tests/bootstrap.php` runs unit tests without a live WP install.
	 * * `tests/stubs/functions.php` contains minimal WordPress function stubs.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The internal name of the plugin.
	 *
	 * [--dir=<dirname>]
	 * : Put the new plugin in some arbitrary directory path. Plugin directory
	 * will be path plus supplied slug.
	 *
	 * [--plugin_name=<title>]
	 * : What to put in the 'Plugin Name:' header.
	 *
	 * [--plugin_description=<description>]
	 * : What to put in the 'Description:' header.
	 *
	 * [--plugin_author=<author>]
	 * : What to put in the 'Author:' header.
	 *
	 * [--plugin_author_uri=<url>]
	 * : What to put in the 'Author URI:' header.
	 *
	 * [--plugin_uri=<url>]
	 * : What to put in the 'Plugin URI:' header.
	 *
	 * [--skip-tests]
	 * : Don't generate files for unit testing.
	 *
	 * [--ci=<provider>]
	 * : Choose a configuration file for a continuous integration provider.
	 * ---
	 * default: github
	 * options:
	 *   - github
	 *   - gitlab
	 *   - circle
	 *   - bitbucket
	 * ---
	 *
	 * [--activate]
	 * : Activate the newly generated plugin.
	 *
	 * [--activate-network]
	 * : Network activate the newly generated plugin.
	 *
	 * [--force]
	 * : Overwrite files that already exist.
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp scaffold camaleaun-plugin my-plugin
	 *     Success: Created plugin files.
	 *     Success: Created test files.
	 *
	 *     $ wp scaffold camaleaun-plugin my-plugin \
	 *         --plugin_name="My Plugin" \
	 *         --plugin_author="Gilberto Tavares" \
	 *         --plugin_author_uri="https://github.com/gilbertotavares" \
	 *         --activate
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		$plugin_slug    = $args[0];
		$plugin_name    = ucwords( str_replace( '-', ' ', $plugin_slug ) );
		$plugin_package = str_replace( ' ', '_', $plugin_name );
		$namespace      = str_replace( ' ', '\\', $plugin_name );
		$namespace_test = $namespace . '\\Tests';
		$const_prefix   = strtoupper( str_replace( '-', '_', $plugin_slug ) );

		if ( in_array( $plugin_slug, [ '.', '..' ], true ) ) {
			WP_CLI::error( "Invalid plugin slug specified. The slug cannot be '.' or '..'." );
		}

		$defaults = [
			'plugin_slug'         => $plugin_slug,
			'plugin_name'         => $plugin_name,
			'plugin_package'      => $plugin_package,
			'plugin_namespace'    => $namespace,
			'plugin_namespace_test' => $namespace_test,
			'plugin_const_prefix' => $const_prefix,
			'plugin_description'  => 'PLUGIN DESCRIPTION HERE',
			'plugin_author'       => 'YOUR NAME HERE',
			'plugin_author_uri'   => 'YOUR SITE HERE',
			'plugin_uri'          => 'PLUGIN SITE HERE',
			'plugin_tested_up_to' => $this->get_wp_version(),
		];
		$data = array_merge( $defaults, array_intersect_key( $assoc_args, $defaults ) );
		$data['textdomain'] = $plugin_slug;

		// Resolve target directory.
		if ( ! empty( $assoc_args['dir'] ) ) {
			if ( ! is_dir( $assoc_args['dir'] ) ) {
				WP_CLI::error( "Cannot create plugin in directory that doesn't exist." );
			}
			$plugin_dir = "{$assoc_args['dir']}/{$plugin_slug}";
		} else {
			$plugin_dir = $this->resolve_plugins_dir() . "/{$plugin_slug}";
		}

		$force = (bool) Utils\get_flag_value( $assoc_args, 'force' );

		// Core plugin files.
		$files_to_create = [
			"{$plugin_dir}/{$plugin_slug}.php"    => self::mustache_render( 'plugin.mustache', $data ),
			"{$plugin_dir}/src/Autoloader.php"    => self::mustache_render( 'plugin-autoloader.mustache', $data ),
			"{$plugin_dir}/src/Packages.php"      => self::mustache_render( 'plugin-packages.mustache', $data ),
			"{$plugin_dir}/src/Constants.php"     => self::mustache_render( 'plugin-constants.mustache', $data ),
			"{$plugin_dir}/readme.txt"             => self::mustache_render( 'plugin-readme.mustache', $data ),
			"{$plugin_dir}/composer.json"          => self::mustache_render( 'plugin-composer.mustache', $data ),
			"{$plugin_dir}/.gitignore"             => self::mustache_render( 'plugin-gitignore.mustache', $data ),
			"{$plugin_dir}/.distignore"            => self::mustache_render( 'plugin-distignore.mustache', $data ),
			"{$plugin_dir}/.editorconfig"          => self::mustache_render( 'plugin-editorconfig.mustache', $data ),
		];

		$files_written = $this->create_files( $files_to_create, $force );
		$this->log_whether_files_written( $files_written, 'All plugin files were skipped.', 'Created plugin files.' );

		// Test files (unless --skip-tests).
		if ( ! Utils\get_flag_value( $assoc_args, 'skip-tests' ) ) {
			$test_files = [
				"{$plugin_dir}/phpunit.xml"                   => self::mustache_render( 'plugin-phpunit.mustache', $data ),
				"{$plugin_dir}/phpstan.neon"                  => self::mustache_render( 'plugin-phpstan.mustache', $data ),
				"{$plugin_dir}/.phpcs.xml.dist"               => self::mustache_render( 'plugin-phpcs.mustache', $data ),
				"{$plugin_dir}/tests/bootstrap.php"           => self::mustache_render( 'plugin-bootstrap.mustache', $data ),
				"{$plugin_dir}/tests/stubs/functions.php"     => self::mustache_render( 'plugin-stubs.mustache', $data ),
			];

			// CI configuration.
			$ci = Utils\get_flag_value( $assoc_args, 'ci', 'github' );
			if ( 'github' === $ci ) {
				$test_files["{$plugin_dir}/.github/workflows/tests.yml"] =
					self::mustache_render( 'plugin-ci-github.mustache', $data );
			}

			$test_files_written = $this->create_files( $test_files, $force );
			$this->log_whether_files_written( $test_files_written, 'All test files were skipped.', 'Created test files.' );
		}

		// Activate.
		if ( Utils\get_flag_value( $assoc_args, 'activate' ) ) {
			WP_CLI::run_command( [ 'plugin', 'activate', $plugin_slug ], [], true, true );
		} elseif ( Utils\get_flag_value( $assoc_args, 'activate-network' ) ) {
			WP_CLI::run_command( [ 'plugin', 'activate', $plugin_slug ], [ 'network' => true ], true, true );
		}
	}

	// ── Helpers ──────────────────────────────────────────────────────────────────

	/**
	 * Resolves the plugins directory without requiring WP to be loaded.
	 */
	private function resolve_plugins_dir(): string {
		$wp_path = WP_CLI::get_runner()->config['path'] ?? getcwd();
		$plugins_dir = rtrim( $wp_path, '/' ) . '/wp-content/plugins';
		if ( ! is_dir( $plugins_dir ) ) {
			if ( ! mkdir( $plugins_dir, 0755, true ) ) {
				WP_CLI::error( "Could not create plugins directory: {$plugins_dir}" );
			}
		}
		return $plugins_dir;
	}

	/**
	 * Returns the WordPress version without requiring WP to be loaded.
	 */
	private function get_wp_version(): string {
		$wp_path = WP_CLI::get_runner()->config['path'] ?? getcwd();
		$version_file = rtrim( $wp_path, '/' ) . '/wp-includes/version.php';
		if ( file_exists( $version_file ) ) {
			$wp_version = '';
			require $version_file;
			if ( '' !== $wp_version ) {
				return $wp_version;
			}
		}
		return '6.0';
	}

	/**
	 * @param array<string,string> $files_to_create
	 * @return array<string>
	 */
	private function create_files( array $files_to_create, bool $force ): array {
		$files_written = [];

		foreach ( $files_to_create as $filename => $contents ) {
			$should_write_file = $this->prompt_if_files_will_be_overwritten( $filename, $force );
			if ( ! $should_write_file ) {
				continue;
			}

			$dir = dirname( $filename );
			if ( ! is_dir( $dir ) ) {
				$make = function_exists( 'wp_mkdir_p' ) ? wp_mkdir_p( $dir ) : mkdir( $dir, 0755, true );
				if ( ! $make ) {
					WP_CLI::error( "Could not create directory: {$dir}" );
				}
			}

			if ( false === file_put_contents( $filename, $contents ) ) {
				WP_CLI::error( "Error creating file: {$filename}" );
			}

			$files_written[] = $filename;
		}

		return $files_written;
	}

	private function prompt_if_files_will_be_overwritten( string $filename, bool $force ): bool {
		$should_write_file = true;

		if ( ! file_exists( $filename ) ) {
			return true;
		}

		WP_CLI::log( 'File already exists: ' . $filename );

		if ( ! $force ) {
			$question         = 'Skip this file, or replace it with a newly generated copy?';
			$response         = Utils\prompt( $question );
			$should_write_file = ( 'r' === strtolower( $response ) );
		}

		$outcome = $should_write_file ? 'Replacing' : 'Skipping';
		WP_CLI::log( $outcome . PHP_EOL );

		return $should_write_file;
	}

	/**
	 * @param array<string> $files_written
	 */
	private function log_whether_files_written( array $files_written, string $skip_message, string $success_message ): void {
		if ( empty( $files_written ) ) {
			WP_CLI::log( $skip_message );
		} else {
			WP_CLI::success( $success_message );
		}
	}

	private static function mustache_render( string $template, array $data = [] ): string {
		return Utils\mustache_render( dirname( __DIR__ ) . "/templates/{$template}", $data );
	}
}