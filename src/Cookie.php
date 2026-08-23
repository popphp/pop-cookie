<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Cookie;

use ArrayIterator;

/**
 * Cookie class
 *
 * @category   Pop
 * @package    Pop\Cookie
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Cookie implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * Instance of the cookie object
     * @var Cookie
     */
    static private Cookie $instance;

    /**
     * Cookie IP
     * @var ?string
     */
    private ?string $ip = null;

    /**
     * Cookie Expiration
     * @var int
     */
    private int $expires = 0;

    /**
     * Cookie Path
     * @var string
     */
    private string $path = '/';

    /**
     * Cookie Domain
     * @var ?string
     */
    private ?string $domain = null;

    /**
     * Cookie Secure Flag
     * @var bool
     */
    private bool $secure = false;

    /**
     * Cookie HTTP Only Flag
     * @var bool
     */
    private bool $httponly = false;

    /**
     * Cookie SameSite Flag (None, Lax, Strict)
     * @var string
     */
    private string $samesite = 'Lax';

    /**
     * Constructor
     *
     * Private method to instantiate the cookie object
     *
     * @param  array $options
     */
    private function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Determine whether or not an instance of the cookie object exists
     * already, and instantiate the object if it does not exist.
     *
     * @param  array $options
     * @return Cookie
     */
    public static function getInstance(array $options = []): Cookie
    {
        if (empty(self::$instance)) {
            self::$instance = new Cookie($options);
        } else if (!empty($options)) {
            self::$instance->setOptions($options);
        }

        return self::$instance;
    }

    /**
     * Method to create options array
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            'expires'  => $this->expires,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'secure'   => $this->secure,
            'httponly' => $this->httponly,
            'samesite' => $this->samesite
        ];
    }

    /**
     * Private method to set options
     *
     * @param  array $options
     * @throws Exception
     * @return Cookie
     */
    public function setOptions(array $options = []): Cookie
    {
        // Set the cookie owner's IP address and domain.
        $this->ip = $_SERVER['REMOTE_ADDR'] ?? null;

        if (isset($_SERVER['SERVER_NAME'])) {
            $this->domain = $_SERVER['SERVER_NAME'];
        } else if (isset($_SERVER['HTTP_HOST'])) {
            $this->domain = $_SERVER['HTTP_HOST'];
        }

        $expires  = isset($options['expires']) ? (int)$options['expires'] : $this->expires;
        $path     = $options['path'] ?? $this->path;
        $domain   = $options['domain'] ?? $this->domain;
        $secure   = isset($options['secure']) ? (bool)$options['secure'] : $this->secure;
        $httponly = isset($options['httponly']) ? (bool)$options['httponly'] : $this->httponly;
        $samesite = $this->samesite;

        if (isset($options['samesite'])) {
            if (($options['samesite'] != 'None') && ($options['samesite'] != 'Lax') && ($options['samesite'] != 'Strict')) {
                throw new Exception("Error: The 'samesite' option must be 'None', 'Lax' or 'Strict'.");
            }
            $samesite = $options['samesite'];
        }

        if (($samesite == 'None') && ($secure === false)) {
            throw new Exception("Error: A 'samesite' value of 'None' requires the 'secure' option to be set to true.");
        }

        // Only commit the new state once every option above has validated successfully.
        $this->expires  = $expires;
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httponly = $httponly;
        $this->samesite = $samesite;

        return $this;
    }

    /**
     * Set a cookie
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  array   $options
     * @throws Exception
     * @return Cookie
     */
    public function set(string $name, mixed $value, array $options = []): Cookie
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }

        if (!is_string($value) && !is_numeric($value)) {
            $value = json_encode($value);
        }

        if (setcookie($name, (string)$value, $this->getOptions()) === false) {
            throw new Exception("Error: Unable to set the cookie '" . $name . "'.");
        }

        return $this;
    }

    /**
     * Return the current cookie expiration
     *
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * Return the current cookie path.
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Return the current cookie domain
     *
     * @return string|null
     */
    public function getDomain(): string|null
    {
        return $this->domain;
    }

    /**
     * Return if the cookie is secure
     *
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * Return if the cookie is HTTP only
     *
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httponly;
    }

    /**
     * Return if the cookie's samesite flag
     *
     * @return string
     */
    public function getSamesite(): string
    {
        return $this->samesite;
    }

    /**
     * Return the current IP address.
     *
     * @return string|null
     */
    public function getIp(): string|null
    {
        return $this->ip;
    }

    /**
     * Delete a cookie
     *
     * @param  string $name
     * @param  array  $options
     * @throws Exception
     * @return void
     */
    public function delete(string $name, array $options = []): void
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }
        if (isset($_COOKIE[$name])) {
            $this->expires = time() - 3600;
            if (setcookie($name, (string)$_COOKIE[$name], $this->getOptions()) === false) {
                throw new Exception("Error: Unable to delete the cookie '" . $name . "'.");
            }
        }
    }

    /**
     * Clear (delete) all cookies
     *
     * @param  array $options
     * @throws Exception
     * @return void
     */
    public function clear(array $options = []): void
    {
        if (!empty($options)) {
            $this->setOptions($options);
        }

        $this->expires = time() - 3600;

        foreach ($_COOKIE as $name => $value) {
            if (isset($_COOKIE[$name])) {
                if (setcookie($name, (string)$_COOKIE[$name], $this->getOptions()) === false) {
                    throw new Exception("Error: Unable to clear the cookie '" . $name . "'.");
                }
            }
        }
    }

    /**
     * Method to get the count of cookie data
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->toArray());
    }
    /**
     * Method to iterate over the cookie
     *
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }
    /**
     * Get the cookie values as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $_COOKIE;
    }

    /**
     * Set method to set the value of the $_COOKIE global variable
     *
     * @param  string $name
     * @param  mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $options = [
            'expires'  => $this->expires,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'secure'   => $this->secure,
            'httponly' => $this->httponly
        ];
        $this->set($name, $value, $options);
    }

    /**
     * Get method to return the value of the $_COOKIE global variable
     *
     * @param  string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        $value = null;
        if (isset($_COOKIE[$name])) {
            $raw   = $_COOKIE[$name];
            $value = (str_starts_with($raw, '{') || str_starts_with($raw, '[') || in_array($raw, ['true', 'false', 'null'], true)) ?
                json_decode($raw, true) : $raw;
        }
        return $value;
    }

    /**
     * Return the isset value of the $_COOKIE global variable
     *
     * @param  string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Unset the value in the $_COOKIE global variable
     *
     * @param  string $name
     * @throws Exception
     * @return void
     */
    public function __unset(string $name): void
    {
        if (isset($_COOKIE[$name])) {
            $this->expires = time() - 3600;
            if (setcookie($name, (string)$_COOKIE[$name], $this->getOptions()) === false) {
                throw new Exception("Error: Unable to unset the cookie '" . $name . "'.");
            }
        }
    }

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->__unset($offset);
    }

}
