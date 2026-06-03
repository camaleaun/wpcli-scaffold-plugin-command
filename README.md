# wpcli-scaffold-plugin-command

WP-CLI command that scaffolds an opinionated WordPress plugin skeleton based on the **camaleaun** convention — the same architecture used in [axellcore](https://github.com/axellhydrosystems/axellcore) and cookycore.

## Generated structure

```
<slug>/
├── <slug>.php                   # Plugin header + PSR-4 bootstrap
├── src/
│   ├── Autoloader.php           # Composer PSR-4 bootstrap
│   ├── Packages.php             # First-party package boot
│   └── Constants.php            # Centralised paths & URLs
├── tests/
│   ├── bootstrap.php            # Unit bootstrap (no live WP needed)
│   └── stubs/functions.php      # Minimal WP function stubs
├── .github/
│   └── workflows/tests.yml      # CI: PHPUnit + PHPStan + PHPCS
├── composer.json                # phpunit, brain/monkey, phpstan, wpcs
├── phpunit.xml
├── phpstan.neon                 # Level 6
├── .phpcs.xml.dist
├── readme.txt
├── .gitignore
├── .distignore
└── .editorconfig
```

## Installation

```bash
wp package install camaleaun/wpcli-scaffold-plugin-command
```

## Usage

```bash
wp scaffold camaleaun-plugin <slug> [options]
```

### Options

| Option | Description |
|---|---|
| `<slug>` | Plugin slug (directory name and text domain) |
| `--plugin_name=<title>` | Plugin Name header |
| `--plugin_description=<desc>` | Description header |
| `--plugin_author=<author>` | Author header |
| `--plugin_author_uri=<url>` | Author URI header |
| `--plugin_uri=<url>` | Plugin URI header |
| `--skip-tests` | Skip test files |
| `--ci=<provider>` | CI provider: `github` (default), `gitlab`, `circle`, `bitbucket` |
| `--activate` | Activate after scaffolding |
| `--activate-network` | Network-activate after scaffolding |
| `--force` | Overwrite existing files |
| `--dir=<dirname>` | Custom output directory |

### Examples

```bash
# Minimal
wp scaffold camaleaun-plugin my-plugin

# Full
wp scaffold camaleaun-plugin my-plugin \
  --plugin_name="My Plugin" \
  --plugin_description="Does awesome things." \
  --plugin_author="Gilberto Tavares" \
  --plugin_author_uri="https://github.com/gilbertotavares" \
  --activate

# Skip tests
wp scaffold camaleaun-plugin my-plugin --skip-tests
```

## Testing

This package is tested with [Behat](https://behat.org/) using the `wp-cli/wp-cli-tests` framework — the same BDD approach used across all official WP-CLI packages.

```bash
composer prepare-tests
composer behat
```
