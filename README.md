# Public Key Directory Extensions

[![CI](https://github.com/fedi-e2ee/pkd-extensions-php/actions/workflows/ci.yml/badge.svg)](https://github.com/fedi-e2ee/pkd-extensions-php/actions/workflows/ci.yml)
[![Psalm](https://github.com/fedi-e2ee/pkd-extensions-php/actions/workflows/psalm.yml/badge.svg)](https://github.com/fedi-e2ee/pkd-extensions-php/actions/workflows/psalm.yml)
[![Latest Stable Version](https://poser.pugx.org/fedi-e2ee/pkd-extensions/v/stable)](https://packagist.org/packages/fedi-e2ee/pkd-extensions)
[![License](https://poser.pugx.org/fedi-e2ee/pkd-extensions/license)](https://packagist.org/packages/fedi-e2ee/pkd-extensions)
[![Downloads](https://img.shields.io/packagist/dt/fedi-e2ee/pkd-extensions.svg)](https://packagist.org/packages/fedi-e2ee/pkd-extensions)

This library implements the Aux Data Type validations for clients, middleware, and servers.

> ![NOTE]
> Additional extensions must be specified [here](https://github.com/fedi-e2ee/fedi-pkd-extensions) before implemented
> in this repository.

## Installing

```terminal
composer require fedi-e2ee/pkd-extensions-php
```

## Usage

This exposes Registry class that will come pre-loaded with the extension types defined in this library.
To extend it further, simply call:

```php
/** @var \FediE2EE\PKD\Extensions\Registry $registry */
$yourClass = new CustomExtensionType(); 
// ^ must implement  \FediE2EE\PKD\Extensions\ExtensionInterface

$registry->addAuxDataType($yourClass, 'optional-alias-to-support-versioning');
$registry->addAuxDataType($yourClass);
```

To check if a specific Auxiliary Data value conforms to your registered type's expected format, simply
call `isValid()`.

```php
if ($yourClass->isValid($userData)) {
    // You can proceed with processing it!
} else {
    // Rejected. You can call getRejectionReason() to find out why.
    throw new CustomException($yourClass->getRejectionReason());
}
```
