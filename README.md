pop-cookie
==========

OVERVIEW
--------
`pop-cookie` is a component used to manage and manipulate cookies in the PHP web environment.

`pop-cookie` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-cookie` using Composer.

    composer require popphp/pop-cookie

BASIC USAGE
-----------

```php
use Pop\Cookie\Cookie;

$cookie = Cookie::getInstance([
    'path'   => '/',
    'expire' => time() + 3600
]);

// Set cookie values
$cookie->foo = 'bar';
$cookie['baz'] = 123;

// Access cookie values
echo $cookie->foo;
echo $cookie['baz'];

// Unset cookie values
unset($cookie->foo);
unset($cookie['baz']);
```
