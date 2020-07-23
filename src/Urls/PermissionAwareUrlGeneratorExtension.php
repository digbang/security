<?php

namespace Digbang\Security\Urls;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Routing\UrlGenerator;

class PermissionAwareUrlGeneratorExtension extends UrlGenerator
{
    /**
     * @var PermissionAwareUrlGenerator
     */
    private $urlGenerator;

    public function __construct(
        PermissionAwareUrlGenerator $urlGenerator,
        RouteCollectionInterface $routes,
        Request $request,
        $assetRoot = null)
    {
        $this->urlGenerator = $urlGenerator;

        parent::__construct($routes, $request, $assetRoot);
    }

    public function full()
    {
        return $this->urlGenerator->url()->full();
    }

    public function current()
    {
        return $this->urlGenerator->url()->current();
    }

    public function previous($fallback = false)
    {
        return $this->urlGenerator->url()->previous($fallback);
    }

    public function to($path, $extra = [], $secure = null)
    {
        return $this->urlGenerator->url()->to($path, $extra, $secure);
    }

    public function secure($path, $parameters = [])
    {
        return $this->urlGenerator->url()->secure($path, $parameters);
    }

    public function asset($path, $secure = null)
    {
        return $this->urlGenerator->url()->asset($path, $secure);
    }

    public function secureAsset($path)
    {
        return $this->urlGenerator->url()->secureAsset($path);
    }

    public function assetFrom($root, $path, $secure = null)
    {
        return $this->urlGenerator->url()->assetFrom($root, $path, $secure);
    }

    public function formatScheme($secure = null)
    {
        return $this->urlGenerator->url()->formatScheme($secure);
    }

    public function signedRoute($name, $parameters = [], $expiration = null, $absolute = true)
    {
        return $this->urlGenerator->url()->signedRoute($name, $parameters, $expiration, $absolute);
    }

    public function temporarySignedRoute($name, $expiration, $parameters = [], $absolute = true)
    {
        return $this->urlGenerator->url()->temporarySignedRoute($name, $expiration, $parameters, $absolute);
    }

    public function hasValidSignature(Request $request, $absolute = true)
    {
        return $this->urlGenerator->url()->hasValidSignature($request, $absolute);
    }

    public function hasCorrectSignature(Request $request, $absolute = true)
    {
        return $this->urlGenerator->url()->hasCorrectSignature($request, $absolute);
    }

    public function signatureHasNotExpired(Request $request)
    {
        return $this->urlGenerator->url()->signatureHasNotExpired($request);
    }

    public function route($name, $parameters = [], $absolute = true)
    {
        return $this->urlGenerator->url()->route($name, $parameters, $absolute);
    }

    public function toRoute($route, $parameters, $absolute)
    {
        return $this->urlGenerator->url()->toRoute($route, $parameters, $absolute);
    }

    public function action($action, $parameters = [], $absolute = true)
    {
        return $this->urlGenerator->url()->action($action, $parameters, $absolute);
    }

    public function formatParameters($parameters)
    {
        return $this->urlGenerator->url()->formatParameters($parameters);
    }

    public function formatRoot($scheme, $root = null)
    {
        return $this->urlGenerator->url()->formatRoot($scheme, $root);
    }

    public function format($root, $path, $route = null)
    {
        return $this->urlGenerator->url()->format($root, $path, $route);
    }

    public function isValidUrl($path)
    {
        return $this->urlGenerator->url()->isValidUrl($path);
    }

    public function defaults(array $defaults)
    {
        return $this->urlGenerator->url()->defaults($defaults);
    }

    public function getDefaultParameters()
    {
        return $this->urlGenerator->url()->getDefaultParameters();
    }

    public function forceScheme($scheme)
    {
        return $this->urlGenerator->url()->forceScheme($scheme);
    }

    public function forceRootUrl($root)
    {
        return $this->urlGenerator->url()->forceRootUrl($root);
    }

    public function formatHostUsing(\Closure $callback)
    {
        return $this->urlGenerator->url()->formatHostUsing($callback);
    }

    public function formatPathUsing(\Closure $callback)
    {
        return $this->urlGenerator->url()->formatPathUsing($callback);
    }

    public function pathFormatter()
    {
        return $this->urlGenerator->url()->pathFormatter();
    }

    public function getRequest()
    {
        return $this->urlGenerator->url()->getRequest();
    }

    public function setRequest(Request $request)
    {
        return $this->urlGenerator->url()->setRequest($request);
    }

    public function setRoutes(RouteCollectionInterface $routes)
    {
        return $this->urlGenerator->url()->setRoutes($routes);
    }

    public function setSessionResolver(callable $sessionResolver)
    {
        return $this->urlGenerator->url()->setSessionResolver($sessionResolver);
    }

    public function setKeyResolver(callable $keyResolver)
    {
        return $this->urlGenerator->url()->setKeyResolver($keyResolver);
    }

    public function setRootControllerNamespace($rootNamespace)
    {
        return $this->urlGenerator->url()->setRootControllerNamespace($rootNamespace);
    }
}
