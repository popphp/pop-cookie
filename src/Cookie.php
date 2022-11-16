<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Cookie;

use ReturnTypeWillChange;

/**
 * Cookie class
 *
 * @category   Pop
 * @package    Pop\Cookie
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2023 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.3.0
 */
class Cookie implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * Instance of the cookie object
     * @var Cookie
     */
    static private $instance;

    /**
     * Cookie IP
     * @var string
     */
    private $ip = null;

    /**
     * Cookie Expiration
     * @var int
     */
    private $expires = 0;

    /**
     * Cookie Path
     * @var string
     */
    private $path = '/';

    /**
     * Cookie Domain
     * @var string
     */
    private $domain = null;

    /**
     * Cookie Secure Flag
     * @var boolean
     */
    private $secure = false;

    /**
     * Cookie HTTP Only Flag
     * @var boolean
     */
    private $httponly = false;

    /**
     * Cookie SameSite Flag (None, Lax, Strict)
     * @var boolean
     */
    private $samesite = 'Lax';

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
     * Method to create options array
     *
     * @return array
     */
    public function getOptions()
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
     * @return Cookie
     */
    public function setOptions(array $options = [])
    {
        // Set the cookie owner's IP address and domain.
        $this->ip     = $_SERVER['REMOTE_ADDR'];
        $this->domain = (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']);

        if (isset($options['expires'])) {
            $this->expires = (int)$options['expires'];
        }
        if (isset($options['path'])) {
            $this->path = $options['path'];
        }
        if (isset($options['domain'])) {
            $this->domain = $options['domain'];
        }
        if (isset($options['secure'])) {
            $this->secure = (bool)$options['secure'];
        }
        if (isset($options['httponly'])) {
            $this->httponly = (bool)$options['httponly'];
        }
        if (isset($options['samesite'])) {
            if (($options['samesite'] == 'None') || ($options['samesite'] == 'Lax') || ($options['samesite'] == 'Strict')) {
                $this->samesite = $options['samesite'];
            }

        }

        return $this;
    }

    /**
     * Determine whether or not an instance of the cookie object exists
     * already, and instantiate the object if it does not exist.
     *
     * @param  array $options
     * @return Cookie
     */
    public static function getInstance(array $options = [])
    {
        if (empty(self::$instance)) {
            self::$instance = new Cookie($options);
        }

        return self::$instance;
    }

    /**
     * Set a cookie
     *
     * @param  string  $name
     * @param  mixed   $value
     * @param  array   $options
     * @return Cookie
     */
    public function set($name, $value, array $options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }

        if (!is_string($value) && !is_numeric($value)) {
            $value = json_encode($value);
        }

        setcookie($name, $value, $this->getOptions());
        return $this;
    }

    /**
     * Return the current cookie expiration
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Return the current cookie path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Return the current cookie domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Return if the cookie is secure
     *
     * @return boolean
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Return if the cookie is HTTP only
     *
     * @return boolean
     */
    public function isHttpOnly()
    {
        return $this->httponly;
    }

    /**
     * Return if the cookie's samesite flag
     *
     * @return string
     */
    public function getSamesite()
    {
        return $this->samesite;
    }

    /**
     * Return the current IP address.
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Delete a cookie
     *
     * @param  string $name
     * @param  array  $options
     * @return void
     */
    public function delete($name, array $options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
        if (isset($_COOKIE[$name])) {
            $this->expires = time() - 3600;
            setcookie($name, $_COOKIE[$name], $this->getOptions());
        }
    }

    /**
     * Clear (delete) all cookies
     *
     * @param  array $options
     * @return void
     */
    public function clear(array $options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }

        $this->expires = time() - 3600;

        foreach ($_COOKIE as $name => $value) {
            if (isset($_COOKIE[$name])) {
                setcookie($name, $_COOKIE[$name], $this->getOptions());
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
     * @return \ArrayIterator
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->toArray());
    }
    /**
     * Get the cookie values as an array
     *
     * @return array
     */
    public function toArray()
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
    public function __set($name, $value)
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
    public function __get($name)
    {
        $value = null;
        if (isset($_COOKIE[$name])) {
            $value = (substr($_COOKIE[$name], 0, 1) == '{') ? json_decode($_COOKIE[$name], true) : $_COOKIE[$name];
        }
        return $value;
    }

    /**
     * Return the isset value of the $_COOKIE global variable
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($_COOKIE[$name]);
    }

    /**
     * Unset the value in the $_COOKIE global variable
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        if (isset($_COOKIE[$name])) {
            $this->expires = time() - 3600;
            setcookie($name, $_COOKIE[$name], $this->getOptions());
        }
    }

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

}
