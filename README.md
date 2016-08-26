# CakePHP Code Sniffer [![Build Status](https://travis-ci.org/cakephp/cakephp-codesniffer.png?branch=master)](http://travis-ci.org/cakephp/cakephp-codesniffer)

This code works with [phpcs](http://pear.php.net/manual/en/package.php.php-codesniffer.php)
and checks code against the coding standards used in CakePHP.

:warning: The `master` branch contains codesniffer rules that are based on the
PSR2 standard. If you want to check against the historical CakePHP coding
standard use any of the `1.x` releases.

## Installation

You should install this codesniffer with composer:

	composer require --dev "cakephp/cakephp-codesniffer"
	vendor/bin/phpcs --config-set installed_paths /path/to/your/app/vendor/cakephp/cakephp-codesniffer

The second command lets `phpcs` know where to find your new sniffs. Ensure that
you do not overwrite any existing `installed_paths` value.

## Usage

Depending on how you installed the code sniffer changes how you run it. If you have
installed phpcs, and this package with PEAR, you can do the following:

	vendor/bin/phpcs --standard=CakePHP /path/to/code

:warning: Warning when these sniffs are installed with composer, ensure that
you have configured the CodeSniffer `installed_paths` setting.

## Contributing

If you'd like to contribute to the Code Sniffer, you can fork the project add
features and send pull requests.

## Releasing CakePHP Code Sniffer

* Create a signed tag
* Write the changelog in the tag commit
