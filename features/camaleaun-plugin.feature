Feature: Scaffold camaleaun plugin

  # ── Default scaffold ──────────────────────────────────────────────────────────

  Scenario: Scaffold a plugin with defaults
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}
    And I run `wp core version`
    And save STDOUT as {WP_VERSION}

    When I run `wp scaffold camaleaun-plugin hello-world`
    Then STDOUT should contain:
      """
      Success: Created plugin files.
      """
    And STDOUT should contain:
      """
      Success: Created test files.
      """
    And the {PLUGIN_DIR}/hello-world/hello-world.php file should exist
    And the {PLUGIN_DIR}/hello-world/src/Autoloader.php file should exist
    And the {PLUGIN_DIR}/hello-world/src/Packages.php file should exist
    And the {PLUGIN_DIR}/hello-world/src/Constants.php file should exist
    And the {PLUGIN_DIR}/hello-world/readme.txt file should exist
    And the {PLUGIN_DIR}/hello-world/composer.json file should exist
    And the {PLUGIN_DIR}/hello-world/.gitignore file should exist
    And the {PLUGIN_DIR}/hello-world/.distignore file should exist
    And the {PLUGIN_DIR}/hello-world/.editorconfig file should exist
    And the {PLUGIN_DIR}/hello-world/phpunit.xml file should exist
    And the {PLUGIN_DIR}/hello-world/phpstan.neon file should exist
    And the {PLUGIN_DIR}/hello-world/.phpcs.xml.dist file should exist
    And the {PLUGIN_DIR}/hello-world/tests/bootstrap.php file should exist
    And the {PLUGIN_DIR}/hello-world/tests/stubs/functions.php file should exist
    And the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should exist
    And the {PLUGIN_DIR}/hello-world/readme.txt file should contain:
      """
      Tested up to: {WP_VERSION}
      """

  # ── Main plugin file ──────────────────────────────────────────────────────────

  Scenario: Plugin main file has correct header and bootstrap
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world --plugin_name="Hello World" --plugin_author="Jane Doe" --plugin_description="An awesome plugin"`
    Then the {PLUGIN_DIR}/hello-world/hello-world.php file should contain:
      """
      Plugin Name:       Hello World
      """
    And the {PLUGIN_DIR}/hello-world/hello-world.php file should contain:
      """
      Author:            Jane Doe
      """
    And the {PLUGIN_DIR}/hello-world/hello-world.php file should contain:
      """
      Description:       An awesome plugin
      """
    And the {PLUGIN_DIR}/hello-world/hello-world.php file should contain:
      """
      define( 'HELLO_WORLD_VERSION', '0.1.0' );
      """
    And the {PLUGIN_DIR}/hello-world/hello-world.php file should contain:
      """
      \Hello\World\Autoloader::init()
      """
    And the {PLUGIN_DIR}/hello-world/hello-world.php file should contain:
      """
      \Hello\World\Packages::init()
      """

  # ── Generated file contents ───────────────────────────────────────────────────

  Scenario: .gitignore and .distignore contain expected entries
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world`
    Then the {PLUGIN_DIR}/hello-world/.gitignore file should contain:
      """
      .DS_Store
      """
    And the {PLUGIN_DIR}/hello-world/.gitignore file should contain:
      """
      vendor/
      """
    And the {PLUGIN_DIR}/hello-world/.distignore file should contain:
      """
      .git
      .gitignore
      """
    And the {PLUGIN_DIR}/hello-world/.distignore file should contain:
      """
      tests
      """

  Scenario: .phpcs.xml.dist contains WPCS and PHP version rules
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world`
    Then the {PLUGIN_DIR}/hello-world/.phpcs.xml.dist file should contain:
      """
      <rule ref="PHPCompatibilityWP"/>
      """
    And the {PLUGIN_DIR}/hello-world/.phpcs.xml.dist file should contain:
      """
      <config name="testVersion" value="8.1-"/>
      """

  Scenario: Autoloader uses correct namespace
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world`
    Then the {PLUGIN_DIR}/hello-world/src/Autoloader.php file should contain:
      """
      namespace Hello\World;
      """
    And the {PLUGIN_DIR}/hello-world/src/Autoloader.php file should contain:
      """
      class Autoloader
      """

  Scenario: Composer has correct PSR-4 autoload mapping and dev dependencies
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world`
    Then the {PLUGIN_DIR}/hello-world/composer.json file should contain:
      """
      "Hello\\World\\": "src/"
      """
    And the {PLUGIN_DIR}/hello-world/composer.json file should contain:
      """
      "phpunit/phpunit"
      """
    And the {PLUGIN_DIR}/hello-world/composer.json file should contain:
      """
      "brain/monkey"
      """
    And the {PLUGIN_DIR}/hello-world/composer.json file should contain:
      """
      "phpstan/phpstan"
      """
    And the {PLUGIN_DIR}/hello-world/composer.json file should contain:
      """
      "wp-coding-standards/wpcs"
      """

  Scenario: Tests bootstrap runs without a live WP install
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world`
    Then the {PLUGIN_DIR}/hello-world/tests/bootstrap.php file should contain:
      """
      defined( 'ABSPATH' ) || define( 'ABSPATH'
      """
    And the {PLUGIN_DIR}/hello-world/tests/bootstrap.php file should contain:
      """
      vendor/autoload.php
      """
    And the {PLUGIN_DIR}/hello-world/tests/bootstrap.php file should contain:
      """
      stubs/functions.php
      """

  # ── --skip-tests ──────────────────────────────────────────────────────────────

  Scenario: Scaffold with --skip-tests omits test files
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world --skip-tests`
    Then STDOUT should contain:
      """
      Success: Created plugin files.
      """
    And the {PLUGIN_DIR}/hello-world/hello-world.php file should exist
    And the {PLUGIN_DIR}/hello-world/tests directory should not exist
    And the {PLUGIN_DIR}/hello-world/phpunit.xml file should not exist
    And the {PLUGIN_DIR}/hello-world/phpstan.neon file should not exist
    And the {PLUGIN_DIR}/hello-world/.phpcs.xml.dist file should not exist
    And the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should not exist

  # ── --force ───────────────────────────────────────────────────────────────────

  Scenario: Scaffold with --force overwrites existing files
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world`
    And I try `wp scaffold camaleaun-plugin hello-world --force`
    Then STDERR should contain:
      """
      already exists
      """
    And STDOUT should contain:
      """
      Replacing
      """
    And STDOUT should contain:
      """
      Success: Created plugin files.
      """
    And the return code should be 0

  # ── --activate / --activate-network ──────────────────────────────────────────

  Scenario: Scaffold and activate
    Given a WP install

    When I run `wp scaffold camaleaun-plugin hello-world --activate`
    Then STDOUT should contain:
      """
      Success: Created plugin files.
      """
    And STDOUT should contain:
      """
      Plugin 'hello-world' activated.
      """

    When I run `wp plugin status hello-world`
    Then STDOUT should contain:
      """
      Status: Active
      """

  @require-wp-4.6
  Scenario: Scaffold and network activate
    Given a WP multisite install

    When I run `wp scaffold camaleaun-plugin hello-world --activate-network`
    Then STDOUT should contain:
      """
      Plugin 'hello-world' network activated.
      """

  # ── Invalid slug ──────────────────────────────────────────────────────────────

  Scenario: Scaffold with invalid slug '.' or '..'
    Given a WP install

    When I try `wp scaffold camaleaun-plugin .`
    Then STDERR should contain:
      """
      Error: Invalid plugin slug specified. The slug cannot be '.' or '..'.
      """
    And the return code should be 1

    When I try `wp scaffold camaleaun-plugin ..`
    Then STDERR should contain:
      """
      Error: Invalid plugin slug specified. The slug cannot be '.' or '..'.
      """
    And the return code should be 1

  Scenario: Scaffold with invalid slug containing path separators
    Given a WP install

    When I try `wp scaffold camaleaun-plugin ../`
    Then STDERR should contain:
      """
      Error: Invalid plugin slug specified. The slug can only contain alphanumeric characters, underscores, and dashes.
      """
    And the return code should be 1

    When I try `wp scaffold camaleaun-plugin my-plugin/`
    Then STDERR should contain:
      """
      Error: Invalid plugin slug specified. The slug can only contain alphanumeric characters, underscores, and dashes.
      """
    And the return code should be 1

  # ── --dir ─────────────────────────────────────────────────────────────────────

  Scenario: Scaffold with custom --dir places plugin outside wp-content/plugins
    Given a WP install
    And a directory exists at {RUN_DIR}/custom-plugins

    When I run `wp scaffold camaleaun-plugin my-plugin --dir={RUN_DIR}/custom-plugins --skip-tests`
    Then the {RUN_DIR}/custom-plugins/my-plugin/my-plugin.php file should exist
    And the {RUN_DIR}/custom-plugins/my-plugin/src/Autoloader.php file should exist
    And the {RUN_DIR}/custom-plugins/my-plugin/tests directory should not exist

  Scenario: Scaffold with --dir that does not exist produces an error
    Given a WP install

    When I try `wp scaffold camaleaun-plugin my-plugin --dir=/tmp/does-not-exist-xyz`
    Then STDERR should contain:
      """
      Error: Cannot create plugin in directory that doesn't exist.
      """
    And the return code should be 1

  # ── CI providers ──────────────────────────────────────────────────────────────

  Scenario: Default CI (github) generates GitHub Actions workflow, no other CI files
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world`
    Then the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should exist
    And the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should contain:
      """
      phpunit:
      """
    And the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should contain:
      """
      phpstan:
      """
    And the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should contain:
      """
      phpcs:
      """
    And the {PLUGIN_DIR}/hello-world/.gitlab-ci.yml file should not exist
    And the {PLUGIN_DIR}/hello-world/.circleci/config.yml file should not exist
    And the {PLUGIN_DIR}/hello-world/bitbucket-pipelines.yml file should not exist

  Scenario: --ci=gitlab generates GitLab CI config
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world --ci=gitlab`
    Then the {PLUGIN_DIR}/hello-world/.gitlab-ci.yml file should exist
    And the {PLUGIN_DIR}/hello-world/.gitlab-ci.yml file should contain:
      """
      stages:
      """
    And the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should not exist

  Scenario: --ci=circle generates CircleCI config
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world --ci=circle`
    Then the {PLUGIN_DIR}/hello-world/.circleci/config.yml file should exist
    And the {PLUGIN_DIR}/hello-world/.circleci/config.yml file should contain:
      """
      version: 2.1
      """
    And the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should not exist

  Scenario: --ci=bitbucket generates Bitbucket Pipelines config
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world --ci=bitbucket`
    Then the {PLUGIN_DIR}/hello-world/bitbucket-pipelines.yml file should exist
    And the {PLUGIN_DIR}/hello-world/bitbucket-pipelines.yml file should contain:
      """
      pipelines:
      """
    And the {PLUGIN_DIR}/hello-world/.github/workflows/tests.yml file should not exist
