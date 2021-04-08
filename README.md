# PHP IrisCrm API

An unofficial IrisCrm API client heavily inspired by [KnpLabs/php-github-api](https://github.com/KnpLabs/php-github-api).

## Work in Progress Notice

This is a work in progress and is not feature-complete yet. Use at your own risk.

## Requirements

* PHP >= 7.1
* A [PSR-17 implementation](https://packagist.org/providers/psr/http-factory-implementation)
* A [PSR-18 implementation](https://packagist.org/providers/psr/http-client-implementation)

## Install

Via [Composer](https://getcomposer.org).

### PHP 7.1+:
```bash
composer require mbroadhead/php-iriscrm-api:dev-master php-http/guzzle7-adapter http-interop/http-factory-discovery
```

## Basic Usage

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$client = new \IrisCrm\Client();

# find a lead by id
$lead = $client->api('leads')->find(123);

# get all leads
$first_page = $client->api('leads')->all();

```

## Features

## Documentation

See the [`doc` directory](doc/) for more detailed documentation.

## Contributors

- Thanks to all the contributors of [php-github-api](https://github.com/KnpLabs/php-github-api), which this library is based.
