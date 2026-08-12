pop-cookie
==========

[![Build Status](https://github.com/popphp/pop-cookie/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-cookie/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-cookie)](http://cc.popphp.org/pop-cookie/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Storing Non-Scalar Values](#storing-non-scalar-values)
* [Deleting Cookies](#deleting-cookies)
* [Inspecting Cookie Configuration](#inspecting-cookie-configuration)
* [Error Handling](#error-handling)

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
        "popphp/pop-cookie" : "^5.0.0"
    }

[Top](#pop-cookie)

Quickstart
----------

The cookie object is a singleton, obtained via `getInstance()`, which takes an options array. Calling
`getInstance()` again later with a new options array reconfigures the *same* shared instance rather than
creating a new one or being ignored:

```php
use Pop\Cookie\Cookie;

$cookie = Cookie::getInstance([
    'expires'  => time() + 3600,
    'path'     => '/',
    'domain'   => 'www.domain.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Later, in the same request, reconfigure the same instance:
$cookie = Cookie::getInstance(['expires' => time() + 7200]);
```

### Available options

```php
$options = [
    'expires'  => time() + 3600,
    'path'     => '/',
    'domain'   => 'www.domain.com',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax'  // 'Lax', 'Strict', 'None'
];
```

> `samesite => 'None'` requires `secure` to be (or become, in the same call) `true`. Browsers reject
> `SameSite=None` cookies that aren't also `Secure`, so `Cookie` throws a `Pop\Cookie\Exception` rather than
> silently producing a cookie that would be dropped client-side. See [Error Handling](#error-handling).

### Setting cookie values

```php
// Set cookie values
$cookie->foo   = 'bar';
$cookie['baz'] = 123;

// Or, with a per-call options override
$cookie->set('foo', 'bar', ['expires' => time() + 3600]);
```

> Setting a value calls PHP's native `setcookie()` immediately, which only sends the `Set-Cookie` header for
> the *next* request — it does not update the current request's `$_COOKIE` superglobal. Reading `$cookie->foo`
> back in the same request that set it will not reflect the new value until the following request.

### Accessing cookie values

```php
echo $cookie->foo;
echo $cookie['baz'];

// Check whether a cookie is set
if (isset($cookie->foo)) { /* ... */ }
if (isset($cookie['baz'])) { /* ... */ }
```

### Unsetting cookie values

```php
unset($cookie->foo);
unset($cookie['baz']);
```

### Iterating over cookies

`Cookie` implements `Countable` and `IteratorAggregate` over the current `$_COOKIE` data:

```php
echo count($cookie);   // number of cookies currently set

foreach ($cookie as $name => $value) {
    echo $name . ': ' . $value;
}

$all = $cookie->toArray();   // raw $_COOKIE as an array
```

[Top](#pop-cookie)

Storing Non-Scalar Values
--------------------------

Any value that isn't already a string or numeric is transparently JSON-encoded on write and decoded back on
read, so arrays, `bool`, and `null` round-trip automatically:

```php
$cookie->preferences = ['theme' => 'dark', 'perPage' => 25];
$cookie->rememberMe  = true;

// On a later request:
$cookie->preferences;   // ['theme' => 'dark', 'perPage' => 25]
$cookie->rememberMe;    // true
```

Numeric values (`int`/`float`) are stored as-is and read back in their raw string form, same as any other
cookie value — `$_COOKIE` (and therefore this library) only ever deals in strings once a value round-trips
through the browser.

[Top](#pop-cookie)

Deleting Cookies
-----------------

Delete a single cookie with `delete()`, or every cookie currently set with `clear()`. Both accept an optional
`$options` array, applied the same way as `getInstance()`/`set()`:

```php
$cookie->delete('foo');
$cookie->delete('foo', ['path' => '/', 'domain' => 'www.domain.com']);

$cookie->clear();   // deletes every cookie currently in $_COOKIE
```

`unset($cookie->foo)` / `unset($cookie['foo'])` are equivalent to `delete()` for a single cookie, without the
`$options` override.

[Top](#pop-cookie)

Inspecting Cookie Configuration
---------------------------------

```php
$cookie->getOptions();    // array shaped for PHP's setcookie() options parameter
$cookie->getExpires();    // int
$cookie->getPath();       // string
$cookie->getDomain();     // string|null
$cookie->isSecure();      // bool
$cookie->isHttpOnly();    // bool
$cookie->getSamesite();   // string
$cookie->getIp();         // string|null - the requesting client's IP address
```

[Top](#pop-cookie)

Error Handling
---------------

`Cookie` throws `Pop\Cookie\Exception` rather than failing silently in these cases:

- `setOptions()` — and therefore `getInstance()`, `set()`, `delete()`, and `clear()`, which all accept and
  apply an `$options` array — throws if `samesite` is set to anything other than `'None'`, `'Lax'`, or
  `'Strict'`.
- `setOptions()` throws if the resulting configuration has `samesite => 'None'` with `secure` not `true`.
- `set()`, `delete()`, `clear()`, and `unset($cookie->foo)` all throw if the underlying call to PHP's
  `setcookie()` fails (for example, if output has already been sent and headers can no longer be modified).

```php
use Pop\Cookie\Cookie;
use Pop\Cookie\Exception;

try {
    $cookie->setOptions(['samesite' => 'None', 'secure' => false]);
} catch (Exception $e) {
    // 'samesite' => 'None' requires 'secure' => true
}
```

[Top](#pop-cookie)
