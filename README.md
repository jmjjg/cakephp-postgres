# cakephp-postgres

## Description

CakePHP 3 plugin that provides classes for adding default validation rules from the database table schema.

Caching is enabled by default but can be disabled in the configuration or at run-time.

Tested with CakePHP 3.1.0, 3.2.2.

## Main classes

AutovalidateBehavior reads various informations from the database table schema... and automatically adds those validation rules to the default ones.

## Setup

Assuming the plugin is installed under plugins/Postgres, add the following to config/bootstrap.php:

    Plugin::load('Postgres', ['autoload' => true]);

## Usage

The following code should be added to your table classes, inside the initialize() method.

The two behaviors are independant and can be loaded in any order.

Note that NULL and boolean TRUE and are equivalent as configuration values.

    public function initialize(array $config)
    {
        // ...
		$this->addBehavior('PostgresAutovalidate',
			[
				'className' => 'Postgres.Autovalidate',
				// Default values
				// 1°) Accepted validator names, as a string or an array of strings, NULL for any
				'accepted' => null,
				// 2°) Cache validation rules and their error messages ?
				'cache' => null,
				// 3°) Domain to use for error messages
				'domain' => 'postgres'
			]
		);
        // ...
    }

### Code quality
```bash
sudo bash -c "( sudo bin/cake orm_cache clear ; rm logs/*.log ; rm -r logs/quality ; find tmp -type f ! -name 'empty' -exec rm {} \; )"
sudo -u apache ant quality -f plugins/Postgres/vendor/Jenkins/build.xml
```
