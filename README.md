# FeatureFlags plugin for CakePHP

FeatureFlags is a CakePHP plugin that enables your to use feature flags in your
application to enable functionality based on simple application config, or
a more complex rule based system.

By using feature flags you can separate your code deployments from what features
are enabled, or have different features enabled in different environments. For
example, you could have features that are incomplete, enabled in staging
environments but disabled in production.

## Installation

You can install this plugin into your application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require markstory/cakephp-feature-flags
```

## Usage

First you need to decide if you want simple boolean feature flags, or more complex
rule based feature flags. For the examples below, we'll assume you have two
features (`calendar-v2`, and `checkout-v2`) that you want to conditionally enable.

## Simple Feature Flags

First create a configuration file `config/features.php`, with the following:

```php
<?php
return [
    'Features' => [
        'calendar-v2' => true,
        'checkout-v2' => false,
    ],
];
```

Next, in `config/bootstrap.php` add the following:

```php
// Load the feature configuration during application startup.
Configure::load('features');
```

In your `Application::services()` method add the following:

```php
use FeatureFlags\FeatureManagerInterface;
use FeatureFlags\Simple\FeatureManager;

public function services(ContainerInterface $container): void
{
    $container->addShared(FeatureManagerInterface::class, function () {
        return new FeatureManager(Configure::read('Features'));
    });
}
```

With the DI container setup, you can have CakePHP inject the `FeatureManager`
into your controllers, and commands as required.

```php
public function view(FeatureManagerInterface $features, $id)
{
    if ($features->has('calendar-v2')) {
        // Logic for the new feature.
        return $this->render();
    }
    ...
}
```

## Rule Based Feature Flags

First create a configuration file `config/features.php`, with the following:

```php
<?php
return [
    'Features' => [
        // Each key is a feature name
        'calendar-v2' => [
            // Features are composed of many segments.
            // All conditions in a segment must match for a feature to be
            // granted
            'segments' => [
                // Segments can incrementally enable features
                'rollout' => 50,
                // Segments are composed of multiple conditions
                'conditions' => [
                    [
                        'property' => 'user_email',
                        'op' => 'equal',
                        'value' => 'winner@example.com',
                    ]
                ],
            ],
        ],
    ],
];
```

Next, in `config/bootstrap.php` add the following:

```php
// Load the feature configuration during application startup.
Configure::load('features');
```

In your `Application::services()` method add the following:

```php
use FeatureFlags\FeatureManagerInterface;
use FeatureFlags\RuleBased\FeatureManager;

public function services(ContainerInterface $container): void
{
    $container->addShared(FeatureManagerInterface::class, function () {
        return new FeatureManager(
            function (array $data) {
                $context = [];
                // Add properties to `$context` based on the data you use
                // to check features.
                return $context;
            }
            Configure::read('Features')
        );
    });
}
```

With the DI container setup, you can have CakePHP inject the `FeatureManager`
into your controllers, and commands as required.

```php
public function view(FeatureManagerInterface $features, $id)
{
    // Including application data in `features->has` calls allows
    // you to build custom feature logic that fits your application.
    $identity = $this->request->getAttribute('identity');
    if ($features->has('calendar-v2', ['user' => $identity])) {
        // Logic for the new feature.
        return $this->render();
    }
    ...
}
```

### Writing conditions

Conditions will safely extract keys out of the `context` that your application prepares.
Each condition compares a single attribute in your `context` to a known value.

```php
[
    'property' => 'user_email',
    'op' => 'equal',
    'value' => 'winner@example.com',
]
```

The following `op` values are supported:

- `equal` Match if the context value matches `value`
- `not_equal` Match if the context value is not equal to `value` 
- `in` Match if the context value is within the array of `value`.
- `not_in` Match if the context value is not contained in the array of `value`.
