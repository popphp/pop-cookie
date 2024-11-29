pop-cookie
==========

[![Build Status](https://github.com/popphp/pop-cookie/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-cookie/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-cookie)](http://cc.popphp.org/pop-cookie/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)

Overview
--------
`pop-cookie` is a component used to securely create and manage cookies in a PHP web environment.
With it, you can set and retrieve cookie values, as well as set required configuration options
for the web application environment.

`pop-cookie` is a component of the [Pop PHP Framework](https://www.popphp.org/).

[Top](#pop-cookie)

Install
-------

Install `pop-cookie` using Composer.

    composer require popphp/pop-cookie

Or, require it in your composer.json file

    "require": {
        "popphp/pop-cookie" : "^4.0.2"
    }

[Top](#pop-cookie)

Quickstart
----------

The cookie object can be created using the `getInstance()` method, which takes an options array:

```php
use Pop\Cookie\Cookie;

$cookie = Cookie::getInstance([
    'path'   => '/',
    'expire' => time() + 3600,
]);
```

### Available options

```php
$options = [
    'path'     => '/',
    'expire'   => time() + 3600,
    'domain'   => 'www.domain.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'  // 'Lax', 'Strict', 'None'
];
```

From there, you can interact with the cookie object.

### Setting cookie values

```php
// Set cookie values
$cookie->foo = 'bar';
$cookie['baz'] = 123;
```

### Accessing cookie values

```php
echo $cookie->foo;
echo $cookie['baz'];
```

### Unset cookie values

```php
unset($cookie->foo);
unset($cookie['baz']);
```

[Top](#pop-cookie)
