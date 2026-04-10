<?php

declare(strict_types=1);

/**
 * This file is part of domprojects/codeigniter4-localize.
 *
 * (c) domProjects
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace domProjects\CodeIgniterLocalize\Tests\Unit;

use CodeIgniter\Config\Factories;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Services;
use domProjects\CodeIgniterLocalize\Config\Localize as LocalizeConfig;
use domProjects\CodeIgniterLocalize\Filters\Localize;

/**
 * @internal
 */
final class LocalizeFilterTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \is_cli(false);
        $this->mockConfigs();
    }

    protected function tearDown(): void
    {
        \is_cli(true);

        parent::tearDown();
    }

    public function testItAppliesSupportedLocaleFromUrlAndPersistsIt(): void
    {
        $request = $this->mockRequest('/fr/about');
        $filter  = new Localize();

        $response = $filter->before($request);

        $this->assertNull($response);
        $this->assertSame('fr', $request->getLocale());
        $this->assertSame('fr', \session('locale'));
        $this->assertSame('fr', Services::response()->getCookie('locale')?->getValue());
    }

    public function testItRedirectsRootToDefaultLocale(): void
    {
        $request = $this->mockRequest('/');
        $filter  = new Localize();

        $response = $filter->before($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(\site_url('en'), $response->getHeaderLine('Location'));
        $this->assertSame('en', $response->getCookie('locale')?->getValue());
    }

    public function testItRedirectsMissingLocalePath(): void
    {
        $request = $this->mockRequest('/about');
        $filter  = new Localize();

        $response = $filter->before($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(\site_url('en/about'), $response->getHeaderLine('Location'));
    }

    public function testItThrows404ForInvalidLocaleSegment(): void
    {
        $request = $this->mockRequest('/zz/about');
        $filter  = new Localize();

        $this->expectException(PageNotFoundException::class);

        $filter->before($request);
    }

    public function testItSkipsExcludedPaths(): void
    {
        $request = $this->mockRequest('/assets/app.css');
        $filter  = new Localize();

        $response = $filter->before($request);

        $this->assertNull($response);
        $this->assertSame('en', $request->getLocale());
        $this->assertNull(\session('locale'));
    }

    public function testItCanUseStoredSessionLocale(): void
    {
        \session()->set('locale', 'fr');

        $request = $this->mockRequest('/about');
        $filter  = new Localize();

        $response = $filter->before($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(\site_url('fr/about'), $response->getHeaderLine('Location'));
    }

    /**
     * @param array<string, string> $cookies
     */
    private function mockRequest(string $path, string $method = 'GET', array $cookies = []): IncomingRequest
    {
        /** @var App $appConfig */
        $appConfig = config(App::class);
        $uri       = Services::siteurifactory(
            $appConfig,
            Services::superglobals(getShared: false),
            false,
        )->createFromString(rtrim($appConfig->baseURL, '/') . '/' . ltrim($path, '/'));
        $request  = new IncomingRequest($appConfig, $uri, null, new UserAgent());
        $response = Services::response($appConfig, false);

        $request = $request->withMethod($method);
        $request->setGlobal('server', [
            'REQUEST_METHOD'  => $method,
            'SERVER_PROTOCOL' => 'HTTP/1.1',
        ]);
        $request->setGlobal('cookie', $cookies);

        Services::injectMock('request', $request);
        Services::injectMock('response', $response);
        Services::injectMock('uri', $request->getUri());

        return $request;
    }

    private function mockConfigs(): void
    {
        $appConfig                   = new App();
        $appConfig->defaultLocale    = 'en';
        $appConfig->supportedLocales = ['en', 'fr'];
        $appConfig->negotiateLocale  = false;

        $localizeConfig                        = new LocalizeConfig();
        $localizeConfig->detectFromBrowser     = false;
        $localizeConfig->redirectRoot          = true;
        $localizeConfig->redirectMissingLocale = true;
        $localizeConfig->storeInSession        = true;
        $localizeConfig->storeInCookie         = true;

        Factories::injectMock('config', App::class, $appConfig);
        Factories::injectMock('config', LocalizeConfig::class, $localizeConfig);
    }
}
