<?php

/**
 * This file is part of domprojects/codeigniter4-localize.
 *
 * (c) domProjects
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace domProjects\CodeIgniterLocalize\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\App;
use Config\Services;
use domProjects\CodeIgniterLocalize\Config\Localize as LocalizeConfig;

class Localize implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest || is_cli()) {
            return null;
        }

        $appConfig      = config(App::class);
        $localizeConfig = config(LocalizeConfig::class);

        if (! $localizeConfig->enabled) {
            return null;
        }

        $supportedLocales = $this->getSupportedLocales($appConfig);
        $path             = trim($request->getUri()->getPath(), '/');

        if ($this->isExcluded($path, $localizeConfig->excluded)) {
            return null;
        }

        $segments        = $request->getUri()->getSegments();
        $firstSegment    = $segments[0] ?? '';
        $requestedLocale = $this->resolveSupportedLocale($firstSegment, $supportedLocales);

        if ($requestedLocale !== null) {
            $this->applyLocale($request, $requestedLocale);
            $this->persistLocale($requestedLocale, $localizeConfig);

            return null;
        }

        $detectedLocale = $this->detectLocale($request, $appConfig, $localizeConfig, $supportedLocales);

        if ($path === '') {
            if ($localizeConfig->redirectRoot && $this->canRedirect($request)) {
                return $this->redirectToLocale($detectedLocale, '', $request, $localizeConfig);
            }

            $this->applyLocale($request, $detectedLocale);
            $this->persistLocale($detectedLocale, $localizeConfig);

            return null;
        }

        if ($this->looksLikeLocale($firstSegment)) {
            if ($localizeConfig->invalidLocaleBehavior === '404') {
                throw PageNotFoundException::forPageNotFound();
            }

            if ($this->canRedirect($request)) {
                $remainingPath = implode('/', array_slice($segments, 1));

                return $this->redirectToLocale($detectedLocale, $remainingPath, $request, $localizeConfig);
            }
        }

        if ($localizeConfig->redirectMissingLocale && $this->canRedirect($request)) {
            return $this->redirectToLocale($detectedLocale, $path, $request, $localizeConfig);
        }

        $this->applyLocale($request, $detectedLocale);
        $this->persistLocale($detectedLocale, $localizeConfig);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function applyLocale(IncomingRequest $request, string $locale): void
    {
        $request->setLocale($locale);
        Services::language($locale)->setLocale($locale);
    }

    private function persistLocale(string $locale, LocalizeConfig $config, ?ResponseInterface $response = null): void
    {
        if ($config->storeInSession) {
            session()->set($config->sessionKey, $locale);
        }

        if ($config->storeInCookie) {
            ($response ?? Services::response())->setCookie(
                $config->cookieName,
                $locale,
                $config->cookieExpire,
            );
        }
    }

    private function redirectToLocale(
        string $locale,
        string $path,
        IncomingRequest $request,
        LocalizeConfig $config,
    ): ResponseInterface {
        $target = '/' . trim($locale . '/' . trim($path, '/'), '/');
        $query  = $request->getUri()->getQuery();

        $response = redirect()->to($query ? $target . '?' . $query : $target);
        $this->persistLocale($locale, $config, $response);

        return $response;
    }

    /**
     * @param list<string> $supportedLocales
     */
    private function resolveSupportedLocale(string $candidate, array $supportedLocales): ?string
    {
        if ($candidate === '') {
            return null;
        }

        foreach ($supportedLocales as $supportedLocale) {
            if (strcasecmp($candidate, $supportedLocale) === 0) {
                return $supportedLocale;
            }
        }

        return null;
    }

    /**
     * @param list<string> $supportedLocales
     */
    private function detectLocale(
        IncomingRequest $request,
        App $appConfig,
        LocalizeConfig $localizeConfig,
        array $supportedLocales,
    ): string {
        if ($localizeConfig->storeInSession) {
            $sessionLocale = session()->get($localizeConfig->sessionKey);

            if (is_string($sessionLocale)) {
                $matched = $this->resolveSupportedLocale($sessionLocale, $supportedLocales);

                if ($matched !== null) {
                    return $matched;
                }
            }
        }

        if ($localizeConfig->storeInCookie) {
            $cookieLocale = $request->getCookie($localizeConfig->cookieName);

            if (is_string($cookieLocale)) {
                $matched = $this->resolveSupportedLocale($cookieLocale, $supportedLocales);

                if ($matched !== null) {
                    return $matched;
                }
            }
        }

        if ($localizeConfig->detectFromBrowser) {
            $matched = $request->negotiate('language', $supportedLocales);

            if ($matched !== '') {
                return $matched;
            }
        }

        return $this->resolveSupportedLocale($appConfig->defaultLocale, $supportedLocales) ?? $supportedLocales[0];
    }

    private function canRedirect(IncomingRequest $request): bool
    {
        return in_array($request->getMethod(), ['GET', 'HEAD'], true);
    }

    /**
     * @param list<string> $excluded
     */
    private function isExcluded(string $path, array $excluded): bool
    {
        $path = trim(strtolower($path), '/');

        foreach ($excluded as $pattern) {
            $pattern = trim(strtolower($pattern), '/');
            $regex   = '#^' . str_replace('\*', '.*', preg_quote($pattern, '#')) . '$#u';

            if (preg_match($regex, $path) === 1) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeLocale(string $segment): bool
    {
        return preg_match('/^[a-z]{2}(?:-[a-z]{2})?$/i', $segment) === 1;
    }

    /**
     * @return list<string>
     */
    private function getSupportedLocales(App $appConfig): array
    {
        if ($appConfig->supportedLocales !== []) {
            return $appConfig->supportedLocales;
        }

        return [$appConfig->defaultLocale];
    }
}
