Feature: Scaffold camaleaun plugin

  Scenario: Scaffold a plugin with defaults
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

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

  Scenario: Composer has correct PSR-4 autoload mapping
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

  Scenario: Scaffold with --force overwrites existing files
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world`
    And I run `wp scaffold camaleaun-plugin hello-world --force`
    Then STDOUT should contain:
      """
      Success: Created plugin files.
      """

  Scenario: Scaffold and activate
    Given a WP install
    And I run `wp plugin path`
    And save STDOUT as {PLUGIN_DIR}

    When I run `wp scaffold camaleaun-plugin hello-world --activate`
    Then STDOUT should contain:
      """
      Success: Created plugin files.
      """
    When I run `wp plugin status hello-world`
    Then STDOUT should contain:
      """
      Status: Active
      """

  Scenario: Scaffold with custom --dir places plugin outside wp-content/plugins
    Given a WP install
    And a directory exists at {RUN_DIR}/custom-plugins

    When I run `wp scaffold camaleaun-plugin my-plugin --dir={RUN_DIR}/custom-plugins`
    Then the {RUN_DIR}/custom-plugins/my-plugin/my-plugin.php file should exist
    And the {RUN_DIR}/custom-plugins/my-plugin/src/Autoloader.php file should exist
