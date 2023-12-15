# GetOrganized API client

## Installation

```sh
composer require itk-dev/getorganized-api-client-php
```

## Usage

```php
use ItkDev\GetOrganized\Client;

$client = new Client($username, $password, $webApplicationUrl);

// Get specific service, e.g. tiles or cases

$tilesService = $client->api('tiles');

$tiles = $tilesService->GetTilesNavigation();
```

## Services

To group GetOrganized API endpoints that consider specific GetOrganized
modules or entities, e.g. Tiles or Cases, we create services that
extend the abstract `ItkDev\GetOrganized\Service` class.

### Example Tiles service

```php
<?php

namespace ItkDev\GetOrganized\Service;

use ItkDev\GetOrganized\Service;

class Tiles extends Service
{
    protected function getApiBaseUrl(): string
    {
        return '/_goapi/Administration/';
    }

    public function GetTilesNavigation()
    {
        return $this->getData(
            'GET',
            $this->getApiBaseUrl().__FUNCTION__,
            []
        );
    }
}

```

## Development

### Tests

```sh
docker run --interactive --tty --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer install
docker run --interactive --tty --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest vendor/bin/phpunit
```

### Coding standards

The following commands let you test that the code adheres to the coding standards:

```sh
docker run --interactive --tty --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer install
docker run --interactive --tty --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer coding-standards-check
```

Automatically fix some coding standards issues by running

```sh
docker run --interactive --tty --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer coding-standards-apply
```

### Code analysis

```sh
docker run --interactive --tty --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer install
docker run --interactive --tty --rm --volume ${PWD}:/app itkdev/php8.1-fpm:latest composer code-analysis
```
