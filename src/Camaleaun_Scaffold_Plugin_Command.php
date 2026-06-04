<?php

use WP_CLI\Utils;

/**
 * Generates opinionated starter code for a WordPress plugin.
 *
 * Produces a PSR-4 plugin skeleton based on the camaleaun convention:
 * src/ layout, Composer autoloader, unit-only PHPUnit bootstrap (no live
 * WP install required), PHPStan level 6, PHPCS/WPCS, and GitHub Actions CI.
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
	 * * `.github/workflows/tests.yml` is the GitHub Actions CI workflow (default CI).
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
	 * [--plugin_namespace=<namespace>]
	 * : PHP namespace for the plugin classes. Overrides the value derived from
	 * --plugin_name. Use to set a custom namespace like 'MyOrg\\MyPlugin'.
	 *
	 * [--plugin_package=<package>]
	 * : @package tag used in docblocks. Overrides the value derived from
	 * --plugin_name (default: plugin_name with spaces removed).
	 *
	 * [--plugin_github_owner=<owner>]
	 * : GitHub username or organisation used in blueprint-dev.json artifact URL.
	 * Derived from --plugin_author_uri when omitted (last path segment of a github.com URL).
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
		$plugin_slug = $args[0];

		// Validate slug — same rules as the official scaffold plugin command.
		if ( in_array( $plugin_slug, [ '.', '..' ], true ) ) {
			WP_CLI::error( "Invalid plugin slug specified. The slug cannot be '.' or '..'." );
		}
		if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', $plugin_slug ) ) {
			WP_CLI::error( "Invalid plugin slug specified. The slug can only contain alphanumeric characters, underscores, and dashes." );
		}

		$const_prefix = strtoupper( str_replace( '-', '_', $plugin_slug ) );

		$defaults = [
			'plugin_slug'         => $plugin_slug,
			'plugin_name'         => ucwords( str_replace( '-', ' ', $plugin_slug ) ),
			'plugin_description'  => 'PLUGIN DESCRIPTION HERE',
			'plugin_author'       => 'YOUR NAME HERE',
			'plugin_author_uri'   => 'YOUR SITE HERE',
			'plugin_uri'          => 'PLUGIN SITE HERE',
			'plugin_tested_up_to' => $this->get_wp_version(),
			'plugin_github_owner' => '',
		];

		// Merge user-supplied flags over defaults.
		$data = array_merge( $defaults, array_intersect_key( $assoc_args, $defaults ) );

		// Derive namespace and package from the final plugin_name (after --plugin_name override).
		// 'Axell Core' → namespace 'AxellCore', package 'AxellCore'.
		// 'My Plugin'  → namespace 'MyPlugin',  package 'MyPlugin'.
		$data['plugin_package']        = str_replace( ' ', '', $data['plugin_name'] );
		$data['plugin_namespace']      = str_replace( ' ', '', $data['plugin_name'] );
		$data['plugin_namespace_test'] = $data['plugin_namespace'] . '\\Tests';

		// --plugin_namespace overrides the derived namespace entirely.
		if ( ! empty( $assoc_args['plugin_namespace'] ) ) {
			$data['plugin_namespace']      = $assoc_args['plugin_namespace'];
			$data['plugin_namespace_test'] = $assoc_args['plugin_namespace'] . '\\Tests';
		}

		// --plugin_package overrides the derived package entirely.
		if ( ! empty( $assoc_args['plugin_package'] ) ) {
			$data['plugin_package'] = $assoc_args['plugin_package'];
		}
		$data['plugin_const_prefix']   = $const_prefix;
		$data['textdomain']            = $plugin_slug;

		// Derive GitHub owner from plugin_author_uri if not explicitly provided.
		if ( '' === $data['plugin_github_owner'] ) {
			$data['plugin_github_owner'] = $this->extract_github_owner( $data['plugin_author_uri'] );
		}

		// JSON needs \\\\ to represent a single backslash — escape namespaces for composer.json.
		$data['plugin_namespace_json']      = str_replace( '\\', '\\\\', $data['plugin_namespace'] );
		$data['plugin_namespace_test_json'] = str_replace( '\\', '\\\\', $data['plugin_namespace_test'] );

		// Resolve target directory.
		if ( ! empty( $assoc_args['dir'] ) ) {
			if ( ! is_dir( $assoc_args['dir'] ) ) {
				WP_CLI::error( "Cannot create plugin in directory that doesn't exist." );
			}
			$plugin_dir = rtrim( $assoc_args['dir'], '/' ) . "/{$plugin_slug}";
		} else {
			$plugin_dir = $this->resolve_plugins_dir() . "/{$plugin_slug}";
		}

		$force = (bool) Utils\get_flag_value( $assoc_args, 'force' );

		// Core plugin files.
		$files_to_create = [
			"{$plugin_dir}/{$plugin_slug}.php"  => self::mustache_render( 'plugin.mustache', $data ),
			"{$plugin_dir}/src/Autoloader.php" => self::mustache_render( 'plugin-autoloader.mustache', $data ),
			"{$plugin_dir}/src/Packages.php"   => self::mustache_render( 'plugin-packages.mustache', $data ),
			"{$plugin_dir}/src/Constants.php"  => self::mustache_render( 'plugin-constants.mustache', $data ),
			"{$plugin_dir}/readme.txt" => self::mustache_render( 'plugin-readme.mustache', $data ),
			"{$plugin_dir}/composer.json"       => self::mustache_render( 'plugin-composer.mustache', $data ),
			"{$plugin_dir}/Gruntfile.js"        => self::mustache_render( 'plugin-gruntfile.mustache', $data ),
			"{$plugin_dir}/package.json"        => self::mustache_render( 'plugin-package.mustache', $data ),
			"{$plugin_dir}/blueprint-dev.json"  => self::mustache_render( 'plugin-blueprint-dev.mustache', $data ),
			"{$plugin_dir}/playwright.config.ts" => self::mustache_render( 'plugin-playwright.mustache', $data ),
			"{$plugin_dir}/.gitignore"          => self::mustache_render( 'plugin-gitignore.mustache', $data ),
			"{$plugin_dir}/.gitattributes"      => self::mustache_render( 'plugin-gitattributes.mustache', $data ),
			"{$plugin_dir}/.gitmodules"         => self::mustache_render( 'plugin-gitmodules.mustache', $data ),
			"{$plugin_dir}/.distignore"         => self::mustache_render( 'plugin-distignore.mustache', $data ),
			"{$plugin_dir}/.editorconfig"       => self::mustache_render( 'plugin-editorconfig.mustache', $data ),
		];

		$files_written = $this->create_files( $files_to_create, $force );
		$this->log_whether_files_written( $files_written, 'All plugin files were skipped.', 'Created plugin files.' );

		// Test files (unless --skip-tests).
		if ( ! Utils\get_flag_value( $assoc_args, 'skip-tests' ) ) {
			$test_files = [
				"{$plugin_dir}/phpunit.xml"               => self::mustache_render( 'plugin-phpunit.mustache', $data ),
				"{$plugin_dir}/phpstan.neon"              => self::mustache_render( 'plugin-phpstan.mustache', $data ),
				"{$plugin_dir}/phpstan-bootstrap.php"     => self::mustache_render( 'plugin-phpstan-bootstrap.mustache', $data ),
				"{$plugin_dir}/.phpcs.xml.dist"           => self::mustache_render( 'plugin-phpcs.mustache', $data ),
				"{$plugin_dir}/tests/bootstrap.php"       => self::mustache_render( 'plugin-bootstrap.mustache', $data ),
				"{$plugin_dir}/tests/stubs/functions.php" => self::mustache_render( 'plugin-stubs.mustache', $data ),
			];

			$ci = Utils\get_flag_value( $assoc_args, 'ci', 'github' );
			if ( 'github' === $ci ) {
				$test_files["{$plugin_dir}/.github/workflows/tests.yml"]    = self::mustache_render( 'plugin-ci-github.mustache', $data );
				$test_files["{$plugin_dir}/.github/workflows/playground.yml"] = self::mustache_render( 'plugin-ci-playground.mustache', $data );
				$test_files["{$plugin_dir}/.github/workflows/release.yml"]  = self::mustache_render( 'plugin-ci-release.mustache', $data );
			} elseif ( 'gitlab' === $ci ) {
				$test_files["{$plugin_dir}/.gitlab-ci.yml"] =
					self::mustache_render( 'plugin-ci-gitlab.mustache', $data );
			} elseif ( 'circle' === $ci ) {
				$test_files["{$plugin_dir}/.circleci/config.yml"] =
					self::mustache_render( 'plugin-ci-circle.mustache', $data );
			} elseif ( 'bitbucket' === $ci ) {
				$test_files["{$plugin_dir}/bitbucket-pipelines.yml"] =
					self::mustache_render( 'plugin-ci-bitbucket.mustache', $data );
			}

			$test_files_written = $this->create_files( $test_files, $force );
			$this->log_whether_files_written( $test_files_written, 'All test files were skipped.', 'Created test files.' );
		}

		// Clone selfdirectory submodule.
		$this->init_selfdirectory( $plugin_dir );

		// Activate — spawn a new wp process so WP is loaded for plugin activation.
		if ( Utils\get_flag_value( $assoc_args, 'activate' ) ) {
			WP_CLI::runcommand( "plugin activate {$plugin_slug}" );
		} elseif ( Utils\get_flag_value( $assoc_args, 'activate-network' ) ) {
			WP_CLI::runcommand( "plugin activate {$plugin_slug} --network" );
		}
	}

	// ── Helpers ──────────────────────────────────────────────────────────────────

	/**
	 * Extracts the GitHub username/org from a github.com URL.
	 * Returns the last path segment for github.com URLs, empty string otherwise.
	 */
	private function extract_github_owner( string $url ): string {
		$parsed = parse_url( $url );
		if ( empty( $parsed['host'] ) || ! str_contains( $parsed['host'], 'github.com' ) ) {
			return '';
		}
		$parts = array_filter( explode( '/', trim( $parsed['path'] ?? '', '/' ) ) );
		return (string) ( reset( $parts ) ?: '' );
	}

	/**
	 * Clones lib/selfdirectory into the plugin directory.
	 *
	 * Uses git clone rather than git submodule add because the latter requires
	 * the plugin dir itself to already be inside a git repository, which is not
	 * guaranteed at scaffold time. The .gitmodules file is written by create_files();
	 * the developer runs `git submodule update --init` or commits lib/selfdirectory
	 * manually after initialising the plugin repo.
	 */
	private function init_selfdirectory( string $plugin_dir ): void {
		$lib_dir  = $plugin_dir . '/lib/selfdirectory';
		$repo_url = 'https://github.com/fervidum/selfdirectory';

		if ( is_dir( $lib_dir . '/.git' ) || is_file( $lib_dir . '/class-selfdirectory.php' ) ) {
			WP_CLI::log( 'lib/selfdirectory already exists — skipping clone.' );
			return;
		}

		if ( ! is_dir( $plugin_dir . '/lib' ) ) {
			mkdir( $plugin_dir . '/lib', 0755, true );
		}

		WP_CLI::log( 'Cloning lib/selfdirectory...' );

		$exit_code = null;
		$output    = [];
		exec( 'git clone --depth=1 ' . escapeshellarg( $repo_url ) . ' ' . escapeshellarg( $lib_dir ) . ' 2>&1', $output, $exit_code );

		if ( 0 !== $exit_code ) {
			WP_CLI::warning( 'Could not clone lib/selfdirectory: ' . implode( ' ', $output ) );
			WP_CLI::log( "Run manually: git submodule add {$repo_url} lib/selfdirectory" );
		} else {
			WP_CLI::log( 'Cloned lib/selfdirectory.' );
		}
	}

	/**
	 * Resolves the plugins directory without requiring WP to be loaded.
	 * Walks up from cwd looking for wp-load.php, same heuristic WP-CLI uses.
	 */
	private function resolve_plugins_dir(): string {
		$wp_root     = $this->find_wp_root();
		$plugins_dir = $wp_root . '/wp-content/plugins';

		if ( ! is_dir( $plugins_dir ) ) {
			if ( ! mkdir( $plugins_dir, 0755, true ) ) {
				WP_CLI::error( "Could not create plugins directory: {$plugins_dir}" );
			}
		}

		return $plugins_dir;
	}

	/**
	 * Returns the WordPress version by reading wp-includes/version.php directly.
	 * Falls back to '6.0' when WP is not present or the file is unreadable.
	 */
	private function get_wp_version(): string {
		$wp_root      = $this->find_wp_root();
		$version_file = $wp_root . '/wp-includes/version.php';

		if ( is_readable( $version_file ) ) {
			$wp_version = '';
			require $version_file; // defines $wp_version, $wp_db_version, etc.
			if ( '' !== $wp_version ) {
				return $wp_version;
			}
		}

		return '6.0';
	}

	/**
	 * Finds the WordPress root directory.
	 *
	 * Priority:
	 * 1. --path flag passed to WP-CLI.
	 * 2. Walk up from cwd until wp-load.php is found (same heuristic WP-CLI uses).
	 * 3. Fall back to cwd.
	 */
	private function find_wp_root(): string {
		$runner = WP_CLI::get_runner();

		// Explicit --path always wins.
		if ( ! empty( $runner->config['path'] ) ) {
			return rtrim( $runner->config['path'], '/' );
		}

		// Walk up from cwd looking for wp-load.php.
		$dir = getcwd();
		while ( true ) {
			if ( file_exists( $dir . '/wp-load.php' ) ) {
				return $dir;
			}
			$parent = dirname( $dir );
			if ( $parent === $dir ) {
				break; // reached filesystem root
			}
			$dir = $parent;
		}

		return getcwd();
	}

	/**
	 * @param array<string,string> $files_to_create
	 * @param bool                 $force
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
				$created = mkdir( $dir, 0755, true );
				if ( ! $created ) {
					WP_CLI::error( "Could not create directory: {$dir}" );
				}
			}

			// Normalise line endings, same as the official scaffold command.
			$contents = str_replace( "\r\n", "\n", $contents );

			if ( false === file_put_contents( $filename, $contents ) ) {
				WP_CLI::error( "Error creating file: {$filename}" );
			}

			$files_written[] = $filename;
		}

		return $files_written;
	}

	private function prompt_if_files_will_be_overwritten( string $filename, bool $force ): bool {
		if ( ! file_exists( $filename ) ) {
			return true;
		}

		WP_CLI::warning( 'File already exists.' );
		WP_CLI::log( $filename );

		if ( ! $force ) {
			$question          = 'Skip this file, or replace it with a newly generated copy? [s/r]: ';
			$response          = Utils\prompt( $question );
			$should_write_file = ( 'r' === strtolower( trim( $response ) ) );
		} else {
			$should_write_file = true;
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
