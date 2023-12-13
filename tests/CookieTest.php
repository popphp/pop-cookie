<?php

namespace {
    ob_start();
    $_COOKIE = [];
}

namespace Pop\Cookie\Test {

    use Pop\Cookie\Cookie;
    use PHPUnit\Framework\TestCase;

    class CookieTest extends TestCase
    {

        public function testCookie()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_HOST']   = 'localhost';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance([
                'expires'  => 3600,
                'path'     => '/',
                'domain'   => 'localhost',
                'secure'   => false,
                'httponly' => false,
                'samesite' => 'Strict'
            ]);
            $cookie->foo    = 'bar';
            $cookie['baz']  = 123;
            $_COOKIE['foo'] = 'bar';
            $_COOKIE['baz'] = 123;

            $this->assertEquals('bar', $_COOKIE['foo']);
            $this->assertEquals(3600, $cookie->getExpires());
            $this->assertEquals('/', $cookie->getPath());
            $this->assertEquals('localhost', $cookie->getDomain());
            $this->assertEquals('127.0.0.1', $cookie->getIp());
            $this->assertEquals('Strict', $cookie->getSamesite());
            $this->assertFalse($cookie->isSecure());
            $this->assertFalse($cookie->isHttpOnly());
            $this->assertEquals(2, $cookie->count());
            $this->assertEquals(2, count($cookie->toArray()));

            $i = 0;
            foreach ($cookie as $c) {
                $i++;
            }
            $this->assertEquals(2, $i);
            $this->assertTrue(isset($cookie['foo']));
            $this->assertEquals('bar', $cookie['foo']);

            unset($cookie['baz']);
            unset($_COOKIE['baz']);
            $this->assertNull($cookie['baz']);
        }

        public function testCookieJson()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_HOST']    = 'localhost';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance([
                'expires'  => 3600,
                'path'     => '/',
                'domain'   => 'localhost',
                'secure'   => false,
                'httponly' => false
            ]);
            $json = ['test'=> 123];
            $cookie->set('json', $json);
            $_COOKIE['json'] = json_encode($json);
            $this->assertEquals('{"test":123}', $_COOKIE['json']);
        }

        public function testDeleteCookie()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_HOST']    = 'localhost';
            unset($_SERVER['SERVER_NAME']);
            $cookie = Cookie::getInstance([
                'expires'  => 3600,
                'path'     => '/',
                'domain'   => 'localhost',
                'secure'   => false,
                'httponly' => false
            ]);
            $json = ['test'=> 123];
            $cookie->set('json', $json);
            $_COOKIE['json'] = json_encode($json);
            $this->assertEquals('{"test":123}', $_COOKIE['json']);
            $cookie->delete('json', [
                'expire'   => 3600,
                'path'     => '/',
                'domain'   => 'localhost',
                'secure'   => false,
                'httponly' => false
            ]);
            unset($_COOKIE['json']);
            $this->assertFalse(isset($cookie['json']));
        }

        public function testClearCookie()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_HOST']    = 'localhost';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance([
                'expires'  => 3600,
                'path'     => '/',
                'domain'   => 'localhost',
                'secure'   => false,
                'httponly' => false
            ]);
            $json = ['test'=> 123];
            $cookie->set('json', $json);
            $_COOKIE['json'] = json_encode($json);
            $this->assertEquals('{"test":123}', $_COOKIE['json']);
            $cookie->clear([
                'expires'  => 3600,
                'path'     => '/',
                'domain'   => 'localhost',
                'secure'   => false,
                'httponly' => false
            ]);
            unset($_COOKIE['json']);
            $this->assertFalse(isset($cookie['json']));
        }

    }

}
