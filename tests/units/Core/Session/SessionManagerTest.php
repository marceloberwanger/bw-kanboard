<?php

namespace KanboardTests\units\Core\Session;

use KanboardTests\units\Base;
use Kanboard\Core\Http\Request;
use Kanboard\Core\Session\SessionManager;

class SessionManagerTest extends Base
{
    public function testCookiePathAtRoot()
    {
        $this->assertEquals('/', $this->buildSessionManager('/index.php')->getCookiePath());
    }

    public function testCookiePathInSubDirectoryHasNoTrailingSlash()
    {
        $this->assertEquals('/kanboard', $this->buildSessionManager('/kanboard/index.php')->getCookiePath());
    }

    public function testCookiePathInNestedSubDirectoryHasNoTrailingSlash()
    {
        $this->assertEquals('/apps/kanboard', $this->buildSessionManager('/apps/kanboard/index.php')->getCookiePath());
    }

    public function testLegacyCookieIsReplacedByTheCurrentCookie()
    {
        $sessionManager = $this->buildSessionManager('/kanboard/index.php', array(
            'REQUEST_URI' => '/kanboard/?controller=TaskCreationController&action=save',
            'HTTP_COOKIE' => 'KB_SID=legacy; KB_SID=current; KB_RM=token',
        ));

        $this->assertSame('current', $sessionManager->getSessionIdBehindLegacyCookie());
    }

    public function testLegacyCookieIsFoundBetweenOtherCookies()
    {
        $sessionManager = $this->buildSessionManager('/kanboard/index.php', array(
            'REQUEST_URI' => '/kanboard/dashboard',
            'HTTP_COOKIE' => 'theme=dark; KB_SID=legacy; XKB_SID=other; KB_SID=current',
        ));

        $this->assertSame('current', $sessionManager->getSessionIdBehindLegacyCookie());
    }

    public function testSingleCookieIsLeftUntouched()
    {
        $sessionManager = $this->buildSessionManager('/kanboard/index.php', array(
            'REQUEST_URI' => '/kanboard/',
            'HTTP_COOKIE' => 'KB_SID=current; KB_RM=token',
        ));

        $this->assertSame('', $sessionManager->getSessionIdBehindLegacyCookie());
    }

    public function testCookiesAreLeftUntouchedAtRoot()
    {
        $sessionManager = $this->buildSessionManager('/index.php', array(
            'REQUEST_URI' => '/',
            'HTTP_COOKIE' => 'KB_SID=first; KB_SID=second',
        ));

        $this->assertSame('', $sessionManager->getSessionIdBehindLegacyCookie());
    }

    public function testCookiesAreLeftUntouchedOutsideLegacyPath()
    {
        // The legacy cookie is never sent for "/kanboard": the second cookie belongs to another path
        $sessionManager = $this->buildSessionManager('/kanboard/index.php', array(
            'REQUEST_URI' => '/kanboard',
            'HTTP_COOKIE' => 'KB_SID=current; KB_SID=root',
        ));

        $this->assertSame('', $sessionManager->getSessionIdBehindLegacyCookie());
    }

    private function buildSessionManager($phpSelf, array $server = array())
    {
        $this->container['request'] = new Request(
            $this->container,
            array_merge(array(
                'PHP_SELF' => $phpSelf,
                'REQUEST_METHOD' => 'GET',
            ), $server)
        );

        return new SessionManager($this->container);
    }
}
