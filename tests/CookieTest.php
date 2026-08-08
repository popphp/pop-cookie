<?php

namespace Pop\Cookie {
    function setcookie(string $name, string $value = '', array $options = []): bool
    {
        if (\Pop\Cookie\Test\CookieTest::$forceSetcookieFailure) {
            return false;
        }
        return \setcookie($name, $value, $options);
    }
}

namespace {
    ob_start();
    $_COOKIE = [];
}

namespace Pop\Cookie\Test {

    use Pop\Cookie\Cookie;
    use Pop\Cookie\Exception;
    use PHPUnit\Framework\TestCase;

    class CookieTest extends TestCase
    {
        public static bool $forceSetcookieFailure = false;

        protected function tearDown(): void
        {
            self::$forceSetcookieFailure = false;
        }

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

        public function testGetInstanceAppliesOptionsOnSubsequentCalls()
        {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['SERVER_NAME'] = 'localhost';

            $cookie1 = Cookie::getInstance(['domain' => 'first.example.com']);
            $cookie2 = Cookie::getInstance(['domain' => 'second.example.com']);

            $this->assertSame($cookie1, $cookie2);
            $this->assertEquals('second.example.com', $cookie2->getDomain());
        }

        public function testInvalidSameSiteValueThrowsException()
        {
            $this->expectException(Exception::class);
            Cookie::getInstance()->setOptions(['samesite' => 'Foo']);
        }

        public function testSameSiteNoneWithoutSecureThrowsException()
        {
            $this->expectException(Exception::class);
            Cookie::getInstance()->setOptions(['samesite' => 'None', 'secure' => false]);
        }

        public function testSameSiteNoneWithSecureDoesNotThrow()
        {
            $cookie = Cookie::getInstance()->setOptions(['samesite' => 'None', 'secure' => true]);
            $this->assertEquals('None', $cookie->getSamesite());
            $this->assertTrue($cookie->isSecure());

            // Restore a valid state for tests that run after this one.
            $cookie->setOptions(['samesite' => 'Lax', 'secure' => false]);
        }

        public function testSetThrowsExceptionWhenSetcookieFails()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_HOST']   = 'localhost';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance(['samesite' => 'Lax', 'secure' => false, 'httponly' => false]);

            self::$forceSetcookieFailure = true;
            $this->expectException(Exception::class);
            $cookie->set('foo', 'bar');
        }

        public function testDeleteThrowsExceptionWhenSetcookieFails()
        {
            $_COOKIE = ['foo' => 'bar'];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_HOST']   = 'localhost';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance(['samesite' => 'Lax', 'secure' => false, 'httponly' => false]);

            self::$forceSetcookieFailure = true;
            $this->expectException(Exception::class);
            $cookie->delete('foo');
        }

        public function testClearThrowsExceptionWhenSetcookieFails()
        {
            $_COOKIE = ['foo' => 'bar'];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_HOST']   = 'localhost';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance(['samesite' => 'Lax', 'secure' => false, 'httponly' => false]);

            self::$forceSetcookieFailure = true;
            $this->expectException(Exception::class);
            $cookie->clear();
        }

        public function testUnsetThrowsExceptionWhenSetcookieFails()
        {
            $_COOKIE = ['foo' => 'bar'];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['HTTP_HOST']   = 'localhost';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance(['samesite' => 'Lax', 'secure' => false, 'httponly' => false]);

            self::$forceSetcookieFailure = true;
            $this->expectException(Exception::class);
            unset($cookie->foo);
        }

        public function testJsonRoundTripBooleanTrue()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance(['samesite' => 'Lax', 'secure' => false]);

            $cookie->set('flag', true);
            $_COOKIE['flag'] = json_encode(true);

            $this->assertTrue($cookie->flag);
        }

        public function testJsonRoundTripBooleanFalse()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance(['samesite' => 'Lax', 'secure' => false]);

            $cookie->set('flag', false);
            $_COOKIE['flag'] = json_encode(false);

            $this->assertFalse($cookie->flag);
        }

        public function testJsonRoundTripNull()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance(['samesite' => 'Lax', 'secure' => false]);

            $cookie->set('maybe', null);
            $_COOKIE['maybe'] = json_encode(null);

            $this->assertNull($cookie->maybe);
        }

        public function testJsonRoundTripIndexedArray()
        {
            $_COOKIE = [];
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $_SERVER['SERVER_NAME'] = 'localhost';
            $cookie = Cookie::getInstance(['samesite' => 'Lax', 'secure' => false]);

            $cookie->set('list', [1, 2, 3]);
            $_COOKIE['list'] = json_encode([1, 2, 3]);

            $this->assertEquals([1, 2, 3], $cookie->list);
        }

    }

}
